<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of comment
 *
 * @author carlos
 */
class comm3_comment extends fs_model
{
   public $iditem;
   public $email;
   public $rid;
   public $codpais;
   public $nick;
   public $creado;
   public $ip;
   public $texto;
   
   public function __construct($v = FALSE)
   {
      parent::__construct('comm3_comments', 'plugins/community3/');
      if($v)
      {
         $this->iditem = $v['iditem'];
         $this->email = $v['email'];
         $this->rid = $v['rid'];
         $this->codpais = $v['codpais'];
         $this->nick = $v['nick'];
         $this->creado = $v['creado'];
         $this->ip = $v['ip'];
         $this->texto = $v['texto'];
      }
      else
      {
         $this->iditem = NULL;
         $this->email = NULL;
         $this->rid = NULL;
         $this->codpais = NULL;
         $this->nick = NULL;
         $this->creado = NULL;
         $this->ip = NULL;
         $this->texto = NULL;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function get($id)
   {
      $data = $this->db->select("SELECT * FROM comm3_comments WHERE id = ".$this->var2str($id).";");
      if($data)
      {
         return new comm3_comment($data[0]);
      }
      else
         return FALSE;
   }
   
   public function get_by_iditem($iditem)
   {
      $vlist = array ();
      
      $data = $this->db->select("SELECT * FROM comm3_comments WHERE iditem = ".$this->var2str($iditem).";");
      if($data)
      {
         foreach($data as $d)
            $vlist[] = new comm3_comment($d);
      }
      
      return $vlist;
   }
   
   public function get_by_rid($rid)
   {
      $data = $this->db->select("SELECT * FROM comm3_comments WHERE rid = ".$this->var2str($rid).";");
      if($data)
      {
         return new comm3_comment($data[0]);
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
         return $this->db->select("SELECT * FROM comm3_comments WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "";
      }
      else
      {
         $sql = "INSERT INTO comm3_comments (iditem,email,rid,codpais,nick,creado,ip,texto)
            VALUES (".$this->var2str($this->iditem).",".$this->var2str($this->email).",".$this->var2str($this->rid).",".$this->var2str($this->codpais).",
            ".$this->var2str($this->nick).",".$this->var2str($this->creado).",".$this->var2str($this->ip).",
            ".$this->var2str($this->texto).");";
      }
      
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM comm3_comments WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function all()
   {
      $vlist = array();
      
      $data = $this->db->select("SELECT * FROM comm3_comments ORDER BY creado DESC;");
      if($data)
      {
         foreach($data as $d)
            $vlist[] = new comm3_comment($d);
      }
      
      return $vlist;
   }
}
