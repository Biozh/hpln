<?php

namespace App\Service;

use App\Entity\Email as EmailEntity;
use App\Entity\Token;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as MimeEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;

class TokenManager
{

    public function __construct(
        private EntityManagerInterface $em
    ) {}
    
    public function generateToken(string $type, User $user): string
    {
        $token = bin2hex(random_bytes(32));
        $expiration = new \DateTimeImmutable('+1 hour');

        $tokenEntity = new Token();
        $tokenEntity->setUser($user)
                    ->setValue($token)
                    ->setType($type)
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setExpiresAt($expiration);

        $this->em->persist($tokenEntity);
        $this->em->flush();

        return $token;
    }

    public function validateToken(string $token, string $type): bool
    {
        $tokenEntity = $this->em->getRepository(Token::class)->findOneBy(['token' => $token, 'type' => $type]);

        return $tokenEntity && $tokenEntity->getExpiresAt() > new \DateTimeImmutable();
    }

    public function getUserFromToken(string $token, string $type): ?User
    {
        $tokenEntity = $this->em->getRepository(Token::class)->findOneBy(['token' => $token, 'type' => $type]);

        return $tokenEntity ? $tokenEntity->getUser() : null;
    }
}
