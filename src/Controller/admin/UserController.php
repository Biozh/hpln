<?php

namespace App\Controller\admin;

use App\Entity\User;
use App\Form\UserType;
use App\Service\Datatable;
use App\Service\Tools;
use App\Service\UserRoleComparator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/utilisateurs')]
final class UserController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private UserRoleComparator $roleComparator) {}

    #[IsGranted('ROLE_ADMIN')]
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
                'title' => 'Rôles',
                'name' => 'roles',
                'sort' => true,
                'search' => false,
                'filter' => true,
                'type' => 'select',
                'choices' => [
                    '' => '',
                    'ROLE_SUPER_ADMIN' => 'Admin Biozh',
                    'ROLE_ADMIN' => 'Administrateur',
                    'ROLE_USER' => 'Utilisateur',
                    'ROLE_ALLOWED_TO_SWITCH' => 'Switch',
                ],
                "selector" => 'u.roles',
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
            $currentUser = $this->getUser();

            foreach ($records['results'] as $i => $result) {
                $avatarDir = $this->generateUrl('front_index') . 'uploads/avatars/' . $result["picture_name"];
                $avatar = "<div class='rounded-circle p-3 fs-6 bg-light cover text-secondary flex-center border border-light' style='width: 32px; height: 32px;" .
                    ($result["picture_name"] !== null ? " background-image: url($avatarDir);" : '') .
                    "'>" . ($result["picture_name"] ? "" : strtoupper(mb_substr($result["firstname"], 0, 1) . mb_substr($result["lastname"], 0, 1))) . "</div>";

                // On doit construire un faux user pour comparer les rôles
                $targetUser = (new User())
                    ->setEmail($result['email'])
                    ->setRoles(json_decode($result['roles'], true) ?? []);


                $roles = "<div class='d-flex align-items-center h-100 gap-1'>";
                foreach (json_decode($result['roles'], true) as $role) {
                    $dataRole = [
                        "ROLE_SUPER_ADMIN" => "Admin Biozh",
                        "ROLE_ADMIN" => "Administrateur",
                        "ROLE_USER" => "Utilisateur",
                        "ROLE_ALLOWED_TO_SWITCH" => "Switch",
                    ];
                    $roles .= "<span class='badge bg-primary'>$dataRole[$role]</span>";
                }
                $roles .= "</div>";

                $actions = '<div class="md-btn-group d-flex align-items-center justify-content-end">';
                $actions .= '<button data-url="' . $this->generateUrl('admin_user_form', ['id' => $result['id']]) . '" data-type="see" class="btn btn-sm btn-secondary flex-center openForm me-2" data-bs-toggle="tooltip" data-bs-title="Consulter"><span class="material-symbols-rounded fs-6">visibility</span></button>';

                $isSelf = $currentUser instanceof User && $currentUser->getEmail() === $result['email'];
                if ($this->roleComparator->isEqualOrSuperior($currentUser, $targetUser) || $isSelf) {
                    $actions .= '<button data-url="' . $this->generateUrl('admin_user_form', ['id' => $result['id']]) . '" data-type="edit" class="btn btn-sm btn-primary flex-center openForm me-2" data-bs-toggle="tooltip" data-bs-title="Modifier"><span class="material-symbols-rounded fs-6">edit</span></button>';
                    $actions .= '<button data-url="' . $this->generateUrl('admin_user_delete', ['id' => $result['id']]) . '" data-type="delete" class="btn btn-sm btn-dark flex-center openForm" data-bs-toggle="tooltip" data-bs-title="Supprimer"><span class="material-symbols-rounded fs-6">delete</span></button>';
                }

                $actions .= '</div>';

                $row = [
                    'avatar' => $avatar,
                    'email' => $result['email'],
                    'firstname' => $result['firstname'],
                    'lastname' => $result['lastname'],
                    'roles' => $roles,
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
    public function new(?User $user = null, Request $request, TokenStorageInterface $tokenStorage, Tools $tools): Response
    {
        $user = $user ?? new User();

        // Check if the user is granted ROLE_ADMIN or if the user is the same as the logged-in user
        if (!$this->isGranted('ROLE_ADMIN') && $user !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit d'accéder à ce formulaire.");
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $user = $user ?? new User();
        $currentUser = $this->getUser();


        if ($form->isSubmitted() && $form->isValid()) {

            // ⛔ Sécurité : on bloque si l'utilisateur n'est ni lui-même ni supérieur hiérarchiquement
            if ($user->getId() !== null && !$this->roleComparator->isEqualOrSuperior($currentUser, $user)) {
                throw $this->createAccessDeniedException("Vous n'avez pas les droits pour modifier cet utilisateur.");
            }

            $this->em->persist($user);
            $this->em->flush();

            $token = $tokenStorage->getToken();

            if ($user === $this->getUser()) {
                $user->setPicture(null);

                if ($token instanceof SwitchUserToken) {
                    $originalToken = $token->getOriginalToken();
                    $newToken = new SwitchUserToken($user, 'main', $user->getRoles(), $originalToken);
                } else {
                    $newToken = new UsernamePasswordToken($user, 'main', $user->getRoles());
                }

                $tokenStorage->setToken($newToken);

                $message = 'Profil enregistré avec succès !';

                return $this->json([
                    'success' => true,
                    'message' => $message,
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

            $errors = $tools->getFormErrors($form);

            return $this->json(['success' => false, 'errors' => $errors], Response::HTTP_BAD_REQUEST);
        }
        return $this->render('user/form.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/supprimer/{id}', name: 'admin_user_delete', methods: ['GET', 'POST'])]
    public function delete(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->getUser();

        $isSelf = $currentUser instanceof User && $currentUser->getId() === $user->getId();
        $isSuperior = $this->roleComparator->isSuperior($currentUser, $user);

        if (!$isSuperior && !$isSelf) {
            throw $this->createAccessDeniedException("Vous n'avez pas l'autorisation de supprimer cet utilisateur.");
        }

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
