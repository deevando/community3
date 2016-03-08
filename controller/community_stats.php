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

require_once __DIR__.'/community_home.php';
require_model('comm3_plugin.php');
require_model('comm3_plugin_key.php');
require_model('comm3_stat.php');
require_model('comm3_stat_item.php');

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_stats extends community_home
{
   public $diario;
   public $mensual;
   public $mensual_clientes;
   public $mostrar;
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
      parent::private_core();
      
      $this->mostrar = 'uso';
      if( isset($_GET['mostrar']) )
      {
         $this->mostrar = $_GET['mostrar'];
      }
      
      if($this->mostrar == 'uso')
      {
         $stat0 = new comm3_stat();
         $stat_item0 = new comm3_stat_item();
         
         $this->diario = array_reverse( $stat0->diario() );
         $this->mensual = $stat0->mensual();
         $this->versiones = $stat0->versiones();
         $this->tablas = $this->get_datos_tablas();
      }
      else
      {
         $visitante = new comm3_visitante();
         $this->mensual_clientes = $visitante->mensual();
      }
   }
   
   protected function public_core()
   {
      parent::public_core();
      
      $this->page_title = 'Estadísticas &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Estadísticas de uso de FacturaScripts.';
      $this->page_keywords = 'facturascripts, eneboo, abanq, woocommerce, prestashop, facturae';
      $this->template = 'public/stats';
   }
   
   private function get_datos_tablas()
   {
      $tablas = array();
      
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
   
   public function paises()
   {
      $sti0 = new comm3_stat_item();
      $paises = $sti0->agrupado_paises();
      foreach($paises as $i => $value)
      {
         $paises[$i]['clientes'] = 0;
         $paises[$i]['clientes_p'] = 0;
      }
      
      $visit0 = new comm3_visitante();
      foreach( $visit0->agrupado_paises() as $pa )
      {
         foreach($paises as $i => $value)
         {
            if($value['codpais'] == $pa['codpais'])
            {
               $paises[$i]['clientes'] = $pa['clientes'];
               $paises[$i]['clientes_p'] = $pa['porcentaje'];
            }
         }
      }
      
      return $paises;
   }
   
   public function provincia($codpais)
   {
      $visit0 = new comm3_visitante();
      return $visit0->agrupado_provincia($codpais);
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
   
   /**
    * Devuelve el tiempo transcurrido de media entre el registro del cliente y la
    * primera compra.
    * @return int
    */
   public function primera_compra()
   {
      $time = '0';
      
      $sql = "select email,min(fecha) as fecha from comm3_plugin_keys group by email;";
      $data = $this->db->select($sql);
      if($data)
      {
         $sumas = 0;
         $num = 0;
         
         $visit0 = new comm3_visitante();
         foreach($data as $d)
         {
            $visitante = $visit0->get($d['email']);
            if($visitante)
            {
               if( strtotime($d['fecha']) > $visitante->first_login)
               {
                  $sumas += strtotime($d['fecha']) - $visitante->first_login;
               }
               $num++;
            }
         }
         
         if($num > 0)
         {
            $time = $sumas/$num;
         }
      }
      
      return $this->timesince($time);
   }
   
   private function timesince($time)
   {
      if($time <= 60)
      {
         $rounded = round($time/60,0);
         if($rounded == 0)
         {
            return 'nada';
         }
         else if($rounded == 1)
         {
            return $rounded.' segundo';
         }
         else
         {
            return $rounded.' segundos';
         }
      }
      else if(60 < $time && $time <= 3600)
      {
         $rounded = round($time/60,0);
         if($rounded == 1)
         {
            return $rounded.' minuto';
         }
         else
         {
            return $rounded.' minutos';
         }
      }
      else if(3600 < $time && $time <= 86400)
      {
         $rounded = round($time/3600,0);
         if($rounded == 1)
         {
            return $rounded.' hora';
         }
         else
         {
            return $rounded.' horas';
         }
      }
      else if(86400 < $time && $time <= 604800)
      {
         $rounded = round($time/86400,0);
         if($rounded == 1)
         {
            return $rounded.' día';
         }
         else
         {
            return $rounded.' días';
         }
      }
      else if(604800 < $time && $time <= 2592000)
      {
         $rounded = round($time/604800,0);
         if($rounded == 1)
         {
            return $rounded.' semana';
         }
         else
         {
            return $rounded.' semanas';
         }
      }
      else if(2592000 < $time && $time <= 29030400)
      {
         $rounded = round($time/2592000,0);
         if($rounded == 1)
         {
            return $rounded.' mes';
         }
         else
         {
            return $rounded.' meses';
         }
      }
      else if($time > 29030400)
      {
         return 'más de un año';
      }
   }
   
   public function plugins()
   {
      $sti0 = new comm3_stat_item();
      $plugins = $sti0->agrupado_plugins();
      
      $ventas = 0;
      $pl0 = new comm3_plugin();
      $pk0 = new comm3_plugin_key();
      foreach($plugins as $i => $value)
      {
         $plugins[$i]['url'] = FALSE;
         $plugins[$i]['ventas'] = 0;
         $plugins[$i]['actualizaciones'] = 0;
         
         $plugin = $pl0->get_by_nombre($i);
         if($plugin)
         {
            $plugins[$i]['url'] = $plugin->url();
            
            foreach($pk0->all_from_plugin($plugin->id) as $pk)
            {
               $plugins[$i]['ventas'] += 1;
               $plugins[$i]['actualizaciones'] += $pk->descargas;
               $ventas++;
            }
         }
      }
      
      foreach($plugins as $i => $value)
      {
         $plugins[$i]['ventas_p'] = $value['ventas']/$ventas*100;
      }
      
      return $plugins;
   }
}
