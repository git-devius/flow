<?php
namespace App\Models;

use App\Database;
use App\Helpers\FileUpload; 
use App\Config; 

class Request {
  
  const WORKFLOW_TYPES = [
    'investment' => 'Demande d\'Investissement',
    'vacation' => 'Demande de Congés',
    'expense' => 'Note de Frais',
  ];

  public static function getInvestmentTypes() {
    $p = Database::get();
    $stmt = $p->query("SELECT DISTINCT type FROM requests WHERE workflow_type = 'investment' ORDER BY type");
    return $stmt->fetchAll(\PDO::FETCH_COLUMN);
  }
  
  public static function create($data){
    $p = Database::get();
    // On initialise current_step à 1 et status à 'pending'
    $p->prepare('INSERT INTO requests (pole_id,company_id,workflow_type,type,budget_planned,objective,start_date_duration,amount,requester_id,status,current_step,file_path) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
      ->execute(array(
          $data['pole_id'],
          $data['company_id'],
          $data['workflow_type'] ?? 'investment',
          $data['type'],
          $data['budget_planned']?1:0,
          $data['objective'],
          $data['start_date_duration'],
          $data['amount'],
          $data['requester_id'],
          'pending', 
          1,
          $data['file_path']
      ));
    return $p->lastInsertId();
  }
  
  public static function find($id){
    $p = Database::get(); 
    $s = $p->prepare('SELECT i.*, u.name requester, c.name company_name, p2.name pole_name FROM requests i JOIN users u ON i.requester_id=u.id JOIN companies c ON i.company_id=c.id JOIN poles p2 ON i.pole_id=p2.id WHERE i.id=?'); 
    $s->execute(array($id)); 
    return $s->fetch();
  }
  
  // NOUVEAU : Met à jour uniquement l'étape
  public static function updateStep($id, $step) {
    $p = Database::get();
    $p->prepare('UPDATE requests SET current_step=?, updated_at=NOW() WHERE id=?')->execute([$step, $id]);
  }

  public static function updateStatus($id, $st){ 
    $p = Database::get(); 
    $p->prepare('UPDATE requests SET status=?, updated_at=NOW() WHERE id=?')->execute(array($st, $id)); 
  }

  public static function delete($id) {
    $p = Database::get();
    $request = self::find($id);
    if ($request && $request['file_path']) {
      try {
        $uploadDir = Config::get('UPLOAD_DIR', __DIR__.'/../uploads');
        FileUpload::delete($request['file_path'], $uploadDir);
      } catch (\Exception $e) {
        error_log("Delete file error: " . $e->getMessage());
      }
    }
    $stmt = $p->prepare('DELETE FROM requests WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
  }

  /**
   * Search optimisé pour éviter les doublons avec EXISTS
   */
  public static function search($user, $filters = array(), $orderBy = 'created_at', $orderDir = 'DESC', $limit = 20, $offset = 0) {
    $p = Database::get();
    
    $query = 'SELECT i.*, u.name requester, c.name company_name, p.name pole_name 
              FROM requests i 
              JOIN users u ON i.requester_id=u.id 
              JOIN companies c ON i.company_id=c.id 
              JOIN poles p ON i.pole_id=p.id ';
              
    $params = array();
    $whereClause = ' WHERE 1=1';

    if (!empty($filters['workflow_type'])) {
        $whereClause .= ' AND i.workflow_type = ?';
        $params[] = $filters['workflow_type'];
    }
    
    // Filtrage permissions sans JOIN pour éviter la multiplication des lignes
    if ($user && isset($user['role']) && isset($user['id']) && $user['role'] !== 'admin') {
      $whereClause .= ' AND (i.requester_id = ? 
        OR EXISTS (
            SELECT 1 FROM workflow_steps ws 
            WHERE ws.company_id = i.company_id 
            AND ws.workflow_type = i.workflow_type 
            AND ws.validator_user_id = ?
        )
      )';
      $params[] = $user['id'];
      $params[] = $user['id'];
    }
    
    // Filtres standards
    if(!empty($filters['search'])) {
      $whereClause .= ' AND (i.type LIKE ? OR i.objective LIKE ? OR u.name LIKE ? OR c.name LIKE ? OR p.name LIKE ?)';
      $searchTerm = '%' . $filters['search'] . '%';
      array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    }
    
    if(!empty($filters['type'])) { $whereClause .= ' AND i.type = ?'; $params[] = $filters['type']; }
    
    if (!empty($filters['status'])) {
        $statuses = is_array($filters['status']) ? $filters['status'] : explode(',', $filters['status']);
        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $whereClause .= " AND i.status IN ({$placeholders})";
        $params = array_merge($params, $statuses);
    }
    
    if(!empty($filters['pole_id'])) { $whereClause .= ' AND i.pole_id = ?'; $params[] = $filters['pole_id']; }
    if(!empty($filters['company_id'])) { $whereClause .= ' AND i.company_id = ?'; $params[] = $filters['company_id']; }
    if(isset($filters['min_amount']) && $filters['min_amount'] !== '') { $whereClause .= ' AND i.amount >= ?'; $params[] = $filters['min_amount']; }
    if(isset($filters['max_amount']) && $filters['max_amount'] !== '') { $whereClause .= ' AND i.amount <= ?'; $params[] = $filters['max_amount']; }
    
    if(!empty($filters['open_only'])) { 
        // Adaptation : open signifie statut pending
        $whereClause .= ' AND i.status = ?'; 
        $params[] = 'pending';
    }
    if(!empty($filters['not_cancelled_only'])) { $whereClause .= ' AND i.status != ?'; $params[] = 'cancelled'; }

    $query .= $whereClause;

    if(!in_array($orderBy, ['created_at', 'amount', 'type', 'status'])) { $orderBy = 'created_at'; }
    $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';
    $query .= ' ORDER BY i.' . $orderBy . ' ' . $orderDir;
    
    // Si la limite est très grande (export), on gère le type INT max
    $limit = min((int)$limit, 18446744073709551615);
    $query .= ' LIMIT ' . $limit . ' OFFSET ' . (int)$offset;
    
    $stmt = $p->prepare($query);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
  }
  
  public static function count($user, $filters = array()) {
    $p = Database::get();
    
    $query = 'SELECT COUNT(DISTINCT i.id) as total 
              FROM requests i 
              JOIN users u ON i.requester_id=u.id 
              JOIN companies c ON i.company_id=c.id 
              JOIN poles p ON i.pole_id=p.id ';

    $params = array();
    $whereClause = ' WHERE 1=1';

    if (!empty($filters['workflow_type'])) {
        $whereClause .= ' AND i.workflow_type = ?';
        $params[] = $filters['workflow_type'];
    }
    
    if ($user && isset($user['role']) && isset($user['id']) && $user['role'] !== 'admin') {
      $whereClause .= ' AND (i.requester_id = ? 
        OR EXISTS (
            SELECT 1 FROM workflow_steps ws 
            WHERE ws.company_id = i.company_id 
            AND ws.workflow_type = i.workflow_type 
            AND ws.validator_user_id = ?
        )
      )';
      $params[] = $user['id'];
      $params[] = $user['id'];
    }

    // [Mêmes filtres que search pour count]
    if(!empty($filters['search'])) {
      $whereClause .= ' AND (i.type LIKE ? OR i.objective LIKE ? OR u.name LIKE ? OR c.name LIKE ? OR p.name LIKE ?)';
      $searchTerm = '%' . $filters['search'] . '%';
      array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    }
    if(!empty($filters['type'])) { $whereClause .= ' AND i.type = ?'; $params[] = $filters['type']; }
    if (!empty($filters['status'])) {
        $statuses = is_array($filters['status']) ? $filters['status'] : explode(',', $filters['status']);
        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $whereClause .= " AND i.status IN ({$placeholders})";
        $params = array_merge($params, $statuses);
    }
    if(!empty($filters['pole_id'])) { $whereClause .= ' AND i.pole_id = ?'; $params[] = $filters['pole_id']; }
    if(!empty($filters['company_id'])) { $whereClause .= ' AND i.company_id = ?'; $params[] = $filters['company_id']; }
    if(isset($filters['min_amount']) && $filters['min_amount'] !== '') { $whereClause .= ' AND i.amount >= ?'; $params[] = $filters['min_amount']; }
    if(isset($filters['max_amount']) && $filters['max_amount'] !== '') { $whereClause .= ' AND i.amount <= ?'; $params[] = $filters['max_amount']; }
    if(!empty($filters['open_only'])) { $whereClause .= ' AND i.status = ?'; $params[] = 'pending'; }
    if(!empty($filters['not_cancelled_only'])) { $whereClause .= ' AND i.status != ?'; $params[] = 'cancelled'; }
    
    $query .= $whereClause;

    $stmt = $p->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetch();
    
    return (int)$result['total'];
  }

