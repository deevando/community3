<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
 */

/**
 * Description of comm3_plugin_key
 *
 * @author carlos
 */
class comm3_plugin_key extends fs_model
{
   public $id;
   public $email;
   public $idplugin;
   public $descargas;
   public $private_update_key;
   public $fecha;
   public $hora;
   
   public function __construct($k = FALSE)
   {
      parent::__construct('comm3_plugin_keys');
      if($k)
      {
         $this->id = $this->intval($k['id']);
         $this->email = $k['email'];
         $this->idplugin = $this->intval($k['idplugin']);
         $this->plugin = $k['plugin'];
         $this->descargas = intval($k['descargas']);
         $this->private_update_key = $k['private_update_key'];
         $this->fecha = date('d-m-Y', strtotime($k['fecha']));
         $this->hora = date('h:i:s', strtotime($k['hora']));
      }
      else
      {
         $this->id = NULL;
         $this->email = NULL;
         $this->idplugin = NULL;
         $this->plugin = NULL;
         $this->descargas = 0;
         $this->private_update_key = $this->random_string(99);
         $this->fecha = NULL;
         $this->hora = NULL;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function get_by_key($key)
   {
      $data = $this->db->select("SELECT * FROM comm3_plugin_keys WHERE private_update_key = ".$this->var2str($key).";");
      if($data)
      {
         return new comm3_plugin_key($data[0]);
      }
      else
      {
         return FALSE;
      }
   }
   
   public function exists()
   {
      if( is_null($this->id) )
      {
         return FALSE;
      }
      else
      {
         return $this->db->select("SELECT * FROM comm3_plugin_keys WHERE id = ".$this->var2str($this->id).";");
      }
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE comm3_plugin_keys SET descargas = ".$this->var2str($this->descargas)
                 .", private_update_key = ".$this->var2str($this->private_update_key)
                 .", email = ".$this->var2str($this->email)
                 .", idplugin = ".$this->var2str($this->idplugin)
                 .", plugin = ".$this->var2str($this->plugin)
                 .", fecha = ".$this->var2str($this->fecha)
                 .", hora = ".$this->var2str($this->hora)
                 ."  WHERE id = ".$this->var2str($this->id).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO comm3_plugin_keys (email,idplugin,plugin,descargas,private_update_key,fecha,hora) VALUES "
                 . "(".$this->var2str($this->email)
                 . ",".$this->var2str($this->idplugin)
                 . ",".$this->var2str($this->plugin)
                 . ",".$this->var2str($this->descargas)
                 . ",".$this->var2str($this->private_update_key)
                 . ",".$this->var2str($this->fecha)
                 . ",".$this->var2str($this->hora).");";
         
         if( $this->db->exec($sql) )
         {
            $this->id = $this->db->lastval();
            return TRUE;
         }
         else
         {
            return FALSE;
         }
      }
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM comm3_plugin_keys WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function all_from_plugin($idplugin)
   {
      $lista = array();
      $sql = "SELECT * FROM comm3_plugin_keys WHERE idplugin = ".$this->var2str($idplugin)
              ." ORDER BY fecha DESC, hora DESC;";
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $lista[] = new comm3_plugin_key($d);
         }
      }
      
      return $lista;
   }
   
   public function all_from_email($email)
   {
      $lista = array();
      $sql = "SELECT * FROM comm3_plugin_keys WHERE email = ".$this->var2str($email)
              ." ORDER BY fecha DESC, hora DESC;";
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $lista[] = new comm3_plugin_key($d);
         }
      }
      
      return $lista;
   }
}
