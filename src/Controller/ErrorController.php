<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;

class ErrorController extends AbstractController
{
    public function __invoke(FlattenException $exception, Request $request): Response
    {
        $originalException = $request->attributes->get('exception');

        // Si ce n'est pas une 404, on la laisse remonter normalement
        if (!$originalException instanceof NotFoundHttpException) {
            throw $originalException;
        }

        $uri = $request->getRequestUri();

        // Si l'utilisateur est connecté et qu'il essaye d'accéder à une URL /admin
        if (str_contains($uri, '/admin')) {
            $this->addFlash('danger', "La page demandée n'existe pas.");
            return $this->redirectToRoute('admin_index');
        }

        return $this->redirectToRoute('admin_index');
    }
}
