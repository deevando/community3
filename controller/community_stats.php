<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_model('comm3_stat.php');
require_model('comm3_stat_item.php');

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_stats extends fs_controller
{
   public $stats;
   public $stat_items;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Estadísticas', 'comunidad', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      $stat0 = new comm3_stat();
      $this->stats = $stat0->all();
      
      $stat_item0 = new comm3_stat_item();
      $this->stat_items = $stat_item0->all();
   }
   
   protected function public_core()
   {
      $this->template = 'public/portada';
      
      /**
       * Necesitamos un identificador para el visitante.
       * Así luego podemos relacioner sus comentarios y preguntas.
       */
      $rid = $this->random_string(30);
      if( isset($_COOKIE['rid']) )
      {
         $rid = $_COOKIE['rid'];
      }
      else
      {
         setcookie('rid', $rid, time()+FS_COOKIES_EXPIRE, '/');
      }
      
      if( isset($_GET['add']) AND isset($_GET['version']) AND isset($_SERVER['REMOTE_ADDR']) )
      {
         $this->template = FALSE;
         
         $stat_item0 = new comm3_stat_item();
         $si = $stat_item0->get($_SERVER['REMOTE_ADDR'], date('d-m-Y'));
         if($si)
         {
            echo 'OK';
         }
         else
         {
            $stat_item0->ip = $_SERVER['REMOTE_ADDR'];
            $stat_item0->version = $_GET['version'];
            $stat_item0->rid = $rid;
            if( $stat_item0->save() )
            {
               echo 'OK';
            }
            else
               echo 'ERROR';
         }
      }
   }
}
