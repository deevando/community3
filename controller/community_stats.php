<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2015-2016  Carlos Garcia Gomez  neorazorx@gmail.com
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
require_model('comm3_visitante.php');

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_stats extends fs_controller
{
   public $diario;
   public $diario_clientes;
   public $page_title;
   public $page_description;
   public $page_keywords;
   public $paises;
   public $plugins;
   private $rid;
   public $semanal;
   public $stat_items;
   public $tablas;
   public $versiones;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Comunidad', 'informes', FALSE, TRUE);
      
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
            
            if($this->rid)
            {
               $stat_item0->rid = $this->rid;
            }
            
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
   
   protected function private_core()
   {
      $this->rid = FALSE;
      $stat0 = new comm3_stat();
      
      $this->semanal = isset($_GET['semanal']);
      if($this->semanal)
      {
         $this->diario = $stat0->semanal();
      }
      else
         $this->diario = $stat0->diario();
      
      $this->versiones = $stat0->versiones();
      
      $stat_item0 = new comm3_stat_item();
      $this->stat_items = $stat_item0->all();
      $this->paises = $stat_item0->agrupado_paises();
      $this->plugins = $stat_item0->agrupado_plugins();
      
      $this->tablas = $this->get_datos_tablas();
      
      $visit0 = new comm3_visitante();
      $this->diario_clientes = $visit0->semanal();
   }
   
   protected function public_core()
   {
      $this->page_title = 'Estadísticas &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Estadísticas de uso de FacturaScripts.';
      $this->page_keywords = 'facturascripts, eneboo, abanq, woocommerce, prestashop, facturae';
      $this->template = 'public/stats';
      
      /**
       * Necesitamos un identificador para el visitante.
       * Así luego podemos relacioner sus comentarios y preguntas.
       */
      $this->rid = $this->random_string(30);
      if( isset($_COOKIE['rid']) )
      {
         $this->rid = $_COOKIE['rid'];
      }
      else
      {
         setcookie('rid', $this->rid, time()+FS_COOKIES_EXPIRE, '/');
      }
      
      $stat0 = new comm3_stat();
      $this->diario = $stat0->semanal();
      
      $visit0 = new comm3_visitante();
      $this->diario_clientes = $visit0->semanal();
      
      /// ahora cambiamos instalaciones activas por total usuarios para la parte pública
      $suma = 0;
      foreach($this->diario as $i => $value)
      {
         $this->diario[$i]['activos'] = $suma;
         foreach($this->diario_clientes as $value2)
         {
            if($value['fecha'] == $value2['fecha'])
            {
               $this->diario[$i]['activos'] = $value2['suma'];
               $suma = $value2['suma'];
               break;
            }
         }
      }
      
      $stat_item0 = new comm3_stat_item();
      $this->paises = $stat_item0->agrupado_paises();
   }
   
   public function diario_reverse()
   {
      return array_reverse($this->diario);
   }
   
   public function diario_clientes_reverse()
   {
      return array_reverse($this->diario_clientes);
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
      $data = $this->db->select("select perfil,count(rid) as contador from comm3_visitantes group by perfil order by contador desc;");
      if($data)
      {
         foreach($data as $d)
         {
            $tablas[] = array('tabla' => 'Visitantes', 'agrupacion' => $d['perfil'], 'contador' => intval($d['contador']));
         }
      }
      
      /// items
      $data = $this->db->select("select count(id) as contador from comm3_items;");
      if($data)
      {
         $tablas[] = array('tabla' => 'Items', 'agrupacion' => '*', 'contador' => intval($data[0]['contador']));
      }
      $data = $this->db->select("select tipo,count(id) as contador from comm3_items group by tipo order by contador desc;");
      if($data)
      {
         foreach($data as $d)
         {
            $tablas[] = array('tabla' => 'Items', 'agrupacion' => $d['tipo'], 'contador' => intval($d['contador']));
         }
      }
      
      /// comentarios
      $data = $this->db->select("select count(id) as contador from comm3_comments;");
      if($data)
      {
         $tablas[] = array('tabla' => 'Comentarios', 'agrupacion' => '*', 'contador' => intval($data[0]['contador']));
      }
      
      return $tablas;
   }
   
   public function num_compradores($resto = FALSE)
   {
      $num = 0;
      
      $sql = "select count(distinct email) as total from comm3_plugin_keys;";
      if($resto)
      {
         $sql = "select count(email) as total from comm3_visitantes;";
      }
      
      $data = $this->db->select($sql);
      if($data)
      {
         $num = intval($data[0]['total']);
      }
      
      return $num;
   }
}
