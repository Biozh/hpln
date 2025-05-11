<?php

namespace App\Controller\admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\Datatable;
use Doctrine\ORM\EntityManagerInterface;
use PharIo\Manifest\Url;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/mon-profil')]
#[IsGranted("ROLE_USER")]
final class ProfileController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/', name: 'admin_profile_index', methods: ['GET', 'POST'])]
    public function profile(Request $request, TokenStorageInterface $tokenStorage): Response
    {
        $form = $this->createForm(UserType::class, $this->getUser());

        return $this->render('user/profile.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/changer-theme', name: 'admin_profile_toggle_theme')]
    public function toggleTheme(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['success' => true]);
        }

        $this->em->persist($user);
        $this->em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/changer-sidebar', name: 'admin_profile_toggle_sidebar')]
    public function toggleSidebar(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['success' => true]);
        }

        $user->setSidebar($user->getSidebar() === 'mini' ? 'opened' : 'mini');
        $this->em->persist($user);
        $this->em->flush();
        return $this->json(['success' => true]);
    }
}