  /**
   * Calcul des KPIs
   */
  public static function getKpis($user) {
      $p = Database::get();
      $kpis = [];
      $whereClause = ' WHERE 1=1';
      $params = [];
      
      $joinCompany = 'JOIN companies c ON i.company_id = c.id'; 

      // Filtre permissions
      if ($user && isset($user['role']) && isset($user['id']) && $user['role'] !== 'admin') {
          $whereClause .= ' AND (i.requester_id = ? 
              OR EXISTS (
                  SELECT 1 FROM workflow_steps ws 
                  WHERE ws.company_id = i.company_id 
                  AND ws.workflow_type = i.workflow_type 
                  AND ws.validator_user_id = ?
              )
          )';
          $params = [$user['id'], $user['id']];
      }
      
      // Requête de base
      $baseQuery = 'SELECT COUNT(DISTINCT i.id) as count, i.workflow_type, i.status, SUM(i.amount) as total_amount 
                    FROM requests i 
                    ' . $joinCompany
                    . $whereClause;

      // 1. Total par type de workflow
      $queryType = $baseQuery . ' GROUP BY i.workflow_type'; 
      $stmtType = $p->prepare($queryType);
      $stmtType->execute($params);
      $kpis['total_by_type'] = $stmtType->fetchAll(\PDO::FETCH_ASSOC);

