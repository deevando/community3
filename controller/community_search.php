<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_model('comm3_item.php');

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_search extends fs_controller
{
   public $page_title;
   public $page_description;
   public $resultados;
   public $rid;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Buscar', 'comunidad', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->resultados = array();
      if( isset($_REQUEST['query']) )
      {
         $this->query = $_REQUEST['query'];
         
         $item = new comm3_item();
         $this->resultados = $item->search($this->query);
      }
   }
   
   protected function public_core()
   {
      $this->page_title = 'Buscar &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Buscador de la comunidadFacturaScripts.';
      $this->template = 'public/search';
      
      $this->rid = FALSE;
      if( isset($_COOKIE['rid']) )
      {
         $this->rid = $_COOKIE['rid'];
      }
      
      $this->resultados = array();
      if( isset($_REQUEST['query']) )
      {
         $this->query = $_REQUEST['query'];
         
         $item = new comm3_item();
         $this->resultados = $item->search($this->query);
      }
   }
   
   public function path()
   {
      if( defined('COMM3_PATH') )
      {
         return COMM3_PATH;
      }
      else
         return '';
   }
}
