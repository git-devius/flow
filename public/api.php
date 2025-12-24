// ... (haut du fichier inchangé)
use App\Controllers\RequestController;

// ... (logique d'auth inchangée)

try {
  if($action === 'validate'){
    // ... validation des permissions
    RequestController::approve($id, $user['id'], $lvl, $dec, $com); 
    echo json_encode(['ok'=>true]); 
    exit;
    
  } elseif($action === 'create_request'){
    // ... préparation des données $data
    $newId = RequestController::createRequest($data, $user['id']); 
    echo json_encode(['ok'=>true,'request_id'=>$newId]); 
    exit;
  }
// ...