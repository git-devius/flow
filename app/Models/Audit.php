<?php
namespace App\Models;

use App\Database;

class Audit {
    
    /**
     * Enregistre une action utilisateur dans les logs
     * @param int|null $userId ID de l'utilisateur (peut être null)
     * @param string $action Nom de l'action (ex: 'login', 'create_request')
     * @param mixed $details Tableau ou objet de données supplémentaires
     */
    public static function log($userId, $action, $details = null) {
        try {
            $db = Database::get();
            
            // CORRECTION : Utilisation de la table 'audit_logs' et de la colonne 'meta'
            // au lieu de 'audits' et 'details'.
            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, action, meta, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId, 
                $action, 
                $details ? json_encode($details) : null
            ]);
            
        } catch (\Exception $e) {
            // On capture l'erreur silencieusement pour ne pas bloquer l'application 
            // si le système de log échoue (ex: disque plein, table manquante temporaire).
            error_log("Erreur Audit Log : " . $e->getMessage());
        }
    }
}