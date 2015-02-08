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
class community_download extends fs_controller
{
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Descargas', 'comunidad', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      
   }
   
   protected function public_core()
   {
      $this->template = 'public/download';
   }
}
