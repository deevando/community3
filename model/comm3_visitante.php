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
   public $email;
   public $rid;
   public $perfil;
   public $codpais;
   public $nick;
   public $first_login;
   public $last_login;
   public $last_ip;
   public $last_browser;
   public $privado;
   public $autorizado;
   
   public function __construct($v = FALSE)
   {
      parent::__construct('comm3_visitantes', 'plugins/community3/');
      if($v)
      {
         $this->email = $v['email'];
         $this->rid = $v['rid'];
         $this->perfil = $v['perfil'];
         $this->codpais = $v['codpais'];
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
      }
      else
      {
         $this->email = NULL;
         $this->rid = NULL;
         $this->perfil = 'voluntario';
         $this->codpais = NULL;
         $this->nick = NULL;
         $this->first_login = time();
         $this->last_login = time();
         $this->last_ip = NULL;
         $this->last_browser = NULL;
         $this->privado = FALSE;
         $this->autorizado = NULL;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function url()
   {
      return 'index.php?page=community_colabora&email='.$this->email;
   }
   
   public function first_login()
   {
      return date('d-m-Y H:i:s', $this->first_login);
   }
   
   public function last_login()
   {
      return date('d-m-Y H:i:s', $this->last_login);
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
         $sql = "UPDATE comm3_visitantes SET rid = ".$this->var2str($this->rid).", perfil = ".$this->var2str($this->perfil).",
            codpais = ".$this->var2str($this->codpais).", nick = ".$this->var2str($this->nick).",
            first_login = ".$this->var2str($this->first_login).", last_login = ".$this->var2str($this->last_login).",
            last_ip = ".$this->var2str($this->last_ip).", last_browser = ".$this->var2str($this->last_browser).",
            privado = ".$this->var2str($this->privado).", autorizado = ".$this->var2str($this->autorizado)."
            WHERE email = ".$this->var2str($this->email).";";
      }
      else
      {
         $sql = "INSERT INTO comm3_visitantes (email,perfil,codpais,nick,first_login,last_login,last_ip,last_browser,rid,privado,autorizado)
            VALUES (".$this->var2str($this->email).",".$this->var2str($this->perfil).",".$this->var2str($this->codpais).",
            ".$this->var2str($this->nick).",".$this->var2str($this->first_login).",".$this->var2str($this->last_login).",
            ".$this->var2str($this->last_ip).",".$this->var2str($this->last_browser).",".$this->var2str($this->rid).",
            ".$this->var2str($this->privado).",".$this->var2str($this->autorizado).");";
      }
      
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM comm3_visitantes WHERE email = ".$this->var2str($this->email).";");
   }
   
   public function all()
   {
      $vlist = array();
      
      $data = $this->db->select("SELECT * FROM comm3_visitantes ORDER BY last_login DESC;");
      if($data)
      {
         foreach($data as $d)
            $vlist[] = new comm3_visitante($d);
      }
      
      return $vlist;
   }
   
   public function search_for_user($admin, $nick, $perfil='---', $codpais='---', $orden='first_login DESC')
   {
      $vlist = array();
      
      $sql = "SELECT * FROM comm3_visitantes WHERE ";
      if($admin)
      {
         $sql .= "1 = 1 ";
      }
      else
      {
         $sql .= "autorizado = ".$this->var2str($nick)." ";
      }
      
      if($perfil != '---')
      {
         $sql .= "AND perfil = ".$this->var2str($perfil)." ";
      }
      
      if($codpais != '---')
      {
         $sql .= "AND codpais = ".$this->var2str($codpais)." ";
      }
      
      $sql .= "ORDER BY ".$orden;
      
      $data = $this->db->select_limit($sql, 1000, 0);
      if($data)
      {
         foreach($data as $d)
            $vlist[] = new comm3_visitante($d);
      }
      
      return $vlist;
   }
}
