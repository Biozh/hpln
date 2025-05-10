<?php

namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;

class Datatable
{
    public function getScriptTable($sql, $columns, $em, $groupBy = null)
    {
        $request = $_REQUEST;

        $searchValues = $request['columns'];
        $iDisplayStart = isset($request['start']) ? intval($request['start']) : 0;
        $iDisplayLength = isset($request['length']) ? intval($request['length']) : 50;
        $orderColumnIndex = isset($request['order'][0]['column']) ? intval($request['order'][0]['column']) : 0;
        $orderDir = isset($request['order'][0]['dir']) && in_array(strtoupper($request['order'][0]['dir']), ['ASC', 'DESC']) ? strtoupper($request['order'][0]['dir']) : 'ASC';
        $globalSearch = isset($request['search']['value']) ? $request['search']['value'] : '';

        $sorts = [];
        foreach ($columns as $column) {
            if (!empty($column['selector'])) {
                $sorts[$column['name']] = $column['selector'];
            }
        }

        // Extract table name from SQL query
        preg_match('/FROM\s+([^\s]+)\s+AS\s+([^\s]+)/i', $sql, $matches);
        $tableName = $matches[1];

        $params = [];
        $schemaManager = $em->getConnection()->getSchemaManager();

        // Filtrage par colonnes
        foreach ($searchValues as $key => $column) {
            $columnIndex = $column['data'];
            $columnSearchValue = $column['search']['value'];

            if (!isset($sorts[$columnIndex]) || empty($columnSearchValue)) {
                continue;
            }

            // Trouver la config de colonne correspondant au nom
            $targetColumnConfig = null;
            foreach ($columns as $colConfig) {
                if ($colConfig['name'] === $columnIndex) {
                    $targetColumnConfig = $colConfig;
                    break;
                }
            }

            if (!$targetColumnConfig) {
                continue;
            }

            $selectorParts = explode(".", $sorts[$columnIndex]);
            $columnName = end($selectorParts);
            $columnTable = $targetColumnConfig['table'] ?? $tableName;

            try {
                $columnsMetadataTarget = $schemaManager->listTableColumns($columnTable);
            } catch (\Throwable $e) {
                continue; // ignore si la table n'existe pas (sécurité)
            }

            if (!isset($columnsMetadataTarget[$columnName])) {
                continue;
            }

            $columnMetadata = $columnsMetadataTarget[$columnName];
            $columnType = $columnMetadata->getType();

            if ($columnType instanceof \Doctrine\DBAL\Types\JsonType) {
                $sql .= " AND JSON_SEARCH(" . $sorts[$columnIndex] . ", 'one', '%" . $columnSearchValue . "%') IS NOT NULL";
            } elseif ($columnType instanceof \Doctrine\DBAL\Types\DateTimeType) {
                $date = \DateTime::createFromFormat('d/m/Y', $columnSearchValue);
                if ($date) {
                    $formattedDate = $date->format('Y-m-d');
                    $sql .= " AND DATE_FORMAT(" . $sorts[$columnIndex] . ", '%Y-%m-%d') LIKE :searchValue_$columnIndex";
                    $params["searchValue_$columnIndex"] = "%$formattedDate%";
                } else {
                    $sql .= " AND DATE_FORMAT(" . $sorts[$columnIndex] . ", '%d/%m/%Y') LIKE :searchValue_$columnIndex";
                    $params["searchValue_$columnIndex"] = "%$columnSearchValue%";
                }
            } else {
                $sql .= " AND " . $sorts[$columnIndex] . " LIKE :searchValue_$columnIndex";
                $params["searchValue_$columnIndex"] = "%$columnSearchValue%";
            }
        }

        // Ajout d'une recherche globale si présente
        if (!empty($globalSearch)) {
            $sql .= " AND (";
            foreach ($sorts as $index => $column) {
                if ($searchValues[$index]['searchable'] === 'true') {
                    $sql .= $column . " LIKE :globalSearch OR ";
                }
            }
            $sql = rtrim($sql, " OR ") . ")";
            $params['globalSearch'] = "%$globalSearch%";
        }

        // Exécution préliminaire pour compter les résultats
        $qb = $em->getConnection()->executeQuery($sql, $params);
        $counts = $qb->fetchAllAssociative();

        $iTotalRecords = count($counts);
        $totalDisplayed = intval($iDisplayLength);
        $iDisplayLength = $totalDisplayed < 0 ? $iTotalRecords : $totalDisplayed;
        $records["iTotalDisplayRecords"] = $records["iTotalRecords"] = $iTotalRecords;

        // Groupement (optionnel)
        if ($groupBy) {
            $sql .= " $groupBy";
        }

        // Ajout du tri basé sur la colonne et la direction
        if (isset($columns[$orderColumnIndex]['selector'])) {
            $orderColumn = $columns[$orderColumnIndex]['name'];
            $orderSelector = $columns[$orderColumnIndex]['selector'];

            $selectorParts = explode(".", $orderSelector);
            $columnName = end($selectorParts);
            $columnTable = $columns[$orderColumnIndex]['table'] ?? $tableName;

            try {
                $columnsMetadataTarget = $schemaManager->listTableColumns($columnTable);
            } catch (\Throwable $e) {
                $columnsMetadataTarget = [];
            }

            if (isset($columnsMetadataTarget[$columnName])) {
                $columnMetadata = $columnsMetadataTarget[$columnName];
                $columnType = $columnMetadata->getType();

                if ($columnType instanceof \Doctrine\DBAL\Types\JsonType) {
                    $sql .= " ORDER BY JSON_UNQUOTE(JSON_EXTRACT(" . $orderSelector . ", '$[0]')) " . $orderDir;
                } elseif ($columnType instanceof \Doctrine\DBAL\Types\DateTimeType) {
                    $sql .= " ORDER BY DATE_FORMAT(" . $orderSelector . ", '%Y-%m-%d %H:%i:%s') " . $orderDir;
                } else {
                    $sql .= " ORDER BY " . $orderSelector . " " . $orderDir;
                }
            } else {
                // fallback sans type
                $sql .= " ORDER BY " . $orderSelector . " " . $orderDir;
            }
        }

        // Pagination
        $sql .= " LIMIT " . intval($iDisplayStart) . ", " . intval($iDisplayLength);

        // Requête finale
        $qb = $em->getConnection()->executeQuery($sql, $params);
        $records['results'] = $qb->fetchAllAssociative();
        $records["aaData"] = $records['results'];

        return $records;
    }
}
