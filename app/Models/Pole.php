<?php
namespace App\Models; use App\Database;
class Pole {
  public static function all(){ return Database::get()->query('SELECT * FROM poles ORDER BY name')->fetchAll(); }
  public static function find($id){ $p=Database::get(); $s=$p->prepare('SELECT * FROM poles WHERE id=?'); $s->execute([$id]); return $s->fetch(); }
  public static function create($name){ $p=Database::get(); $p->prepare('INSERT INTO poles (name) VALUES (?)')->execute([$name]); return $p->lastInsertId(); }
  public static function update($id,$name){ $p=Database::get(); $p->prepare('UPDATE poles SET name=? WHERE id=?')->execute([$name,$id]); }
  public static function delete($id){ $p=Database::get(); $p->prepare('DELETE FROM poles WHERE id=?')->execute([$id]); }
}
