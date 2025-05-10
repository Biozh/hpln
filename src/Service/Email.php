<?php

namespace App\Service;

use App\Entity\Email as EmailEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as MimeEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;

class Email
{

    public function __construct(
        private ParameterBagInterface $params,
        private Environment $twig,
        private MailerInterface $mailer,
        private EntityManagerInterface $em
    ) {}

    public function send(array $to, string $subject, string $message, EmailEntity $replyMail = null): EmailEntity
    {
        $emailEntity = new EmailEntity();
        $emailEntity->setEmails($to);
        $emailEntity->setSubject($subject);
        $emailEntity->setText($message);
        $emailEntity->setCreatedAt(new \DateTimeImmutable());

        $email = (new MimeEmail())
            ->from($this->params->get('MAILER_NAME')) 
            ->to(...$to)
            ->subject($subject)
            ->html($this->twig->render('email/blank.html.twig', ['email' => $emailEntity]));


        if ($replyMail !== null) {
            $email->replyTo(...$to);
            $replyMail->addReply($emailEntity);
            $emailEntity->setParent($replyMail);

            $this->em->persist($replyMail);
        }

        $emailEntity->setParent($replyMail);

        $this->mailer->send($email);

        $this->em->persist($emailEntity);
        $this->em->flush();

        return $emailEntity;
    }
}
