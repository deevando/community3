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
 * Description of visitante
 *
 * @author carlos
 */
class comm3_visitante extends fs_model
{
   /**
    * Clave primaria.
    * @var type 
    */
   public $email;
   public $rid;
   public $perfil;
   public $codpais;
   public $provincia;
   public $ciudad;
   public $nick;
   public $first_login;
   public $last_login;
   public $last_ip;
   public $last_browser;
   public $privado;
   public $autorizado;
   public $autorizado2;
   public $autorizado3;
   public $autorizado4;
   public $autorizado5;
   public $interacciones;
   public $compras;
   
   public function __construct($v = FALSE)
   {
      parent::__construct('comm3_visitantes', 'plugins/community3/');
      if($v)
      {
         $this->email = $v['email'];
         $this->rid = $v['rid'];
         $this->perfil = $v['perfil'];
         $this->codpais = $v['codpais'];
         $this->provincia = $v['provincia'];
         $this->ciudad = $v['ciudad'];
         $this->nick = $v['nick'];
         $this->first_login = intval($v['first_login']);
         $this->last_login = intval($v['last_login']);
         $this->last_ip = $v['last_ip'];
         $this->last_browser = $v['last_browser'];
         
         $this->privado = FALSE;
         if( isset($v['privado']) )
         {
            $this->privado = $this->str2bool($v['privado']);
         }
         
         $this->autorizado = NULL;
         if( isset($v['autorizado']) )
         {
            $this->autorizado = $v['autorizado'];
         }
         
         $this->autorizado2 = NULL;
         if( isset($v['autorizado2']) )
         {
            $this->autorizado2 = $v['autorizado2'];
         }
         
         $this->autorizado3 = NULL;
         if( isset($v['autorizado3']) )
         {
            $this->autorizado3 = $v['autorizado3'];
         }
         
         $this->autorizado4 = NULL;
         if( isset($v['autorizado4']) )
         {
            $this->autorizado4 = $v['autorizado4'];
         }
         
         $this->autorizado5 = NULL;
         if( isset($v['autorizado5']) )
         {
            $this->autorizado5 = $v['autorizado5'];
         }
         
         $this->interacciones = intval($v['interacciones']);
         $this->compras = intval($v['compras']);
      }
      else
      {
         $this->email = NULL;
         $this->rid = NULL;
         $this->perfil = 'voluntario';
         $this->codpais = NULL;
         $this->provincia = NULL;
         $this->ciudad = NULL;
         $this->nick = NULL;
         $this->first_login = time();
         $this->last_login = time();
         $this->last_ip = NULL;
         $this->last_browser = NULL;
         $this->privado = FALSE;
         $this->autorizado = NULL;
         $this->autorizado2 = NULL;
         $this->autorizado3 = NULL;
         $this->autorizado4 = NULL;
         $this->autorizado5 = NULL;
         $this->interacciones = 0;
         $this->compras = 0;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function url()
   {
      if($this->nick)
      {
         return 'index.php?page=community_visitantes&nick='.$this->nick;
      }
      else
      {
         return 'index.php?page=community_visitantes&email='.$this->email;
      }
   }
   
   public function first_login()
   {
      return date('d-m-Y H:i:s', $this->first_login);
   }
   
   public function last_login()
   {
      return date('d-m-Y', $this->last_login);
   }
   
   /**
    * Devuelve TRUE si el usuario es un autorizado del usuario.
    * @param type $nick
    */
   public function autorizado($nick)
   {
      return in_array($nick, array($this->autorizado,$this->autorizado2,$this->autorizado3,$this->autorizado4,$this->autorizado5) );
   }
   
   public function get($email)
   {
      $data = $this->db->select("SELECT * FROM comm3_visitantes WHERE email = ".$this->var2str($email).";");
      if($data)
      {
         return new comm3_visitante($data[0]);
      }
      else
         return FALSE;
   }
   
   public function get_by_rid($rid)
   {
      $data = $this->db->select("SELECT * FROM comm3_visitantes WHERE rid = ".$this->var2str($rid).";");
      if($data)
      {
         return new comm3_visitante($data[0]);
      }
      else
         return FALSE;
   }
   
   public function get_by_nick($nick)
   {
      $data = $this->db->select("SELECT * FROM comm3_visitantes WHERE nick = ".$this->var2str($nick).";");
      if($data)
      {
         return new comm3_visitante($data[0]);
      }
      else
         return FALSE;
   }

   public function exists()
   {
      if( is_null($this->email) )
      {
         return FALSE;
      }
      else
         return $this->db->select("SELECT * FROM comm3_visitantes WHERE email = ".$this->var2str($this->email).";");
   }
   
   public function save()
   {
      $this->last_browser = $this->no_html($this->last_browser);
      
      if( $this->exists() )
      {
         $sql = "UPDATE comm3_visitantes SET rid = ".$this->var2str($this->rid)
                 .", perfil = ".$this->var2str($this->perfil)
                 .", codpais = ".$this->var2str($this->codpais)
                 .", provincia = ".$this->var2str($this->provincia)
                 .", ciudad = ".$this->var2str($this->ciudad)
                 .", nick = ".$this->var2str($this->nick)
                 .", first_login = ".$this->var2str($this->first_login)
                 .", last_login = ".$this->var2str($this->last_login)
                 .", last_ip = ".$this->var2str($this->last_ip)
                 .", last_browser = ".$this->var2str($this->last_browser)
                 .", privado = ".$this->var2str($this->privado)
                 .", autorizado = ".$this->var2str($this->autorizado)
                 .", autorizado2 = ".$this->var2str($this->autorizado2)
                 .", autorizado3 = ".$this->var2str($this->autorizado3)
                 .", autorizado4 = ".$this->var2str($this->autorizado4)
                 .", autorizado5 = ".$this->var2str($this->autorizado5)
                 .", interacciones = ".$this->var2str($this->interacciones)
                 .", compras = ".$this->var2str($this->compras)
                 ."  WHERE email = ".$this->var2str($this->email).";";
      }
      else
      {
         $sql = "INSERT INTO comm3_visitantes (email,perfil,codpais,provincia,ciudad,nick,first_login,
            last_login,last_ip,last_browser,rid,privado,autorizado,autorizado2,autorizado3,
            autorizado4,autorizado5,interacciones,compras) VALUES 
                  (".$this->var2str($this->email).
                 ",".$this->var2str($this->perfil).
                 ",".$this->var2str($this->codpais).
                 ",".$this->var2str($this->provincia).
                 ",".$this->var2str($this->ciudad).
                 ",".$this->var2str($this->nick).
                 ",".$this->var2str($this->first_login).
                 ",".$this->var2str($this->last_login).
                 ",".$this->var2str($this->last_ip).
                 ",".$this->var2str($this->last_browser).
                 ",".$this->var2str($this->rid).
                 ",".$this->var2str($this->privado).
                 ",".$this->var2str($this->autorizado).
                 ",".$this->var2str($this->autorizado2).
                 ",".$this->var2str($this->autorizado3).
                 ",".$this->var2str($this->autorizado4).
                 ",".$this->var2str($this->autorizado5).
                 ",".$this->var2str($this->interacciones).
                 ",".$this->var2str($this->compras).");";
      }
      
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM comm3_visitantes WHERE email = ".$this->var2str($this->email).";");
   }
   
   /**
    * Devuelve un array con los últimos visitantes
    * @return \comm3_visitante
    */
   public function all($offset = 0, $limit = FS_ITEM_LIMIT)
   {
      $vlist = array();
      
      $data = $this->db->select_limit("SELECT * FROM comm3_visitantes ORDER BY last_login DESC", $limit, $offset);
      if($data)
      {
         foreach($data as $d)
            $vlist[] = new comm3_visitante($d);
      }
      
      return $vlist;
   }
   
   public function search_for_user($admin, $nick, $query='', $perfil='---', $codpais='---', $prov='---', $ciudad='---', $compras='---', $orden='last_login DESC')
   {
      $vlist = array();
      
      $sql = "SELECT * FROM comm3_visitantes WHERE ";
      if($admin)
      {
         $sql .= "1 = 1 ";
      }
      else
      {
         $sql .= "(autorizado = ".$this->var2str($nick).
                 " OR autorizado2 = ".$this->var2str($nick).
                 " OR autorizado3 = ".$this->var2str($nick).
                 " OR autorizado4 = ".$this->var2str($nick).
                 " OR autorizado5 = ".$this->var2str($nick).") ";
      }
      
      if($query != '')
      {
         $sql .= "AND lower(email) LIKE '%".$this->no_html( trim( strtolower($query) ) )."%' ";
      }
      
      if($perfil != '---')
      {
         $sql .= "AND perfil = ".$this->var2str($perfil)." ";
      }
      
      if($codpais != '---')
      {
         $sql .= "AND codpais = ".$this->var2str($codpais)." ";
      }
      
      if($prov != '---')
      {
         $sql .= "AND provincia = ".$this->var2str($prov)." ";
      }
      
      if($ciudad != '---')
      {
         $sql .= "AND ciudad = ".$this->var2str($ciudad)." ";
      }
      
      if($compras == 'compradores')
      {
         $sql .= "AND compras > 0 ";
      }
      else if($compras = 'nocompradores')
      {
         $sql .= "AND compras = 0 ";
      }
      
      if($orden == 'nick ASC')
      {
         $sql .= "AND nick IS NOT null AND nick != '' ";
      }
      
      $sql .= "ORDER BY ".$orden;
      
      $data = $this->db->select_limit($sql, 2000, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $vlist[] = new comm3_visitante($d);
         }
      }
      
      return $vlist;
   }
   
   public function interacciones()
   {
      $this->interacciones = 0;
      
      $data = $this->db->select("SELECT COUNT(id) as num FROM comm3_items WHERE email = ".$this->var2str($this->email).";");
      if($data)
      {
         $this->interacciones = intval($data[0]['num']);
      }
      
      $data = $this->db->select("SELECT COUNT(id) as num FROM comm3_comments WHERE email = ".$this->var2str($this->email).";");
      if($data)
      {
         $this->interacciones += intval($data[0]['num']);
      }
      
      return $this->interacciones;
   }
   
   public function compras()
   {
      $this->compras = 0;
      
      $data = $this->db->select("SELECT COUNT(*) as num FROM comm3_plugin_keys WHERE email = ".$this->var2str($this->email).";");
      if($data)
      {
         $this->compras = intval($data[0]['num']);
      }
      
      return $this->compras;
   }
   
   public function semanal()
   {
      $vlist = array();
      
      $sql = "SELECT first_login as fecha,COUNT(rid) as c FROM comm3_visitantes"
              . " GROUP BY fecha ORDER BY fecha DESC";
      $data = $this->db->select_limit($sql, 365, 0);
      if($data)
      {
         $item = array(
             'fecha' => date('#W(Y)'),
             'nuevos' => 0,
             'suma' => 0
         );
         
         $suma = 0;
         foreach($data as $d)
         {
            $suma += intval($d['c']);
         }
         $item['suma'] = $suma;
         
         foreach($data as $d)
         {
            $suma -= intval($d['c']);
            if( date('#W(Y)', intval($d['fecha'])) == $item['fecha'] )
            {
               $item['nuevos'] += intval($d['c']);
            }
            else
            {
               $vlist[] = $item;
               
               $item['fecha'] = date('#W(Y)', intval($d['fecha']));
               $item['nuevos'] = intval($d['c']);
               $item['suma'] = $suma;
            }
         }
      }
      
      return $vlist;
   }
}
