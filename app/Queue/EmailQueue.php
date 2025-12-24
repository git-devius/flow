<?php
namespace App\Queue;

class EmailQueue {
  
  public static function dir(){ 
    return __DIR__ . '/../../queue/emails'; 
  }
  
  public static function failedDir(){ 
    return self::dir() . '/failed'; 
  }
  
  public static function push($payload){ 
    $d = self::dir(); 
    if(!is_dir($d)) {
      mkdir($d, 0775, true);
    }
    $f = $d . '/mail_' . uniqid('', true) . '.json'; 
    file_put_contents($f, json_encode($payload)); 
    return $f; 
  }
  
  public static function pop(){
    $d = self::dir(); 
    if(!is_dir($d)) return null;
    
    $files = glob($d . '/mail_*.json'); 
    if(!$files) return null; 
    
    sort($files);
    $f = $files[0]; 
    $payload = json_decode(file_get_contents($f), true); 
    $proc = $f . '.processing'; 
    
    if(!@rename($f, $proc)) return null; 
    
    return array('file' => $proc, 'payload' => $payload);
  }
  
  public static function done($f){ 
    if(file_exists($f)) {
      unlink($f);
    }
  }
  
  public static function fail($f, $why){ 
    $fd = self::failedDir(); 
    if(!is_dir($fd)) {
      mkdir($fd, 0775, true);
    }
    
    $dest = $fd . '/' . basename($f); 
    @rename($f, $dest); 
    file_put_contents($dest . '.error.txt', $why); 
  }
  
  public static function listQueued(){ 
    $d = self::dir(); 
    if(!is_dir($d)) return array(); 
    
    $files = glob($d . '/mail_*.json'); 
    if(!$files) return array();
    
    sort($files); 
    $result = array();
    
    foreach($files as $f) {
      $result[] = array(
        'file' => $f,
        'payload' => json_decode(@file_get_contents($f), true)
      );
    }
    
    return $result;
  }
  
  public static function listFailed(){ 
    $d = self::failedDir(); 
    if(!is_dir($d)) return array(); 
    
    $files = glob($d . '/mail_*.json'); 
    if(!$files) return array();
    
    sort($files); 
    $result = array();
    
    foreach($files as $f) {
      $result[] = array(
        'file' => $f,
        'error' => @file_get_contents($f . '.error.txt'),
        'payload' => json_decode(@file_get_contents($f), true)
      );
    }
    
    return $result;
  }
  
  public static function retry($filename){ 
    $fd = self::failedDir(); 
    $src = $fd . '/' . basename($filename); 
    
    if(!file_exists($src)) return false; 
    
    $dst = self::dir() . '/' . basename($filename); 
    return @rename($src, $dst); 
  }
  
  public static function delete($filename){ 
    $base = basename($filename);
    $dir = self::dir();
    $failedDir = self::failedDir();
    
    $paths = array(
      $dir . '/' . $base,
      $dir . '/' . $base . '.processing',
      $failedDir . '/' . $base,
      $failedDir . '/' . $base . '.error.txt'
    );
    
    $ok = false; 
    foreach($paths as $p) { 
      if(file_exists($p)) { 
        @unlink($p); 
        $ok = true; 
      } 
    } 
    
    return $ok; 
  }
}
