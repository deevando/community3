<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2015  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
   public $page_title;
   public $page_description;
   public $paises;
   public $stat_items;
   public $versiones;
   public $plugins;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Estadísticas', 'comunidad', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      $stat0 = new comm3_stat();
      $this->diario = $stat0->diario();
      $this->versiones = $stat0->versiones();
      
      $stat_item0 = new comm3_stat_item();
      $this->stat_items = $stat_item0->all();
      $this->paises = $stat_item0->agrupado_paises();
      $this->plugins = $stat_item0->agrupado_plugins();
   }
   
   protected function public_core()
   {
      $this->page_title = 'Estadísticas &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Estadísticas de uso de FacturaScripts.';
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
            
            if( isset($_GET['plugins']) )
            {
               $coma = FALSE;
               foreach( explode(',', $_GET['plugins']) as $plug)
               {
                  if($coma)
                  {
                     $stat_item0->plugins .= ',';
                  }
                  else
                     $coma = TRUE;
                  
                  $stat_item0->plugins .= '['.$plug.']';
               }
            }
            
            if( $stat_item0->save() )
            {
               echo 'OK';
            }
            else
               echo 'ERROR';
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
