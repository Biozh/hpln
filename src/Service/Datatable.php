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
        $globalSearch = $request['search']['value'] ?? '';

        $sorts = [];
        foreach ($columns as $column) {
            if (!empty($column['selector'])) {
                $sorts[$column['name']] = $column['selector'];
            }
        }

        // Table principale (FROM ...)
        preg_match('/FROM\s+([^\s]+)\s+AS\s+([^\s]+)/i', $sql, $matches);
        $tableName = $matches[1];

        $params = [];
        $schemaManager = $em->getConnection()->createSchemaManager();

        // Charger les métadonnées de toutes les tables impliquées
        $tablesUsed = [$tableName];
        foreach ($columns as $column) {
            if (!empty($column['table']) && !in_array($column['table'], $tablesUsed)) {
                $tablesUsed[] = $column['table'];
            }
        }

        $columnsMetadata = [];
        foreach ($tablesUsed as $tbl) {
            $columnsMetadata[$tbl] = $schemaManager->listTableColumns($tbl);
        }

        // Recherche par colonne
        foreach ($searchValues as $key => $column) {
            $columnIndex = $column['data'];
            $columnSearchValue = $column['search']['value'];

            if (isset($sorts[$columnIndex]) && !empty($columnSearchValue)) {
                [$alias, $field] = explode('.', $sorts[$columnIndex]);
                $table = null;

                foreach ($columns as $col) {
                    if ($col['name'] === $columnIndex && isset($col['table'])) {
                        $table = $col['table'];
                        break;
                    }
                }
                $table ??= $tableName;

                $columnMetadata = $columnsMetadata[$table][$field] ?? null;
                if (!$columnMetadata) continue;

                $columnType = $columnMetadata->getType();

                if ($columnType instanceof \Doctrine\DBAL\Types\JsonType) {
                    $sql .= " AND JSON_SEARCH(" . $sorts[$columnIndex] . ", 'one', :searchValue_$columnIndex) IS NOT NULL";
                    $params["searchValue_$columnIndex"] = "%$columnSearchValue%";
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

        // Recherche globale
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

        // Comptage total
        $countQuery = $em->getConnection()->executeQuery($sql, $params);
        $iTotalRecords = count($countQuery->fetchAllAssociative());

        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;

        // Groupement
        if ($groupBy) {
            $sql .= " $groupBy";
        }

        // Tri
        if (isset($columns[$orderColumnIndex]['selector'])) {
            $selector = $columns[$orderColumnIndex]['selector'];

            if ($selector && strpos($selector, '.') !== false) {
                [$alias, $field] = explode('.', $selector);
                $table = $columns[$orderColumnIndex]['table'] ?? $tableName;
                $columnMetadata = $columnsMetadata[$table][$field] ?? null;

                if ($columnMetadata) {
                    $columnType = $columnMetadata->getType();

                    if ($columnType instanceof \Doctrine\DBAL\Types\JsonType) {
                        $sql .= " ORDER BY JSON_UNQUOTE(JSON_EXTRACT(" . $selector . ", '$[0]')) $orderDir";
                    } elseif ($columnType instanceof \Doctrine\DBAL\Types\DateTimeType) {
                        $sql .= " ORDER BY DATE_FORMAT(" . $selector . ", '%Y-%m-%d %H:%i:%s') $orderDir";
                    } else {
                        $sql .= " ORDER BY $selector $orderDir";
                    }
                } else {
                    // fallback au champ brut
                    $sql .= " ORDER BY $selector $orderDir";
                }
            }
        }

        // Pagination
        $sql .= " LIMIT " . intval($iDisplayStart) . ", " . intval($iDisplayLength);

        // Exécution finale
        $qb = $em->getConnection()->executeQuery($sql, $params);
        $records['results'] = $qb->fetchAllAssociative();
        $records["aaData"] = $records['results'];

        return $records;
    }
}
