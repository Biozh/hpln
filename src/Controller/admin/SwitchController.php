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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/switch')]
#[IsGranted("ROLE_ALLOWED_TO_SWITCH")]
final class SwitchController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route(path: '/', name: 'admin_switch_index', methods: ['GET'])]
    public function index(Request $request, Datatable $datatable): Response
    {
        // datatable columns
        $columns = [
            [
                'title' => 'Email',
                'name' => 'email',
                'sort' => true,
                'search' => true,
                'filter' => false,
                'selector' => 'u.email',
            ],
            [
                'title' => 'Prénom',
                'name' => 'firstname',
                'sort' => true,
                'search' => true,
                'filter' => false,
                'selector' => 'u.firstname',
            ],
            [
                'title' => 'Nom',
                'name' => 'lastname',
                'sort' => true,
                'search' => true,
                'filter' => false,
                'selector' => 'u.lastname',
            ],
            [
                'title' => 'Rôles',
                'name' => 'roles',
                'sort' => true,
                'search' => true,
                'filter' => false,
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
            foreach ($records['results'] as $i => $result) {

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
                $user = $this->getUser();
                if ($user instanceof User && $result['id'] != $user->getId()) {
                    $actions .= '<a href="' . $this->generateUrl('admin_index', ['_switch_user' => $result['email']]) . '" class="btn btn-sm btn-primary flex-center me-2" data-bs-toggle="tooltip" data-bs-title="Switch"><span class="material-symbols-rounded fs-6">logout</span></a>';
                }
                $actions .= '</div>';

                $row = [
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

        return $this->render('admin/switch/index.html.twig', [
            "columns" => $columns,
        ]);
    }
}
