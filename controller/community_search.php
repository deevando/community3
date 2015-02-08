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
   public $resultados;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Buscar', 'comunidad', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      
   }
   
   protected function public_core()
   {
      $this->template = 'public/search';
      
      $this->resultados = array();
      if( isset($_REQUEST['query']) )
      {
         $this->query = $_REQUEST['query'];
         
         $item = new comm3_item();
         $this->resultados = $item->search($this->query);
      }
   }
}
