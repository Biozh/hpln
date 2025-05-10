<?php

namespace App\Controller\admin;

use App\Entity\Token;
use App\Entity\User;
use App\Service\TokenManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private ParameterBagInterface $params) {}

    #[Route(path: '/connexion', name: 'security_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if($this->getUser()) {
            return $this->redirectToRoute('admin_index');
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/deconnexion', name: 'security_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/mot-de-passe-perdu', name: 'security_forgot_password')]
    public function forgotPassword(Request $request, AuthenticationUtils $authenticationUtils, CsrfTokenManagerInterface $CSRFtokenManager, TokenManager $tokenManager, MailerInterface $mailer): Response
    {
        $lastUsername = $authenticationUtils->getLastUsername();
        $error = null;

        if ($request->getMethod() === "POST") {
            $email = $request->request->get('_username', null);
            $CSRFToken = $request->request->get('_csrf_token', null);
        
            if (!$CSRFtokenManager->isTokenValid(new CsrfToken('forgot_password', $CSRFToken))) {
                $error = 'Une erreur est survenue, veuillez réessayer.';
            } elseif (empty($email)) {
                $error = 'Veuillez renseigner une adresse e-mail.';
            } else {
                $checkUser = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
                if (!$checkUser) {
                    $error = 'Cet email n\'existe pas.';
                }
            }
        
            if (!$error) {
                $token = $tokenManager->generateToken('forgot_password', $checkUser);        

                $resetEmail = (new Email())
                    ->from($this->params->get('MAILER_NAME'))
                    ->to($checkUser->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->html($this->renderView('email/forgot_password.html.twig', ['token' => $token, 'user' => $checkUser]));
                $mailer->send($resetEmail);   

                $this->addFlash('success', 'Un email vous a été envoyé pour réinitialiser votre mot de passe.');
                return $this->redirectToRoute('security_forgot_password');
            }
        }
        

        return $this->render('security/forgot_password.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error ?? null,
        ]);
    }

    #[Route(path: '/reinitialiser-mot-de-passe', name: 'security_reset_password')]
    public function resetPassword(Request $request, CsrfTokenManagerInterface $CSRFtokenManager, TokenManager $tokenManager): Response
    {
        $error = $request->query->get('error', null);
        $token = $request->query->get('token', $request->request->get('token', null)); // Récupère le token depuis GET ou POST
    
        if ($request->getMethod() === "POST") {
            $CSRFToken = $request->request->get('_csrf_token', null);
            $tokenEntity = $this->em->getRepository(Token::class)->findOneBy(['value' => $token, 'type' => 'forgot_password']);
    
            if (!$CSRFtokenManager->isTokenValid(new CsrfToken('reset_password', $CSRFToken))) {
                $error = 'Une erreur est survenue, veuillez réessayer.';
            }
    
            if (!$tokenEntity) {
                $error = 'Ce token n\'est pas valide.';
            }
    
            if ($tokenEntity && $tokenEntity->getExpiresAt() < new \DateTimeImmutable()) {
                $error = 'Ce token a expiré.';
            }
    
            if ($tokenEntity && $tokenEntity->getUsedAt() !== null) {
                $error = 'Ce token a déjà été utilisé.';
            }
    
            if (!$error) {
                $password = $request->request->get('password', null);
                $passwordConfirm = $request->request->get('confirmPassword', null);
    
                if (empty($password) || empty($passwordConfirm)) {
                    $error = 'Veuillez renseigner un mot de passe.';
                } elseif ($password !== $passwordConfirm) {
                    $error = 'Les mots de passe ne correspondent pas.';
                } else {
                    $user = $tokenEntity->getUser();
                    $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
                    $tokenEntity->setUsedAt(new \DateTimeImmutable());
                    $this->em->persist($user);
                    $this->em->persist($tokenEntity);
                    $this->em->flush();
                    $this->addFlash('success', 'Votre mot de passe a été réinitialisé.');
                    return $this->redirectToRoute('security_login');
                }
            }

            return $this->redirectToRoute('security_reset_password', [
                'token' => $token,
                'error' => $error
            ]);
        }
    
        return $this->render('security/reset_password.html.twig', [
            'error' => $error,
            'token' => $token,
        ]);
    }
    
}
