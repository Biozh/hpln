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
            if ($column['selector']) {
                $sorts[$column['name']] = $column['selector'];
            }
        }
        
        // Extract table name from SQL query
        preg_match('/FROM\s+([^\s]+)\s+AS\s+([^\s]+)/i', $sql, $matches);
        $tableName = $matches[1];
    
        $params = [];
        $schemaManager = $em->getConnection()->getSchemaManager(); 
        $columnsMetadata = $schemaManager->listTableColumns($tableName);
    
        // Filtrage par colonnes
        foreach ($searchValues as $key => $column) {
            $columnIndex = $column['data'];
            $columnSearchValue = $column['search']['value'];

            if (isset($sorts[$columnIndex]) && !empty($columnSearchValue)) {
                $columnMetadata = $columnsMetadata[explode(".", $sorts[$columnIndex])[1]];
                $columnType = $columnMetadata->getType();
                if($columnType instanceof \Doctrine\DBAL\Types\JsonType) {
                    // $sql .= " AND JSON_CONTAINS(" . $sorts[$columnIndex] . ", '\"" . $columnSearchValue . "\"') = 1";
                    $sql .= " AND JSON_SEARCH(" . $sorts[$columnIndex] . ", 'one', '%" . $columnSearchValue . "%') IS NOT NULL";
                } 
                
                else if ($columnType instanceof \Doctrine\DBAL\Types\DateTimeType) {
                    // Convert the date string from "dd/mm/YYYY" to "Y-m-d H:i:s"
                    $date = \DateTime::createFromFormat('d/m/Y', $columnSearchValue);
                    if ($date) {
                        $formattedDate = $date->format('Y-m-d');
                        // dd($formattedDate);
                        $sql .= " AND DATE_FORMAT(" . $sorts[$columnIndex] . ", '%Y-%m-%d') LIKE :searchValue_$columnIndex";
                        // $sql .= " AND DATE_FORMAT(" . $sorts[$columnIndex] . ", '%d/%m/%Y') LIKE :searchValue_$columnIndex";
                        $params["searchValue_$columnIndex"] = "%$formattedDate%";
                    } else {
                        $sql .= " AND DATE_FORMAT(" . $sorts[$columnIndex] . ", '%d/%m/%Y') LIKE :searchValue_$columnIndex";
                        $params["searchValue_$columnIndex"] = "%$columnSearchValue%";
                    }
                }
                
                else {
                    $sql .= " AND " . $sorts[$columnIndex] . " LIKE :searchValue_$columnIndex";
                    $params["searchValue_$columnIndex"] = "%$columnSearchValue%";
                }
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
            if(isset($columns[$orderColumnIndex]['selector']) && !empty($columns[$orderColumnIndex]['selector'])) {
                $columnMetadata = $columnsMetadata[$orderColumn];
                $columnType = $columnMetadata->getType();

                if($columnType instanceof \Doctrine\DBAL\Types\JsonType) {
                    $sql .= " ORDER BY JSON_UNQUOTE(JSON_EXTRACT(" . $orderColumn . ", '$[0]')) " . $orderDir;
                } 
                else if ($columnType instanceof \Doctrine\DBAL\Types\DateTimeType) {
                    $sql .= " ORDER BY DATE_FORMAT(" . $orderColumn . ", '%Y-%m-%d %H:%i:%s') " . $orderDir;
                }
                else {
                    $sql .= " ORDER BY " . $orderColumn . " " . $orderDir; // Appliquer l'ordre ASC/DESC
                }	
            }
        }
    
        // Limite et offset pour la pagination
        $sql .= " LIMIT " . intval($iDisplayStart) . ", " . intval($iDisplayLength);
    
        // Exécution de la requête
        $qb = $em->getConnection()->executeQuery($sql, $params);
        $records['results'] = $qb->fetchAllAssociative();
    
        // Structure des données pour DataTables
        $records["aaData"] = $records['results'];
        
        return $records;
    }
}
