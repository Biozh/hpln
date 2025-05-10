<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Video;
use App\Entity\VideoCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Form\Type\VichImageType;

class VideoType extends AbstractType
{
    public function __construct(private EntityManagerInterface $em, private UrlGeneratorInterface $urlGenerator) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
            ])
            ->add('url', UrlType::class, [
                'label' => 'Lien (youtube)',
                'attr' => [
                    'placeholder' => 'https://www.youtube.com/watch?v=...',
                ],
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
            ])
            ->add('users', EntityType::class, [
                'label' => "Participants",
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getFirstname() . " " . $user->getLastname();
                },
                'multiple' => true,
                'attr' => [
                    'class' => 'select2',
                ],
            ])
            ->add('category', EntityType::class, [
                'label' => "Catégorie",
                'placeholder' => 'Sélectionner une catégorie',
                // 'multiple' => true,
                'class' => VideoCategory::class,
                'choice_label' => "name",
                'choice_value' => 'name',
                'choice_attr' => function ($choice, string $key, mixed $value) {
                    return [
                        'data-url' => $this->urlGenerator->generate('admin_cms_projects_delete_category', ['id' => $choice->getId()])
                    ];
                },
                'attr' => [
                    'data-placeholder' => 'Sélectionner une catégorie',
                    'data-allow-create' => 'true',
                    'data-tags' => 'true',
                    'class' => 'select2',
                ],
            ])
        ;


        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            $found = $this->em->getRepository(VideoCategory::class)->findBy(['name' => $data['category']]);
            if (!$found) {
                $category = new VideoCategory();
                $category->setName($data['category']);

                $slugger = new AsciiSlugger();
                $category->setSlug($slugger->slug($category->getName()));

                $this->em->persist($category);
                $this->em->flush();
            }

            $event->setData($data);
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $user = $form->getData();

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
            'data_class' => Video::class,
        ]);
    }
}
