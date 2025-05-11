<?php

namespace App\Controller\admin\cms;

use App\Entity\Video;
use App\Entity\VideoCategory;
use App\Form\VideoType;
use App\Service\Datatable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/cms/nos-projets')]
#[IsGranted('ROLE_ADMIN')]
final class ProjectController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/', name: 'admin_cms_projects_index', methods: ['GET'])]
    public function index(Request $request, Datatable $datatable): Response
    {
        $em = $this->em;

        // datatable columns
        $columns = [
            [
                'title' => 'Titre',
                'name' => 'title',
                'sort' => true,
                'search' => false,
                'filter' => true,
                'selector' => 'v.title',
            ],
            [
                'title' => 'Catégorie',
                'name' => 'category',
                'sort' => true,
                'search' => false,
                'filter' => true,
                'type' => 'select',
                'choices' => [
                    '' => '',
                ],
                'selector' => 'vc.slug',
                'table' => 'video_category',
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


        $categories = $em->getRepository(VideoCategory::class)->findAll();
        foreach ($categories as $category) {
            $columns[1]["choices"][$category->getSlug()] = $category->getName();
        }

        // datatable datas request
        if ($request->query->get("draw")) {

            $sql = "SELECT v.*, vc.name as category
            FROM video AS v
            LEFT JOIN video_category AS vc ON vc.id = v.category_id
            WHERE v.id > 0 ";

            $records = $datatable->getScriptTable($sql, $columns, $this->em, "GROUP BY v.id");
            foreach ($records['results'] as $i => $result) {
                $actions = '<div class="md-btn-group d-flex align-items-center justify-content-end">';
                $actions .= '<button data-url="' . $this->generateUrl('admin_cms_projects_form', ['id' => $result['id']]) . '" data-type="see" class="btn btn-sm btn-secondary flex-center openForm me-2" data-bs-toggle="tooltip" data-bs-title="Consulter"><span class="material-symbols-rounded fs-6">visibility</span></button>';
                $actions .= '<button data-url="' . $this->generateUrl('admin_cms_projects_form', ['id' => $result['id']]) . '" data-type="edit" class="btn btn-sm btn-primary flex-center openForm me-2" data-bs-toggle="tooltip" data-bs-title="Modifier"><span class="material-symbols-rounded fs-6">edit</span></button>';
                $actions .= '<button data-url="' . $this->generateUrl('admin_cms_projects_delete', ['id' => $result['id']]) . '" data-type="delete" class="btn btn-sm btn-danger flex-center openForm" data-bs-toggle="tooltip" data-bs-title="Supprimer"><span class="material-symbols-rounded fs-6">delete</span></button>';
                $actions .= '</div>';

                $row = [
                    'title' => $result['title'],
                    'category' => $result['category'],
                    'actions' => $actions,
                ];

                $records["aaData"][$i] = $row;
            }


            return new response(json_encode($records));
        }

        $categories = $this->em->getRepository(VideoCategory::class)->findAll();

        return $this->render('cms/projects/index.html.twig', [
            "columns" => $columns,
            "categories" => $categories,
        ]);
    }

    #[Route('/formulaire/{id?}', name: 'admin_cms_projects_form', methods: ['GET', 'POST'])]
    public function new(Video $video = null, Request $request): Response
    {
        $video = $video ?? new Video();
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($video);
            $this->em->flush();

            $message = 'Vidéo enregistrée avec succès !';
            return $this->json([
                'success' => true,
                'message' => $message,
            ]);
        }


        if ($form->isSubmitted() && !$form->isValid()) {

            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $formField = $error->getOrigin();
                $fieldName = $formField->getName();

                // Check if the field is a child of RepeatedType
                if ($formField->getParent() && $formField->getParent()->getConfig()->getType()->getInnerType() instanceof RepeatedType) {
                    $parentName = $formField->getParent()->getName();
                    $fieldName = sprintf('video[%s][%s]', $parentName, $formField->getName()); // Ex: video[password][first]
                }
                // check if the field is a child of FileType
                else if ($formField->getConfig()->getType()->getInnerType() instanceof FileType) {
                    $fieldName = sprintf('video[%s][%s]', 'picture', $formField->getName());
                } else {
                    $fieldName = sprintf('video[%s]', $fieldName);
                }
                $errors[] = [
                    'field' => $fieldName,
                    'message' => $error->getMessage(),
                ];
            }

            return $this->json(['success' => false, 'errors' => $errors], Response::HTTP_BAD_REQUEST);
        }
        return $this->render('cms/projects/form.html.twig', [
            'video' => $video,
            'form' => $form,
        ]);
    }

    #[Route('/supprimer-categories/{id}', name: 'admin_cms_projects_delete_category', methods: ['GET', 'POST'])]
    public function removeCategory(Request $request, VideoCategory $category)
    {
        $token = $request->getPayload()->getString('_token');

        if ($token) {
            if ($this->isCsrfTokenValid('delete' . $category->getId(), $token)) {
                $catName = $category->getName();
                $this->em->remove($category);
                $this->em->flush();
            }

            return $this->json(['success' => true, 'message' => 'Catégorie supprimée avec succès !', 'category' => $catName]);
        } else {
            return $this->render('cms/projects/_delete_category_form.html.twig', [
                'category' => $category,
                'message' => 'Êtes-vous sûr(e) de vouloir supprimer cette catégorie ?',
            ]);
        }
    }


    #[Route('/supprimer/{id}', name: 'admin_cms_projects_delete', methods: ['GET', 'POST'])]
    public function delete(Video $video, Request $request): Response
    {
        $token = $request->getPayload()->getString('_token');

        if ($token) {
            if ($this->isCsrfTokenValid('delete' . $video->getId(), $token)) {
                $this->em->remove($video);
                $this->em->flush();
            }

            return $this->json(['success' => true, 'message' => 'Vidéo supprimée avec succès !']);
        } else {
            return $this->render('cms/projects/_delete_form.html.twig', [
                'video' => $video,
                'message' => 'Êtes-vous sûr(e) de vouloir supprimer cette vidéo ?',
            ]);
        }
    }
}
