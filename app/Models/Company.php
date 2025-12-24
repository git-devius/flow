<?php
namespace App\Models; use App\Database;
class Company {
  // NOTE: Les champs validator_lv1_user_id et validator_lv2_user_id sont conservés
  // dans la requête de sélection si votre base de données les contient toujours.
  // Cependant, les fonctions CRUD ne les utiliseront plus.
  public static function all(){ $q='SELECT c.*, p.name pole_name FROM companies c JOIN poles p ON c.pole_id=p.id ORDER BY p.name, c.name'; return Database::get()->query($q)->fetchAll(); }
  public static function find($id){ $p=Database::get(); $s=$p->prepare('SELECT * FROM companies WHERE id=?'); $s->execute([$id]); return $s->fetch(); }
  public static function byPole($pole_id){ $p=Database::get(); $s=$p->prepare('SELECT * FROM companies WHERE pole_id=? ORDER BY name'); $s->execute([$pole_id]); return $s->fetchAll(); }
  
  // MODIFIÉ : Suppression des valideurs $v1, $v2
  public static function create($pole_id,$name){ 
      $p=Database::get(); 
      $p->prepare('INSERT INTO companies (pole_id,name) VALUES (?,?)')
        ->execute([$pole_id,$name]); 
      return $p->lastInsertId(); 
  }
  
  // MODIFIÉ : Suppression des valideurs $v1, $v2
  public static function update($id,$pole_id,$name){ 
      $p=Database::get(); 
      $p->prepare('UPDATE companies SET pole_id=?, name=? WHERE id=?')
        ->execute([$pole_id,$name,$id]); 
  }
  
  public static function delete($id){ $p=Database::get(); $p->prepare('DELETE FROM companies WHERE id=?')->execute([$id]); }
}