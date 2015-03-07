<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_model('comm3_item.php');

/**
 * Description of community_changelog
 *
 * @author carlos
 */
class community_changelog extends fs_controller
{
   public $page_title;
   public $page_description;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Changelog', 'comunidad', FALSE, FALSE);
   }
   
   protected function public_core()
   {
      $this->page_title = 'Cambios &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Todos los cambios en FacturaScripts y sus plugins.';
      $this->template = 'public/portada';
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
