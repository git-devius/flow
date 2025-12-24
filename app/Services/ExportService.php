<?php
namespace App\Services;

class ExportService {

    /**
     * Génère un export CSV des demandes
     * * @param array $requests Liste des demandes récupérées depuis le modèle
     * @param string $workflowType Type de workflow pour le nom du fichier
     * @return void
     */
    public static function generateCsv(array $requests, string $workflowType = 'all') {
        if (headers_sent()) {
            throw new \Exception("Impossible de générer le CSV, les en-têtes ont déjà été envoyés.");
        }

        $filename = 'export_demandes_' . $workflowType . '_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // BOM pour l'ouverture correcte dans Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, array(
            'ID', 'Workflow', 'Etape', 'Type', 'Montant (€)', 
            'Pôle', 'Société', 'Demandeur', 'Statut', 
            'Budget prévu', 'Objet', 'Début & durée', 'Date création'
        ), ';');
        
        foreach($requests as $req) {
            fputcsv($output, array(
                $req['id'],
                $req['workflow_type'],
                $req['current_step'],
                $req['type'],
                $req['amount'],
                $req['pole_name'],
                $req['company_name'],
                $req['requester'],
                $req['status'],
                $req['budget_planned'] ? 'Oui' : 'Non',
                $req['objective'],
                $req['start_date_duration'],
                date('d/m/Y H:i', strtotime($req['created_at']))
            ), ';');
        }
        
        fclose($output);
    }
}