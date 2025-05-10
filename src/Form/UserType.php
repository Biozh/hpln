<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PharIo\Manifest\Email;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
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

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'required' => true,
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                    'Switch' => 'ROLE_ALLOWED_TO_SWITCH',
                ],
                'attr' => [
                    'class' => 'select2',
                    'required' => false
                ],
                'multiple' => true,
                'expanded' => false,
            ]);
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $user = $form->getData();

            $createdAt = $user->getCreatedAt();
            if (!$createdAt) {
                $user->setCreatedAt(new \DateTimeImmutable());
            }

            $user->setUpdatedAt(new \DateTimeImmutable());
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
                // Ajouter l'erreur à la deuxième ou première partie du champ RepeatedType
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
