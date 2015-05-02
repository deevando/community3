<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2015  Francesc Pineda Segarra  shawe.ewahs@gmail.com
 * Copyright (C) 2015  Carlos García Gómez      neorazorx@gmail.com
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

class comm3_plugin extends fs_model
{
   public $id;
   public $nick;
   public $nombre;
   public $descripcion;
   public $link;
   public $zip_link;
   public $estable;
   public $version;
   public $ultima_modificacion;
   public $descargas;
   
   public function __construct($v = FALSE)
   {
      parent::__construct('comm3_plugins', 'plugins/community3/');
      if($v)
      {
         $this->id = $this->intval($v['id']);
         $this->nick = $v['nick'];
         $this->nombre = $v['nombre'];
         $this->descripcion = $v['descripcion'];
         $this->link = $v['link'];
         $this->zip_link = $v['zip_link'];
         $this->estable = $this->str2bool($v['estable']);
         $this->version = intval($v['version']);
         $this->ultima_modificacion = date('d-m-Y', strtotime($v['ultima_modificacion']));
         $this->descargas = intval($v['descargas']);
      }
      else
      {
         $this->id = NULL;
         $this->nick = NULL;
         $this->nombre = NULL;
         $this->descripcion = NULL;
         $this->changelog = NULL;
         $this->link = NULL;
         $this->zip_link = NULL;
         $this->estable = FALSE;
         $this->version = 1;
         $this->ultima_modificacion = date('d-m-Y');
         $this->descargas = 0;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function url()
   {
      if( is_null($this->id) )
      {
         return 'index.php?page=community_plugins';
      }
      else
      {
         return 'index.php?page=community_plugins&id='.$this->id;
      }
   }
   
   public function get($id)
   {
      $data = $this->db->select("SELECT * FROM ". $this->table_name ." WHERE id = ".$this->var2str($id).";");
      if($data)
      {
         return new comm3_plugin($data[0]);
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
         return $this->db->select("SELECT * FROM ". $this->table_name ." WHERE id = ".$this->var2str($this->id).";");
      }
   }
   
   public function save()
   {
      $this->descripcion = $this->no_html($this->descripcion);
      
      if( $this->exists() )
      {
         $sql = "UPDATE ".$this->table_name." SET  nick = ".$this->var2str($this->nick).
                 ", nombre = ".$this->var2str($this->nombre).
                 ", descripcion = ".$this->var2str($this->descripcion).
                 ", link = ".$this->var2str($this->link).
                 ", zip_link = ".$this->var2str($this->zip_link).
                 ", estable = ".$this->var2str($this->estable).
                 ", version = ".$this->var2str($this->version).
                 ", ultima_modificacion = ".$this->var2str($this->ultima_modificacion).
                 ",  descargas = ".$this->var2str($this->descargas).
                 " WHERE id = ".$this->var2str($this->id).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (nick,nombre,descripcion,link,zip_link,estable,version,ultima_modificacion,descargas) VALUES (".
                 $this->var2str($this->nick).",".$this->var2str($this->nombre).",".$this->var2str($this->descripcion).",".
                 $this->var2str($this->link).",".$this->var2str($this->zip_link).",".$this->var2str($this->estable).",".
                 $this->var2str($this->version).",".$this->var2str($this->ultima_modificacion).",".$this->var2str($this->descargas).");";
         
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
      return $this->db->exec("DELETE FROM ". $this->table_name ." WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function all()
   {
      $vlist = array();
      
      $data = $this->db->select("SELECT * FROM ". $this->table_name ." ORDER BY lower(nombre) ASC, nick ASC;");
      if($data)
      {
         foreach($data as $d)
         {
            $vlist[] = new comm3_plugin($d);
         }
      }
      
      return $vlist;
   }
   
   public function all_by_dev($nick)
   {
      $vlist = array();
      
      $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE nick = ".$this->var2str($nick)." ORDER BY lower(nombre) ASC;");
      if($data)
      {
         foreach($data as $d)
         {
            $vlist[] = new comm3_plugin($d);
         }
      }
      
      return $vlist;
   }
}