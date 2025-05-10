<?php

namespace App\Controller\front;

use App\Entity\CMSContact;
use App\Entity\CMSField;
use App\Entity\User;
use App\Entity\Video;
use App\Entity\VideoCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/')]
class FrontController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/', name: 'front_index')]
    public function index(): Response
    {
        return $this->render('front/index.html.twig');
    }
}
