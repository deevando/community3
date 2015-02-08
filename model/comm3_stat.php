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
class visitante extends fs_model
{
   public $email;
   public $rid;
   public $perfil;
   public $codpais;
   public $nick;
   public $last_login;
   public $last_login_time;
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
         $this->last_login = date('d-m-Y', strtotime($v['last_login']));
         
         $this->last_login_time = '00:00:00';
         if( !is_null($v['last_login_time']) )
         {
            $this->last_login_time = $v['last_login_time'];
         }
         
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
         $this->last_login = date('d-m-Y');
         $this->last_login_time = date('h:i:s');
         $this->last_ip = NULL;
         $this->last_browser = NULL;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function get($email)
   {
      $data = $this->db->select("SELECT * FROM visitantes WHERE email = ".$this->var2str($email).";");
      if($data)
      {
         return new visitante($data[0]);
      }
      else
         return FALSE;
   }
   
   public function get_by_rid($rid)
   {
      $data = $this->db->select("SELECT * FROM visitantes WHERE rid = ".$this->var2str($rid).";");
      if($data)
      {
         return new visitante($data[0]);
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
         return $this->db->select("SELECT * FROM visitantes WHERE email = ".$this->var2str($this->email).";");
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "";
      }
      else
      {
         $sql = "INSERT INTO visitantes (email,perfil,codpais,nick,last_login,last_login_time,last_ip,last_browser,rid)
            VALUES (".$this->var2str($this->email).",".$this->var2str($this->perfil).",".$this->var2str($this->codpais).",
            ".$this->var2str($this->nick).",".$this->var2str($this->last_login).",".$this->var2str($this->last_login_time).",
            ".$this->var2str($this->last_ip).",".$this->var2str($this->last_browser).",".$this->var2str($this->rid).");";
      }
      
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("SELECT * FROM visitantes WHERE email = ".$this->var2str($this->email).";");
   }
   
   public function all()
   {
      $vlist = array();
      
      $data = $this->db->select("SELECT * FROM visitantes ORDER BY last_login DESC, last_login_time DESC;");
      if($data)
      {
         foreach($data as $d)
            $vlist[] = new visitante($d);
      }
      
      return $vlist;
   }
}
