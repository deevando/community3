<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
      }
      else
      {
         $this->email = NULL;
         $this->rid = NULL;
         $this->perfil = NULL;
         $this->codpais = NULL;
         $this->nick = NULL;
         $this->first_login = time();
         $this->last_login = time();
         $this->last_ip = NULL;
         $this->last_browser = NULL;
      }
   }
   
   protected function install()
   {
      return '';
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
            last_ip = ".$this->var2str($this->last_ip).", last_browser = ".$this->var2str($this->last_browser)."
            WHERE email = ".$this->var2str($this->email).";";
      }
      else
      {
         $sql = "INSERT INTO comm3_visitantes (email,perfil,codpais,nick,first_login,last_login,last_ip,last_browser,rid)
            VALUES (".$this->var2str($this->email).",".$this->var2str($this->perfil).",".$this->var2str($this->codpais).",
            ".$this->var2str($this->nick).",".$this->var2str($this->first_login).",".$this->var2str($this->last_login).",
            ".$this->var2str($this->last_ip).",".$this->var2str($this->last_browser).",".$this->var2str($this->rid).");";
      }
      
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("SELECT * FROM comm3_visitantes WHERE email = ".$this->var2str($this->email).";");
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
}
