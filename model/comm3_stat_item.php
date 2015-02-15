<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
      }
      else
      {
         $this->ip = NULL;
         $this->fecha = date('d-m-Y');
         $this->rid = NULL;
         $this->codpais = NULL;
         $this->version = NULL;
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
            rid = ".$this->var2str($this->rid)." WHERE ip = ".$this->var2str($this->ip)." AND fecha = ".$this->var2str($this->fecha).";";
      }
      else
      {
         $sql = "INSERT INTO comm3_stat_items (ip,fecha,rid,codpais,version) VALUES
            (".$this->var2str($this->ip).",".$this->var2str($this->fecha).",".$this->var2str($this->rid).",
            ".$this->var2str($this->codpais).",".$this->var2str($this->version).");";
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
}
