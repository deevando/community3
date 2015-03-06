<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_home extends fs_controller
{
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Portada', 'comunidad', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      
   }
   
   protected function public_core()
   {
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
