<?php

namespace App\Form;

use App\Entity\Email as EntityEmail;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PharIo\Manifest\Email;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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

class ContactType extends AbstractType
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $em
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if(!isset($options['email_choices'])) {
            $options['emails_choices'] = array_map(fn(User $user) => $user->getEmail(), $this->em->getRepository(User::class)->findAll());
            $options['emails_choices'] = array_combine($options['emails_choices'], $options['emails_choices']);
        }

        $builder
            ->add('emails', ChoiceType::class, [
                'choices' => $options['emails_choices'],
                'placeholder' => 'Choisir un email',
                'required' => true,
                'attr' => [
                    'class' => 'select2',
                    'data-tags' => 'true',  // Permet l'ajout de nouvelles valeurs
                ],
                'multiple' => true,
                'expanded' => false,
            ])
            ->add('subject', TextType::class, [
                'required' => true,
            ])
            ->add('text', TextareaType::class, [
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EntityEmail::class,
            'emails_choices' => null,
        ]);
    }
}
