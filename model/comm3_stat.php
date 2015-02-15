<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of stat
 *
 * @author carlos
 */
class comm3_stat extends fs_model
{
   public $fecha;
   public $version;
   public $descargas;
   public $activos;
   
   public function __construct($s = FALSE)
   {
      parent::__construct('comm3_stats', 'plugins/community3/');
      if($s)
      {
         $this->fecha = date('d-m-Y', strtotime($s['fecha']));
         $this->version = $s['version'];
         $this->descargas = intval($s['descargas']);
         $this->activos = intval($s['activos']);
      }
      else
      {
         $this->fecha = date('d-m-Y');
         $this->version = NULL;
         $this->descargas = 0;
         $this->activos = 0;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function get($fecha, $version)
   {
      $data = $this->db->select("SELECT * FROM comm3_stats WHERE fecha = ".$this->var2str($fecha)." AND version = ".$this->var2str($version).";");
      if($data)
      {
         return new comm3_stat($data[0]);
      }
      else
         return FALSE;
   }
   
   public function exists()
   {
      if( is_null($this->fecha) OR is_null($this->version) )
      {
         return FALSE;
      }
      else
         return $this->db->select("SELECT * FROM comm3_stats WHERE fecha = ".$this->var2str($this->fecha)." AND version = ".$this->var2str($this->version).";");
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE comm3_stats SET descargas = ".$this->var2str($this->descargas).", activos = ".$this->var2str($this->activos)."
            WHERE fecha = ".$this->var2str($this->fecha)." AND version = ".$this->var2str($this->version).";";
      }
      else
      {
         $sql = "INSERT INTO comm3_stats (fecha,version,descargas,activos) VALUES
            (".$this->var2str($this->fecha).",".$this->var2str($this->version).",
            ".$this->var2str($this->descargas).",".$this->var2str($this->activos).");";
      }
      
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM comm3_stats WHERE fecha = ".$this->var2str($this->fecha)." AND version = ".$this->var2str($this->version).";");
   }
   
   public function all()
   {
      $vlist = array();
      
      $data = $this->db->select("SELECT * FROM comm3_stats ORDER BY fecha DESC, version DESC;");
      if($data)
      {
         foreach($data as $d)
            $vlist[] = new comm3_stat($d);
      }
      
      return $vlist;
   }
}
