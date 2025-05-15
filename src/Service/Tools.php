<?php

namespace App\Service;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;

class Tools
{
    public function getFormErrors(FormInterface $form): array
    {
        $errors = [];
        $formName = $form->getName(); // ex: 'user'

        foreach ($form->getErrors(true) as $error) {

            $formField = $error->getOrigin();
            $fieldName = $formField->getName();

            // Gestion des champs RepeatedType (ex: user[password][first])
            if (
                $formField->getParent() &&
                $formField->getParent()->getConfig()->getType()->getInnerType() instanceof RepeatedType
            ) {
                $parentName = $formField->getParent()->getName();
                $fieldName = sprintf('%s[%s][%s]', $formName, $parentName, $fieldName);
            }
            // Gestion des fichiers vich (ex: user[picture][file])
            elseif ($formField->getConfig()->getType()->getInnerType() instanceof VichImageType) {
                $fieldName = sprintf('%s[%s][%s]', $formName, $fieldName, "file");
            }
            // Champ simple (ex: user[email])
            else {
                $fieldName = sprintf('%s[%s]', $formName, $fieldName);
            }

            $errors[] = [
                'field' => $fieldName,
                'message' => $error->getMessage(),
            ];
        }

        return $errors;
    }
}
