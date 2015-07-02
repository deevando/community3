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
   public $page_keywords;
   public $paises;
   public $plugins;
   public $semanal;
   public $stat_items;
   public $tablas;
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
      $this->versiones = $stat0->versiones();
      
      $stat_item0 = new comm3_stat_item();
      $this->stat_items = $stat_item0->all();
      $this->paises = $stat_item0->agrupado_paises();
      $this->plugins = $stat_item0->agrupado_plugins();
      
      $this->semanal = isset($_GET['semanal']);
      
      $this->tablas = $this->get_datos_tablas();
   }
   
   protected function public_core()
   {
      $this->page_title = 'Estadísticas &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Estadísticas de uso de FacturaScripts.';
      $this->page_keywords = 'facturascripts, eneboo, abanq, woocommerce, prestashop, facturae';
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
      if($this->semanal)
      {
         $semanas = array();
         foreach(array_reverse($this->diario) as $d)
         {
            $num = intval( date('YW', strtotime($d['fecha'])) );
            if( isset($semanas[$num]) )
            {
               $semanas[$num]['activos'] += $d['activos'];
               $semanas[$num]['descargas'] += $d['descargas'];
            }
            else
            {
               $semanas[$num] = array(
                   'fecha' => date('Y#W', strtotime($d['fecha'])),
                   'activos' => $d['activos'],
                   'descargas' => $d['descargas']
               );
            }
         }
         
         return $semanas;
      }
      else
         return array_reverse($this->diario);
   }
   
   private function get_old_items()
   {
      $csv = file_get_contents('http://localhost/carlos/fscommunity2/stats.php?csv=TRUE');
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
   
   private function get_datos_tablas()
   {
      $tablas = array();
      
      /// visitantes
      $data = $this->db->select("select count(rid) as contador from comm3_visitantes;");
      if($data)
      {
         $tablas[] = array('tabla' => 'Visitantes', 'agrupacion' => '*', 'contador' => intval($data[0]['contador']));
      }
      $data = $this->db->select("select count(rid) as contador from comm3_visitantes where perfil = 'voluntario';");
      if($data)
      {
         $tablas[] = array('tabla' => 'Visitantes', 'agrupacion' => 'Voluntarios', 'contador' => intval($data[0]['contador']));
      }
      $data = $this->db->select("select count(rid) as contador from comm3_visitantes where perfil = 'programador';");
      if($data)
      {
         $tablas[] = array('tabla' => 'Visitantes', 'agrupacion' => 'Programadores', 'contador' => intval($data[0]['contador']));
      }
      $data = $this->db->select("select count(rid) as contador from comm3_visitantes where perfil = 'cliente';");
      if($data)
      {
         $tablas[] = array('tabla' => 'Visitantes', 'agrupacion' => 'Clientes de partner', 'contador' => intval($data[0]['contador']));
      }
      
      /// items
      $data = $this->db->select("select count(id) as contador from comm3_items;");
      if($data)
      {
         $tablas[] = array('tabla' => 'Items', 'agrupacion' => '*', 'contador' => intval($data[0]['contador']));
      }
      $data = $this->db->select("select count(id) as contador from comm3_items where tipo = 'error';");
      if($data)
      {
         $tablas[] = array('tabla' => 'Items', 'agrupacion' => 'Errores', 'contador' => intval($data[0]['contador']));
      }
      $data = $this->db->select("select count(id) as contador from comm3_items where tipo = 'idea';");
      if($data)
      {
         $tablas[] = array('tabla' => 'Items', 'agrupacion' => 'Ideas', 'contador' => intval($data[0]['contador']));
      }
      $data = $this->db->select("select count(id) as contador from comm3_items where tipo = 'question';");
      if($data)
      {
         $tablas[] = array('tabla' => 'Items', 'agrupacion' => 'Questions', 'contador' => intval($data[0]['contador']));
      }
      $data = $this->db->select("select count(id) as contador from comm3_items where tipo = 'task';");
      if($data)
      {
         $tablas[] = array('tabla' => 'Items', 'agrupacion' => 'Tareas', 'contador' => intval($data[0]['contador']));
      }
      
      /// comentarios
      $data = $this->db->select("select count(id) as contador from comm3_comments;");
      if($data)
      {
         $tablas[] = array('tabla' => 'Comentarios', 'agrupacion' => '*', 'contador' => intval($data[0]['contador']));
      }
      
      return $tablas;
   }
}
