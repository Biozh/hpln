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
    public function __construct(private EntityManagerInterface $em, private SerializerInterface $serializer, private ParameterBagInterface $params) {}

    #[Route('/', name: 'front_index')]
    public function index(): Response
    {
        $videos = $this->em->getRepository(Video::class)->findBy([], ['createdAt' => 'DESC']);
        $categories = $this->em->getRepository(VideoCategory::class)->findAll();

        $description1 = $this->em->getRepository(CMSField::class)->findOneBy(['slug' => 'about_description1']);
        $description2 = $this->em->getRepository(CMSField::class)->findOneBy(['slug' => 'about_description2']);

        $aboutUsers = $this->em->getRepository(User::class)->findBy(['showAboutPage' => true], ['createdAt' => 'DESC']);

        return $this->render('hpln.html.twig', [
            'videos' => $this->serializer->serialize($videos, 'json', ['groups' => ['video:read']]),
            'categories' => $this->serializer->serialize($categories, 'json', ['groups' => ['video:read']]),
            'aboutUsers' => $this->serializer->serialize($aboutUsers, 'json', ['groups' => ['video:read']]),
            'description1' => $this->serializer->serialize($description1, 'json'),
            'description2' => $this->serializer->serialize($description2, 'json'),
        ]);
    }

    #[Route('/contact', name: 'front_contact', methods: ['POST'])]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $datas = json_decode($request->getContent(), true);

        $resetEmail = (new Email())
            ->from($this->params->get('MAILER_NAME'))
            ->to($this->params->get('MAILER_NAME'))
            ->subject('Demande de contact')
            ->html($this->renderView('email/contact.html.twig', ['datas' => $datas]));
        $mailer->send($resetEmail);

        $contact = new CMSContact();
        $contact->setFirstname($datas['firstname']);
        $contact->setLastname($datas['lastname']);
        $contact->setEmail($datas['email']);
        $contact->setMessage($datas['message']);
        $contact->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($contact);
        $this->em->flush();

        return $this->json([
            "success" => true,
            "message" => "Message envoyé avec succès !",
        ]);
    }

    #[Route('/_error', name: 'front_error')]
    public function notFound(Request $request, Security $security): Response
    {
        $exception = $request->attributes->get('exception');

        if (!$exception instanceof NotFoundHttpException) {
            throw $exception; // on laisse passer les autres erreurs
        }

        return $this->redirectToRoute('front_index');
    }
}
