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
class community_questions extends fs_controller
{
   public $page_title;
   public $page_description;
   public $resultados;
   public $rid;
   
   private $offset;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Preguntas', 'comunidad', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      $this->offset = 0;
      if( isset($_GET['offset']) )
      {
         $this->offset = intval($_GET['offset']);
      }
      
      $item = new comm3_item();
      $this->resultados = $item->all_by_tipo('question', $this->offset);
   }
   
   protected function public_core()
   {
      $this->page_title = 'Preguntas &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Preguntas a la comunidad FacturaScripts.';
      $this->template = 'public/questions';
      
      $this->rid = FALSE;
      if( isset($_COOKIE['rid']) )
      {
         $this->rid = $_COOKIE['rid'];
      }
      
      $this->offset = 0;
      if( isset($_GET['offset']) )
      {
         $this->offset = intval($_GET['offset']);
      }
      
      $item = new comm3_item();
      $this->resultados = $item->all_by_tipo('question', $this->offset);
   }
   
   public function anterior_url()
   {
      $url = '';
      
      if($this->offset > 0)
      {
         $url = $this->url()."&offset=".($this->offset-FS_ITEM_LIMIT);
      }
      
      return $url;
   }
   
   public function siguiente_url()
   {
      $url = '';
      
      if( count($this->resultados) == FS_ITEM_LIMIT )
      {
         $url = $this->url()."&offset=".($this->offset+FS_ITEM_LIMIT);
      }
      
      return $url;
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
