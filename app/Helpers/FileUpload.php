<?php
namespace App\Helpers;

class FileUpload {
  
  /**
   * Types MIME autorisés avec leurs extensions
   */
  private static $allowedTypes = array(
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/vnd.ms-excel' => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif'
  );
  
  /**
   * Taille maximale : 10MB
   */
  private static $maxSize = 10485760; // 10 * 1024 * 1024
  
  /**
   * Uploader un fichier de manière sécurisée
   * 
   * @param array $file Le tableau $_FILES['field_name']
   * @param string $uploadDir Le répertoire de destination
   * @return string Le nom du fichier uploadé
   * @throws \Exception En cas d'erreur
   */
  public static function upload($file, $uploadDir) {
    // Vérification de base
    if(!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
      throw new \Exception('Fichier non valide ou non uploadé');
    }
    
    // Vérifier qu'il n'y a pas d'erreur d'upload
    if($file['error'] !== UPLOAD_ERR_OK) {
      throw new \Exception(self::getUploadErrorMessage($file['error']));
    }
    
    // Vérifier la taille
    if($file['size'] > self::$maxSize) {
      $maxMB = round(self::$maxSize / 1048576, 1);
      throw new \Exception("Fichier trop volumineux (max {$maxMB}MB)");
    }
    
    if($file['size'] === 0) {
      throw new \Exception('Fichier vide');
    }
    
    // Vérifier le type MIME réel
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if(!isset(self::$allowedTypes[$mimeType])) {
      throw new \Exception('Type de fichier non autorisé. Types acceptés : PDF, Word, Excel, Images (JPG, PNG, GIF)');
    }
    
    // Générer un nom de fichier sécurisé et unique
    $extension = self::$allowedTypes[$mimeType];
    $filename = 'inv_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    
    // Créer le répertoire si nécessaire
    if(!is_dir($uploadDir)) {
      if(!mkdir($uploadDir, 0775, true)) {
        throw new \Exception('Impossible de créer le répertoire de destination');
      }
    }
    
    $targetPath = $uploadDir . '/' . $filename;
    
    // Déplacer le fichier
    if(!move_uploaded_file($file['tmp_name'], $targetPath)) {
      throw new \Exception('Erreur lors du déplacement du fichier');
    }
    
    // Définir les permissions
    chmod($targetPath, 0644);
    
    return $filename;
  }
  
  /**
   * Supprimer un fichier uploadé
   * 
   * @param string $filename Le nom du fichier
   * @param string $uploadDir Le répertoire
   * @return bool
   */
  public static function delete($filename, $uploadDir) {
    if(empty($filename)) return false;
    
    $path = $uploadDir . '/' . $filename;
    
    if(file_exists($path)) {
      return @unlink($path);
    }
    
    return false;
  }
  
  /**
   * Obtenir un message d'erreur lisible
   */
  private static function getUploadErrorMessage($code) {
    $errors = array(
      UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille autorisée par le serveur',
      UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille autorisée',
      UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé',
      UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été uploadé',
      UPLOAD_ERR_NO_TMP_DIR => 'Répertoire temporaire manquant',
      UPLOAD_ERR_CANT_WRITE => 'Erreur d\'écriture sur le disque',
      UPLOAD_ERR_EXTENSION => 'Upload bloqué par une extension PHP'
    );
    
    return isset($errors[$code]) ? $errors[$code] : 'Erreur d\'upload inconnue';
  }
  
  /**
   * Valider un fichier uploadé sans le déplacer
   * 
   * @param array $file Le tableau $_FILES['field_name']
   * @return array array('valid' => bool, 'error' => string|null)
   */
  public static function validate($file) {
    try {
      if(!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return array('valid' => false, 'error' => 'Fichier non valide');
      }
      
      if($file['error'] !== UPLOAD_ERR_OK) {
        return array('valid' => false, 'error' => self::getUploadErrorMessage($file['error']));
      }
      
      if($file['size'] > self::$maxSize) {
        $maxMB = round(self::$maxSize / 1048576, 1);
        return array('valid' => false, 'error' => "Fichier trop volumineux (max {$maxMB}MB)");
      }
      
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mimeType = finfo_file($finfo, $file['tmp_name']);
      finfo_close($finfo);
      
      if(!isset(self::$allowedTypes[$mimeType])) {
        return array('valid' => false, 'error' => 'Type de fichier non autorisé');
      }
      
      return array('valid' => true, 'error' => null);
      
    } catch(\Exception $e) {
      return array('valid' => false, 'error' => $e->getMessage());
    }
  }
  
  /**
   * Obtenir la taille maximale en MB
   */
  public static function getMaxSizeMB() {
    return round(self::$maxSize / 1048576, 1);
  }
  
  /**
   * Obtenir la liste des extensions autorisées
   */
  public static function getAllowedExtensions() {
    return array_unique(array_values(self::$allowedTypes));
  }
}
