<?php

namespace App\Form;

use App\Entity\User;
use App\Service\UserRoleComparator;
use Doctrine\ORM\EntityManagerInterface;
use PharIo\Manifest\Email;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $em,
        private Security $security,
        private UserRoleComparator $roleComparator
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'required' => true,
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'required' => isset($options['data']) && $options['data']->getId() ? false : true,
                'mapped' => false, // intercepted by form event
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Répéter le mot de passe'],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom'
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('role_asso', TextType::class, [
                'label' => 'Role dans l\'association'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse'
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville'
            ])
            ->add('zip', TextType::class, [
                'label' => 'Code postal'
            ])
            ->add('picture', VichImageType::class, [
                'label' => "Photo de profil",
                'delete_label' => "Supprimer",
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => false,
                'asset_helper' => true,
            ])
        ;

        // roles management
        $currentUser = $this->security->getUser();
        $targetUser = $builder->getData();

        $isSelf = $currentUser instanceof User && $currentUser->getId() === $targetUser->getId();
        $isSuperior = $this->roleComparator->isSuperior($currentUser, $targetUser);
        $isEqualOrSuperior = $this->roleComparator->isEqualOrSuperior($currentUser, $targetUser);
        $isSuperAdmin = in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles(), true);
        $isAdmin = in_array('ROLE_ADMIN', $currentUser->getRoles(), true);

        $canEditRoles = (
            // on est super admin et on modifie soi-même
            ($isSelf && $isSuperAdmin)
            // ou on est au moins égal au niveau du target et ce n’est pas soi-même
            || (!$isSelf && $isEqualOrSuperior)
        );

        if ($isAdmin || $isSuperAdmin) {
            $roleChoices = [
                'Utilisateur' => 'ROLE_USER',
            ];

            // Seuls les admins peuvent attribuer le rôle admin
            if ($isAdmin) {
                $roleChoices['Administrateur'] = 'ROLE_ADMIN';
            }

            // Seuls les super admins peuvent attribuer le rôle switch et super admin
            if ($isSuperAdmin) {
                $roleChoices['Administrateur Biozh'] = 'ROLE_SUPER_ADMIN';
                $roleChoices['Switch'] = 'ROLE_ALLOWED_TO_SWITCH';
            }

            $builder->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => $roleChoices,
                'multiple' => true,
                'expanded' => false,
                'attr' => ['class' => 'select2']
            ]);
        }

        // default vaLues
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $user = $form->getData();

            $createdAt = $user->getCreatedAt();
            if (!$createdAt) {
                $user->setCreatedAt(new \DateTimeImmutable());
            }

            $showAboutPage = $user->isShowAboutPage();
            if (!$showAboutPage) {
                $user->setShowAboutPage(false);
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $user = $form->getData();

            $createdAt = $user->getCreatedAt();
            if (!$createdAt) {
                $user->setCreatedAt(new \DateTimeImmutable());
            }

            // Récupérer les deux mots de passe saisis

            $password = $form->get('password')->get('first')->getData();
            $confirmPassword = $form->get('password')->get('second')->getData();

            // Si les mots de passe ne correspondent pas, ajouter une erreur
            if ($password !== $confirmPassword) {
                // Ajouter l'erreur à la deuxièFme ou première partie du champ RepeatedType
                $form->get('password')->get('second')->addError(new FormError('Les mots de passe ne correspondent pas.'));
            } else {
                // Hachage du mot de passe si tout est correct
                if ($password) {
                    $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
                    $user->setPassword($hashedPassword);
                }
            }
        });


        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            // check email duplication
            $existingUser = $this->em->getRepository(User::class)
                ->findOneBy(['email' => $data->getEmail()]);
            if ($existingUser && $existingUser !== $data) {
                $form->get('email')->addError(new FormError('Cette adresse e-mail est déjà utilisée.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
