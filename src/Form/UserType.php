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
                'empty_data' => '',
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'required' => isset($options['data']) && $options['data']->getId() ? false : true,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'constraints' => [
                        new Assert\Length([
                            'min' => 8,
                            'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                            'max' => 255,
                        ]),
                        new Assert\Regex([
                            'pattern' => '/(?=.*[A-Z])(?=.*[a-z])(?=.*\d).+/',
                            'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.',
                        ])
                    ]
                ],
                'second_options' => ['label' => 'Répéter le mot de passe'],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'empty_data' => '',
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'empty_data' => '',
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'empty_data' => '',
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'empty_data' => '',
            ])
            ->add('zip', TextType::class, [
                'label' => 'Code postal',
                'empty_data' => '',
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

        $isSuperAdmin = in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles(), true);
        $isAdmin = in_array('ROLE_ADMIN', $currentUser->getRoles(), true);

        if ($isAdmin || $isSuperAdmin) {
            $roleChoices = [
                'Utilisateur' => 'ROLE_USER',
            ];

            // Seuls les admins peuvent attribuer le rôle admin
            if ($isAdmin || $isSuperAdmin) {
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
                'attr' => ['class' => 'select2'],
                'data' => $options['data']->getRoles() ?? [],
                'constraints' => [
                    new Assert\NotNull(['message' => 'Les rôles ne peuvent pas être nuls.']),
                    new Assert\Count([
                        'min' => 1,
                        'minMessage' => 'Au moins un rôle doit être sélectionné.',
                    ]),
                    new Assert\All([
                        new Assert\NotBlank(['message' => 'Chaque rôle doit être renseigné.']),
                        new Assert\Type([
                            'type' => 'string',
                            'message' => 'Chaque rôle doit être une chaîne de caractères.',
                        ]),
                        new Assert\Choice([
                            'choices' => array_values($roleChoices),
                            'message' => 'Le rôle sélectionné est invalide.',
                        ]),
                    ]),
                ],
            ]);
        }

        // default vaLues
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $user = $form->getData();

            $user->setUpdatedAt(new \DateTimeImmutable());

            $createdAt = $user->getCreatedAt();
            if (!$createdAt) {
                $user->setCreatedAt(new \DateTimeImmutable());
            }

            if (empty($user->getRoles())) {
                $user->setRoles(['ROLE_USER']);
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

                // si le mot de passe est différent de l'ancien mot de passe
                if ($password && count($form->getErrors(true, true)) === 0) {
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
