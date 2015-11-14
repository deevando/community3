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
   public $descripcion_html;
   public $link;
   public $zip_link;
   public $imagen;
   public $estable;
   public $version;
   public $creado;
   public $ultima_modificacion;
   public $descargas;
   public $private_update_name;
   public $private_update_key;
   public $oculto;
   public $referencia;
   
   public function __construct($v = FALSE)
   {
      parent::__construct('comm3_plugins', 'plugins/community3/');
      if($v)
      {
         $this->id = $this->intval($v['id']);
         $this->nick = $v['nick'];
         $this->nombre = $v['nombre'];
         $this->descripcion = $v['descripcion'];
         $this->descripcion_html = $v['descripcion_html'];
         $this->link = $v['link'];
         $this->zip_link = $v['zip_link'];
         $this->imagen = $v['imagen'];
         $this->estable = $this->str2bool($v['estable']);
         $this->version = intval($v['version']);
         
         if( is_null($v['creado']) )
         {
            $this->creado = date('d-m-Y', strtotime($v['ultima_modificacion']));
         }
         else
         {
            $this->creado = date('d-m-Y', strtotime($v['creado']));
         }
         
         $this->ultima_modificacion = date('d-m-Y', strtotime($v['ultima_modificacion']));
         $this->descargas = intval($v['descargas']);
         $this->private_update_name = $v['private_update_name'];
         $this->private_update_key = $v['private_update_key'];
         $this->oculto = $this->str2bool($v['oculto']);
         $this->referencia = $v['referencia'];
      }
      else
      {
         $this->id = NULL;
         $this->nick = NULL;
         $this->nombre = NULL;
         $this->descripcion = NULL;
         $this->descripcion_html = NULL;
         $this->changelog = NULL;
         $this->link = NULL;
         $this->zip_link = NULL;
         $this->imagen = NULL;
         $this->estable = FALSE;
         $this->version = 1;
         $this->creado = date('d-m-Y');
         $this->ultima_modificacion = date('d-m-Y');
         $this->descargas = 0;
         $this->private_update_name = NULL;
         $this->private_update_key = NULL;
         $this->oculto = FALSE;
         $this->referencia = NULL;
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
         return 'index.php?page=community_edit_plugin&id='.$this->id;
      }
   }
   
   public function descripcion_html()
   {
      if($this->descripcion_html == '')
      {
         return nl2br($this->descripcion);
      }
      else
      {
         return str_replace(
                 array('&lt;','&gt;','&quot;','&#39;'),
                 array('<','>','"',"'"),
                 $this->descripcion_html
         );
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
   
   public function get_by_nombre($nombre)
   {
      $data = $this->db->select("SELECT * FROM ". $this->table_name ." WHERE nombre = ".$this->var2str($nombre).";");
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
      $this->descripcion_html = $this->no_html($this->descripcion_html);
      
      if( $this->exists() )
      {
         $sql = "UPDATE ".$this->table_name." SET  nick = ".$this->var2str($this->nick).
                 ", nombre = ".$this->var2str($this->nombre).
                 ", descripcion = ".$this->var2str($this->descripcion).
                 ", descripcion_html = ".$this->var2str($this->descripcion_html).
                 ", link = ".$this->var2str($this->link).
                 ", zip_link = ".$this->var2str($this->zip_link).
                 ", imagen = ".$this->var2str($this->imagen).
                 ", estable = ".$this->var2str($this->estable).
                 ", version = ".$this->var2str($this->version).
                 ", creado = ".$this->var2str($this->creado).
                 ", ultima_modificacion = ".$this->var2str($this->ultima_modificacion).
                 ", descargas = ".$this->var2str($this->descargas).
                 ", private_update_name = ".$this->var2str($this->private_update_name).
                 ", private_update_key = ".$this->var2str($this->private_update_key).
                 ", oculto = ".$this->var2str($this->oculto).
                 ", referencia = ".$this->var2str($this->referencia).
                 " WHERE id = ".$this->var2str($this->id).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (nick,nombre,descripcion,descripcion_html,link,"
                 . "zip_link,imagen,estable,version,creado,ultima_modificacion,descargas,"
                 . "private_update_name,private_update_key,oculto,referencia) VALUES ".
                 "(".$this->var2str($this->nick).
                 ",".$this->var2str($this->nombre).
                 ",".$this->var2str($this->descripcion).
                 ",".$this->var2str($this->descripcion_html).
                 ",".$this->var2str($this->link).
                 ",".$this->var2str($this->zip_link).
                 ",".$this->var2str($this->imagen).
                 ",".$this->var2str($this->estable).
                 ",".$this->var2str($this->version).
                 ",".$this->var2str($this->creado).
                 ",".$this->var2str($this->ultima_modificacion).
                 ",".$this->var2str($this->descargas).
                 ",".$this->var2str($this->private_update_name).
                 ",".$this->var2str($this->private_update_key).
                 ",".$this->var2str($this->oculto).
                 ",".$this->var2str($this->referencia).");";
         
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
   
   public function all($order = 'lower(nombre) ASC, nick ASC')
   {
      $vlist = array();
      
      $data = $this->db->select("SELECT * FROM ". $this->table_name ." ORDER BY ".$order.";");
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