<?php

namespace App\Controller\admin\cms;

use App\Entity\CMSField;
use App\Entity\User;
use App\Entity\Video;
use App\Entity\VideoCategory;
use App\Form\AboutAddUserType;
use App\Form\AboutUserType;
use App\Form\UserType;
use App\Form\VideoType;
use App\Service\Datatable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/cms/l-association')]
#[IsGranted('ROLE_ADMIN')]
final class AboutController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/', name: 'admin_cms_about_index', methods: ['GET', 'POST'])]
    public function index(Request $request, Datatable $datatable): Response
    {
        // datatable columns
        $columns = [
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
            WHERE u.id > 0 AND u.show_about_page = 1";

            $records = $datatable->getScriptTable($sql, $columns, $this->em, "GROUP BY u.id");
            foreach ($records['results'] as $i => $result) {
                $actions = '<div class="md-btn-group d-flex align-items-center justify-content-end">';
                $actions .= '<button data-url="' . $this->generateUrl('admin_user_form', ['id' => $result['id']]) . '" data-type="see" class="btn btn-sm btn-secondary flex-center openForm me-2" data-bs-toggle="tooltip" data-bs-title="Consulter"><span class="material-symbols-rounded fs-6">visibility</span></button>';
                $actions .= '<button data-url="' . $this->generateUrl('admin_cms_about_delete', ['id' => $result['id']]) . '" data-type="delete" class="btn btn-sm btn-danger flex-center openForm" data-bs-toggle="tooltip" data-bs-title="Supprimer"><span class="material-symbols-rounded fs-6">delete</span></button>';
                $actions .= '</div>';

                $row = [
                    'firstname' => $result['firstname'],
                    'lastname' => $result['lastname'],
                    'actions' => $actions,
                ];

                $records["aaData"][$i] = $row;
            }


            return new response(json_encode($records));
        }

        $addMemberForm = $this->createForm(AboutAddUserType::class, null);
        $addMemberForm->handleRequest($request);

        if ($request->isMethod('POST')) {
            if ($addMemberForm->isSubmitted() && $addMemberForm->isValid()) {
                $user = $addMemberForm->getData()['member'];
                if ($user) {
                    $user->setShowAboutPage(true);
                    $this->em->persist($user);
                    $this->em->flush();

                    return $this->json([
                        'success' => true,
                        'message' => 'Membre ajouté avec succès',
                    ]);
                }
                return $this->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'ajout du membre',
                ]);
            }

            if ($addMemberForm->isSubmitted() && !$addMemberForm->isValid()) {
                $errors = [];
                foreach ($addMemberForm->getErrors(true) as $error) {
                    $formField = $error->getOrigin();
                    $fieldName = $formField->getName();
                    if ($formField->getConfig()->getType()->getInnerType() instanceof FileType) {
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
        }

        $description1 = $this->em->getRepository(CMSField::class)->findOneBy(['slug' => 'about_description1']);
        $description2 = $this->em->getRepository(CMSField::class)->findOneBy(['slug' => 'about_description2']);

        return $this->render('cms/about/index.html.twig', [
            "columns" => $columns,
            "addMemberForm" => $addMemberForm->createView(),
            "description1" => $description1,
            "description2" => $description2,
        ]);
    }

    #[Route('/sauvegarder-descriptions', name: 'admin_cms_about_save_descriptions', methods: ['POST'])]
    public function saveDescriptions(Request $request): Response
    {
        $data = $request->request->all();

        $CMSField = $this->em->getRepository(CMSField::class)->findOneBy(["slug" => "about_description1"]) ?? new CMSField();

        if (!$CMSField->getSlug()) $CMSField->setSlug('about_description1');
        $CMSField->setValue($data['description1']);

        $this->em->persist($CMSField);
        $this->em->flush();

        $CMSField = $this->em->getRepository(CMSField::class)->findOneBy(["slug" => "about_description2"]) ?? new CMSField();
        if (!$CMSField->getSlug()) $CMSField->setSlug('about_description2');
        $CMSField->setValue($data['description2']);

        $this->em->persist($CMSField);
        $this->em->flush();


        return $this->json([
            'success' => true,
            'message' => 'Descriptions enregistrées avec succès !',
        ]);
    }

    #[Route('/supprimer/{id}', name: 'admin_cms_about_delete', methods: ['GET', 'POST'])]
    public function delete(User $user, Request $request): Response
    {
        $token = $request->getPayload()->getString('_token');

        if ($token) {
            if ($this->isCsrfTokenValid('delete' . $user->getId(), $token)) {
                $user->setShowAboutPage(false);
                $this->em->persist($user);
                $this->em->flush();
            }

            return $this->json(['success' => true, 'message' => 'Membre retiré avec succès !']);
        } else {
            return $this->render('cms/about/_delete_form.html.twig', [
                'user' => $user,
                'message' => 'Êtes-vous sûr(e) de vouloir retirer ce membre ?',
            ]);
        }
    }
}
