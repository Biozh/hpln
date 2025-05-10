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

class AboutUserType extends AbstractType
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

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $user = $form->getData();

            $theme = $user->getTheme();
            if (!$theme) {
                $user->setTheme('dark');
            }

            $createdAt = $user->getCreatedAt();
            if (!$createdAt) {
                $user->setCreatedAt(new \DateTimeImmutable());
            }

            $user->setUpdatedAt(new \DateTimeImmutable());
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
