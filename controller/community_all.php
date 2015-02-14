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
class community_all extends fs_controller
{
   private $offset;
   public $resultados;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Todo', 'comunidad', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      $this->offset = 0;
      if( isset($_GET['offset']) )
      {
         $this->offset = intval($_GET['offset']);
      }
      
      if( isset($_GET['old']) )
      {
         $this->get_old_items();
      }
      
      $item = new comm3_item();
      $this->resultados = $item->all($this->offset);
   }
   
   protected function public_core()
   {
      $this->template = 'public/all';
      
      $this->offset = 0;
      if( isset($_GET['offset']) )
      {
         $this->offset = intval($_GET['offset']);
      }
      
      if( isset($_GET['old']) )
      {
         $this->get_old_items();
      }
      
      $item = new comm3_item();
      $this->resultados = $item->all($this->offset);
   }
   
   private function get_old_items()
   {
      $csv = file_get_contents('http://localhost/carlos/fscommunity2/all.php?csv=TRUE');
      if($csv)
      {
         foreach( explode("\n", $csv) as $i => $value )
         {
            if($i > 0 AND $value != '')
            {
               $line = explode(';', $value);
               
               $item = new comm3_item();
               $item->tipo = base64_decode($line[0]);
               $item->email = base64_decode($line[1]);
               $item->texto = base64_decode($line[2]);
               $item->info = base64_decode($line[3]);
               $item->creado = intval( base64_decode($line[4]) );
               $item->actualizado = intval( base64_decode($line[5]) );
               $item->url_title = base64_decode($line[6]);
               $item->ip = base64_decode($line[7]);
               $item->save();
            }
         }
      }
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
}
