<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015-2016, Carlos García Gómez. All Rights Reserved. 
 */

/**
 * Description of comm3_partner
 *
 * @author carlos
 */
class comm3_partner extends fs_model
{
   public $nombre;
   public $nombrecomercial;
   public $descripcion;
   public $link;
   public $administrador;
   
   public function __construct($p = FALSE)
   {
      parent::__construct('comm3_partners');
      if($p)
      {
         $this->nombre = $p['nombre'];
         $this->nombrecomercial = $p['nombrecomercial'];
         $this->descripcion = $p['descripcion'];
         $this->link = $p['link'];
         $this->administrador = $p['administrador'];
      }
      else
      {
         $this->nombre = NULL;
         $this->nombrecomercial = NULL;
         $this->descripcion = NULL;
         $this->link = NULL;
         $this->administrador = NULL;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function descripcion()
   {
      return nl2br($this->descripcion);
   }
   
   public function get($nombre)
   {
      $data = $this->db->select("SELECT * FROM comm3_partners WHERE nombre = ".$this->var2str($nombre).";");
      if($data)
      {
         return new comm3_partner($data[0]);
      }
      else
      {
         return FALSE;
      }
   }
   
   public function exists()
   {
      if( is_null($this->nombre) )
      {
         return FALSE;
      }
      else
      {
         return $this->db->select("SELECT * FROM comm3_partners WHERE nombre = ".$this->var2str($this->nombre).";");
      }
   }
   
   public function save()
   {
      $this->nombre = $this->no_html($this->nombre);
      $this->nombrecomercial = $this->no_html($this->nombrecomercial);
      $this->descripcion = $this->no_html($this->descripcion);
      $this->link = $this->no_html($this->link);
      
      if( $this->exists() )
      {
         $sql = "UPDATE comm3_partners SET descripcion = ".$this->var2str($this->descripcion)
                 .", nombrecomercial = ".$this->var2str($this->nombrecomercial)
                 .", link = ".$this->var2str($this->link)
                 .", administrador = ".$this->var2str($this->administrador)
                 ."  WHERE nombre = ".$this->var2str($this->nombre).";";
      }
      else
      {
         $sql = "INSERT INTO comm3_partners (nombre,nombrecomercial,descripcion,link,administrador) VALUES "
                 . "(".$this->var2str($this->nombre)
                 . ",".$this->var2str($this->nombrecomercial)
                 . ",".$this->var2str($this->descripcion)
                 . ",".$this->var2str($this->link)
                 . ",".$this->var2str($this->administrador).");";
      }
      
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM comm3_partners WHERE nombre = ".$this->var2str($this->nombre).";");
   }
   
   public function all()
   {
      $lista = array();
      $sql = "SELECT * FROM comm3_partners ORDER BY nombre DESC;";
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $lista[] = new comm3_partner($d);
         }
      }
      
      return $lista;
   }
}
