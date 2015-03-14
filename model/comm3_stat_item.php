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

/**
 * Description of stat_item
 *
 * @author carlos
 */
class comm3_stat_item extends fs_model
{
   public $ip;
   public $fecha;
   public $rid;
   public $codpais;
   public $version;
   public $plugins;
   
   public function __construct($s = FALSE)
   {
      parent::__construct('comm3_stat_items', 'plugins/community3/');
      if($s)
      {
         $this->ip = $s['ip'];
         $this->fecha = date('d-m-Y', strtotime($s['fecha']));
         $this->rid = $s['rid'];
         $this->codpais = $s['codpais'];
         $this->version = $s['version'];
         $this->plugins = $s['plugins'];
      }
      else
      {
         $this->ip = NULL;
         $this->fecha = date('d-m-Y');
         $this->rid = NULL;
         $this->codpais = NULL;
         $this->version = NULL;
         $this->plugins = '';
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function get($ip, $fecha)
   {
      $data = $this->db->select("SELECT * FROM comm3_stat_items WHERE ip = ".$this->var2str($ip)." AND fecha = ".$this->var2str($fecha).";");
      if($data)
      {
         return new comm3_stat_item($data[0]);
      }
      else
         return FALSE;
   }
   
   public function get_by_rid($rid)
   {
      $data = $this->db->select("SELECT * FROM comm3_stat_items WHERE rid = ".$this->var2str($rid).";");
      if($data)
      {
         return new comm3_stat_item($data[0]);
      }
      else
         return FALSE;
   }

   public function exists()
   {
      if( is_null($this->ip) )
      {
         return FALSE;
      }
      else
         return $this->db->select("SELECT * FROM comm3_stat_items WHERE ip = ".$this->var2str($this->ip)." AND fecha = ".$this->var2str($this->fecha).";");
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE comm3_stat_items SET codpais = ".$this->var2str($this->codpais).", version = ".$this->var2str($this->version).",
            rid = ".$this->var2str($this->rid).", plugins = ".$this->var2str($this->plugins)."
            WHERE ip = ".$this->var2str($this->ip)." AND fecha = ".$this->var2str($this->fecha).";";
      }
      else
      {
         $sql = "INSERT INTO comm3_stat_items (ip,fecha,rid,codpais,version,plugins) VALUES
            (".$this->var2str($this->ip).",".$this->var2str($this->fecha).",".$this->var2str($this->rid).",
            ".$this->var2str($this->codpais).",".$this->var2str($this->version).",".$this->var2str($this->plugins).");";
      }
      
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM comm3_stat_items WHERE ip = ".$this->var2str($this->ip)." AND fecha = ".$this->var2str($this->fecha).";");
   }
   
   public function all($offset = 0)
   {
      $vlist = array();
      
      $data = $this->db->select_limit("SELECT * FROM comm3_stat_items ORDER BY fecha DESC, version DESC", FS_ITEM_LIMIT, $offset);
      if($data)
      {
         foreach($data as $d)
            $vlist[] = new comm3_stat_item($d);
      }
      
      return $vlist;
   }
   
   public function all_by_ip($ip, $offset = 0)
   {
      $vlist = array();
      
      $data = $this->db->select_limit("SELECT * FROM comm3_stat_items WHERE ip = ".$this->var2str($ip)." ORDER BY fecha DESC, version DESC", FS_ITEM_LIMIT, $offset);
      if($data)
      {
         foreach($data as $d)
            $vlist[] = new comm3_stat_item($d);
      }
      
      return $vlist;
   }
   
   public function agrupado()
   {
      $alist = array();
      
      $data = $this->db->select("SELECT fecha,version,COUNT(*) as total FROM comm3_stat_items GROUP BY fecha, version ORDER BY fecha DESC;");
      if($data)
      {
         foreach($data as $d)
         {
            $alist[] = array(
                'fecha' => date('d-m-Y', strtotime($d['fecha'])),
                'version' => $d['version'],
                'activos' => intval($d['total'])
            );
         }
      }
      
      return $alist;
   }
   
   public function agrupado_paises()
   {
      $alist = array();
      
      $data = $this->db->select("SELECT codpais,COUNT(*) as total FROM comm3_stat_items GROUP BY codpais ORDER BY total DESC;");
      if($data)
      {
         $total = 0;
         foreach($data as $d)
         {
            if( strlen($d['codpais']) > 0)
            {
               $alist[] = array(
                   'codpais' => $d['codpais'],
                   'activos' => intval($d['total']),
                   'porcentaje' => 0
               );
               
               $total += intval($d['total']);
            }
         }
         
         foreach($alist as $i => $value)
         {
            if($total > 0)
            {
               $alist[$i]['porcentaje'] = $value['activos']/$total*100;
            }
         }
      }
      
      return $alist;
   }
   
   public function agrupado_plugins()
   {
      $plist = array();
      
      $data = $this->db->select_limit("SELECT plugins FROM comm3_stat_items", 1000, 0);
      if($data)
      {
         $total = 0;
         foreach($data as $d)
         {
            if( strlen($d['plugins']) > 2)
            {
               $aux = explode(',', str_replace(array('[',']'), array('',''), $d['plugins']) );
               foreach($aux as $a)
               {
                  if( isset($plist[$a]) )
                  {
                     $plist[$a]['total']++;
                  }
                  else
                     $plist[$a] = array('total'=>1, 'porcentaje'=>0);
                  
                  $total++;
               }
            }
         }
         
         foreach($plist as $i => $value)
         {
            if($total > 0)
            {
               $plist[$i]['porcentaje'] = $value['total']/$total*100;
            }
         }
         
         /// ordenamos
         uasort($plist, function($a, $b) {
            if($a == $b)
            {
               return 0;
            }
            else if($a > $b)
            {
               return -1;
            }
            else
               return 1;
         });
      }
      
      return $plist;
   }
}
