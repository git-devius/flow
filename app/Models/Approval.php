<?php
namespace App\Models; use App\Database;
class Approval {
  public static function add($iid,$vid,$lvl,$dec,$com=null){ $p=Database::get(); $p->prepare('INSERT INTO approvals (request_id, validator_id, level, decision, comment) VALUES (?,?,?,?,?)')->execute([$iid,$vid,$lvl,$dec,$com]); }
  public static function forInvestment($iid){ $p=Database::get(); $s=$p->prepare('SELECT a.*,u.name validator_name FROM approvals a LEFT JOIN users u ON a.validator_id=u.id WHERE request_id=? ORDER BY decision_at ASC'); $s->execute([$iid]); return $s->fetchAll(); }
}