      // 2. Total par statut
      $queryStatus = 'SELECT COUNT(DISTINCT i.id) as count, i.workflow_type, i.status, SUM(i.amount) as total_amount 
                      FROM requests i 
                      ' . $joinCompany
                      . $whereClause
                      . ' GROUP BY i.workflow_type, i.status'; 
                      
      $stmtStatus = $p->prepare($queryStatus);
      $stmtStatus->execute($params);
      $kpis['total_by_status'] = $stmtStatus->fetchAll(\PDO::FETCH_ASSOC);

      // 3. Montant total en attente
      $pendingClause = $whereClause . " AND i.status = 'pending'";
      $pendingParams = $params;

      if ($user === null || (isset($user['role']) && $user['role'] === 'admin')) {
          $pendingParams = []; 
          $pendingClause = " WHERE i.status = 'pending'";
          $joinCompany = 'JOIN companies c ON i.company_id = c.id';
      }

      $queryPending = 'SELECT COUNT(DISTINCT i.id) as count, SUM(i.amount) as total_amount 
                       FROM requests i 
                       ' . $joinCompany
                       . $pendingClause;
                       
      $stmtPending = $p->prepare($queryPending);
      $stmtPending->execute($pendingParams);
      $kpis['pending_approval'] = $stmtPending->fetch(\PDO::FETCH_ASSOC);

      return $kpis;
  }
}