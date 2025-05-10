<?php

namespace App\Controller\admin;

use App\Entity\User;
use App\Form\UserType;
use App\Service\Datatable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/utilisateurs')]
#[IsGranted('ROLE_ADMIN')]
final class UserController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/', name: 'admin_user_index', methods: ['GET'])]
    public function index(Request $request, Datatable $datatable): Response
    {
        // datatable columns
        $columns = [
            [
                'title' => 'Avatar',
                'name' => 'avatar',
                'sort' => false,
                'search' => false,
                'filter' => false,
                'selector' => '',
            ],
            [
                'title' => 'Email',
                'name' => 'email',
                'sort' => true,
                'search' => false,
                'filter' => true,
                'selector' => 'u.email',
            ],
            [
                'title' => 'Prénom',
                'name' => 'firstname',
                'sort' => true,
                'search' => false,
                'filter' => true,
                'selector' => 'u.firstname',
            ],
            [
                'title' => 'Nom',
                'name' => 'lastname',
                'sort' => true,
                'search' => false,
                'filter' => true,
                'selector' => 'u.lastname',
            ],
            [
                'title' => 'Actions',
                'name' => 'actions',
                'sort' => false,
                'search' => false,
                'filter' => false,
                'selector' => '',
            ]
        ];

        // datatable datas request
        if ($request->query->get("draw")) {

            $sql = "SELECT u.*
            FROM user AS u
            WHERE u.id > 0 ";

            $records = $datatable->getScriptTable($sql, $columns, $this->em, "GROUP BY u.id");
            foreach ($records['results'] as $i => $result) {

                $avatarDir = $this->generateUrl('admin_index') . 'uploads/avatars/' . $result["picture_name"];
                if ($result["picture_name"] !== null) {
                    $avatar = "<div class='rounded-circle p-3 bg-body-secondary cover' style='width: 32px; height: 32px; background-image: url(" . $avatarDir . ");'></div>";
                } else {
                    $avatar = "<div class='rounded-circle p-3 bg-body-secondary cover' style='width: 32px; height: 32px;'></div>";
                }

                $actions = '<div class="md-btn-group d-flex align-items-center justify-content-end">';
                $actions .= '<button data-url="' . $this->generateUrl('admin_user_form', ['id' => $result['id']]) . '" data-type="see" class="btn btn-sm btn-secondary flex-center openForm me-2" data-bs-toggle="tooltip" data-bs-title="Consulter"><span class="material-symbols-rounded fs-6">visibility</span></button>';
                $actions .= '<button data-url="' . $this->generateUrl('admin_user_form', ['id' => $result['id']]) . '" data-type="edit" class="btn btn-sm btn-primary flex-center openForm me-2" data-bs-toggle="tooltip" data-bs-title="Modifier"><span class="material-symbols-rounded fs-6">edit</span></button>';
                $actions .= '<button data-url="' . $this->generateUrl('admin_user_delete', ['id' => $result['id']]) . '" data-type="delete" class="btn btn-sm btn-dark flex-center openForm" data-bs-toggle="tooltip" data-bs-title="Supprimer"><span class="material-symbols-rounded fs-6">delete</span></button>';
                $actions .= '</div>';

                $row = [
                    'avatar' => $avatar,
                    'email' => $result['email'],
                    'firstname' => $result['firstname'],
                    'lastname' => $result['lastname'],
                    'actions' => $actions,
                ];

                $records["aaData"][$i] = $row;
            }


            return new response(json_encode($records));
        }

        return $this->render('user/index.html.twig', [
            "columns" => $columns,
        ]);
    }

    #[Route('/formulaire/{id?}', name: 'admin_user_form', methods: ['GET', 'POST'])]
    public function new(User $user = null, Request $request, TokenStorageInterface $tokenStorage): Response
    {
        $user = $user ?? new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($user);
            $this->em->flush();

            if ($user === $this->getUser()) {
                $user->setPicture(null);
                $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                $tokenStorage->setToken($token);

                $message = 'Profil enregistré avec succès !';
                // return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
                $this->addFlash('success', $message);
                return $this->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => $this->generateUrl('admin_user_index', [], UrlGeneratorInterface::ABSOLUTE_URL)
                ]);
            }

            return $this->json(['success' => true, 'message' => 'Utilisateur enregistré avec succès !']);
        }


        if ($form->isSubmitted() && !$form->isValid()) {

            if ($user === $this->getUser()) {
                $user->setPicture(null);
                $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                $tokenStorage->setToken($token);
            }

            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $formField = $error->getOrigin();
                $fieldName = $formField->getName();

                // Check if the field is a child of RepeatedType
                if ($formField->getParent() && $formField->getParent()->getConfig()->getType()->getInnerType() instanceof RepeatedType) {
                    $parentName = $formField->getParent()->getName();
                    $fieldName = sprintf('user[%s][%s]', $parentName, $formField->getName()); // Ex: user[password][first]
                }
                // check if the field is a child of FileType
                else if ($formField->getConfig()->getType()->getInnerType() instanceof FileType) {
                    $fieldName = sprintf('user[%s][%s]', 'picture', $formField->getName());
                } else {
                    $fieldName = sprintf('user[%s]', $fieldName);
                }
                $errors[] = [
                    'field' => $fieldName,
                    'message' => $error->getMessage(),
                ];
            }

            return $this->json(['success' => false, 'errors' => $errors], Response::HTTP_BAD_REQUEST);
        }
        return $this->render('user/form.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/supprimer/{id}', name: 'admin_user_delete', methods: ['GET', 'POST'])]
    public function delete(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $token = $request->getPayload()->getString('_token');

        if ($token) {
            if ($this->isCsrfTokenValid('delete' . $user->getId(), $token)) {
                $entityManager->remove($user);
                $entityManager->flush();
            }

            return $this->json(['success' => true, 'message' => 'Utilisateur supprimé avec succès !']);
        } else {
            return $this->render('user/_delete_form.html.twig', [
                'user' => $user,
                'message' => 'Êtes-vous sûr(e) de vouloir supprimer cet utilisateur ?',
            ]);
        }
    }
}
