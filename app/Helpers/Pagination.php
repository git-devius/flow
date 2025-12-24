<?php
namespace App\Helpers;

class Pagination {
  
  private $totalItems;
  private $itemsPerPage;
  private $currentPage;
  private $totalPages;
  
  /**
   * Constructor
   */
  public function __construct($totalItems, $itemsPerPage = 20, $currentPage = 1) {
    $this->totalItems = max(0, (int)$totalItems);
    $this->itemsPerPage = max(1, (int)$itemsPerPage);
    $this->currentPage = max(1, (int)$currentPage);
    $this->totalPages = $this->totalItems > 0 ? (int)ceil($this->totalItems / $this->itemsPerPage) : 1;
    
    // Corriger la page courante si elle dépasse
    if($this->currentPage > $this->totalPages) {
      $this->currentPage = $this->totalPages;
    }
  }
  
  /**
   * Obtenir l'offset pour la requête SQL
   */
  public function getOffset() {
    return ($this->currentPage - 1) * $this->itemsPerPage;
  }
  
  /**
   * Obtenir la limite pour la requête SQL
   */
  public function getLimit() {
    return $this->itemsPerPage;
  }
  
  /**
   * Obtenir le numéro de la page courante
   */
  public function getCurrentPage() {
    return $this->currentPage;
  }
  
  /**
   * Obtenir le nombre total de pages
   */
  public function getTotalPages() {
    return $this->totalPages;
  }
  
  /**
   * Obtenir le nombre total d'éléments
   */
  public function getTotalItems() {
    return $this->totalItems;
  }
  
  /**
   * Y a-t-il une page précédente ?
   */
  public function hasPrevious() {
    return $this->currentPage > 1;
  }
  
  /**
   * Y a-t-il une page suivante ?
   */
  public function hasNext() {
    return $this->currentPage < $this->totalPages;
  }
  
  /**
   * Obtenir le numéro de la page précédente
   */
  public function getPreviousPage() {
    return $this->hasPrevious() ? $this->currentPage - 1 : 1;
  }
  
  /**
   * Obtenir le numéro de la page suivante
   */
  public function getNextPage() {
    return $this->hasNext() ? $this->currentPage + 1 : $this->totalPages;
  }
  
  /**
   * Obtenir les numéros de pages à afficher (avec ...)
   */
  public function getPageNumbers($maxVisible = 7) {
    $pages = array();
    
    if($this->totalPages <= $maxVisible) {
      // Afficher toutes les pages
      for($i = 1; $i <= $this->totalPages; $i++) {
        $pages[] = $i;
      }
    } else {
      // Toujours afficher la première page
      $pages[] = 1;
      
      // Calculer la plage autour de la page courante
      $start = max(2, $this->currentPage - 2);
      $end = min($this->totalPages - 1, $this->currentPage + 2);
      
      // Ajouter "..." si nécessaire
      if($start > 2) {
        $pages[] = '...';
      }
      
      // Pages autour de la page courante
      for($i = $start; $i <= $end; $i++) {
        $pages[] = $i;
      }
      
      // Ajouter "..." si nécessaire
      if($end < $this->totalPages - 1) {
        $pages[] = '...';
      }
      
      // Toujours afficher la dernière page
      if($this->totalPages > 1) {
        $pages[] = $this->totalPages;
      }
    }
    
    return $pages;
  }
  
  /**
   * Générer le HTML de pagination Bootstrap
   */
  public function render($baseUrl, $params = array()) {
    if($this->totalPages <= 1) {
      return '';
    }
    
    $html = '<nav aria-label="Navigation des pages">';
    $html .= '<ul class="pagination justify-content-center">';
    
    // Bouton Précédent
    $disabled = !$this->hasPrevious() ? ' disabled' : '';
    $prevUrl = $this->buildUrl($baseUrl, array_merge($params, array('page' => $this->getPreviousPage())));
    $html .= '<li class="page-item' . $disabled . '">';
    $html .= '<a class="page-link" href="' . htmlspecialchars($prevUrl) . '" tabindex="-1">Précédent</a>';
    $html .= '</li>';
    
    // Numéros de pages
    foreach($this->getPageNumbers() as $page) {
      if($page === '...') {
        $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
      } else {
        $active = $page === $this->currentPage ? ' active' : '';
        $url = $this->buildUrl($baseUrl, array_merge($params, array('page' => $page)));
        $html .= '<li class="page-item' . $active . '">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($url) . '">' . $page . '</a>';
        $html .= '</li>';
      }
    }
    
    // Bouton Suivant
    $disabled = !$this->hasNext() ? ' disabled' : '';
    $nextUrl = $this->buildUrl($baseUrl, array_merge($params, array('page' => $this->getNextPage())));
    $html .= '<li class="page-item' . $disabled . '">';
    $html .= '<a class="page-link" href="' . htmlspecialchars($nextUrl) . '">Suivant</a>';
    $html .= '</li>';
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    // Informations
    $from = $this->getOffset() + 1;
    $to = min($this->getOffset() + $this->itemsPerPage, $this->totalItems);
    $html .= '<p class="text-center text-muted small">';
    $html .= 'Affichage de ' . $from . ' à ' . $to . ' sur ' . $this->totalItems . ' résultats';
    $html .= '</p>';
    
    return $html;
  }
  
  /**
   * Construire une URL avec paramètres
   */
  private function buildUrl($baseUrl, $params) {
    $query = http_build_query($params);
    return $baseUrl . ($query ? '?' . $query : '');
  }
}
