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
   public $diario;
   public $stats;
   public $stat_items;
   public $versiones;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Estadísticas', 'comunidad', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      if( isset($_GET['old']) )
      {
         $this->get_old_items();
      }
      
      $stat0 = new comm3_stat();
      $this->diario = $stat0->diario();
      $this->stats = $stat0->all();
      $this->versiones = $stat0->versiones();
      
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
   
   private function get_old_items()
   {
      $csv = file_get_contents('http://www.facturascripts.com/community/stats.php?csv=TRUE');
      if($csv)
      {
         foreach( explode("\n", $csv) as $i => $value )
         {
            if($i > 0 AND $value != '')
            {
               $line = explode(';', $value);
               
               $item = new comm3_stat_item();
               $item->ip = base64_decode($line[0]);
               $item->fecha = date('d-m-Y', intval( base64_decode($line[1]) ));
               $item->version = base64_decode($line[2]);
               $item->save();
            }
         }
      }
   }
   
   public function diario_reverse()
   {
      return array_reverse($this->diario);
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
