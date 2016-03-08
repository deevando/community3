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

require_once __DIR__.'/community_home.php';
require_model('comm3_plugin.php');
require_model('comm3_plugin_key.php');

/**
 * Description of community_edit_plugin
 *
 * @author carlos
 */
class community_edit_plugin extends community_home
{
   public $allow_delete;
   public $autorizado;
   public $claves;
   public $plugin;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Editar plugin', 'community', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      parent::private_core();
      
      /// ¿El usuario tiene permiso para eliminar en esta página?
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      
      $this->plugin = FALSE;
      if( isset($_REQUEST['id']) )
      {
         $commplug = new comm3_plugin();
         $this->plugin = $commplug->get($_REQUEST['id']);
      }
      
      if($this->plugin)
      {
         $this->autorizado = FALSE;
         if($this->user->admin OR $this->plugin->nick == $this->user->nick)
         {
            $this->autorizado = TRUE;
         }
         else
         {
            $visit0 = new comm3_visitante();
            $visitante = $visit0->get_by_nick($this->plugin->nick);
            if($visitante)
            {
               $this->autorizado = $visitante->autorizado($this->user->nick);
            }
         }
         
         if($this->allow_delete AND !$this->user->admin AND $this->plugin->nick != $this->user->nick)
         {
            $this->allow_delete = FALSE;
         }
         
         if( isset($_GET['key']) )
         {
            $this->download_update();
         }
         else if( $this->plugin->oculto AND !$this->autorizado )
         {
            $this->new_error_msg('No tienes permiso para ver este plugin.');
            $this->plugin = FALSE;
         }
         else if( !$this->autorizado )
         {
            $this->new_advice('No tienes permiso para editar este plugin.');
         }
         else if( isset($_POST['id']) )
         {
            $this->modificar_plugin();
         }
         else if( isset($_GET['rekey']) )
         {
            /// generar nuevo clave de actualización
            $this->plugin->private_update_key = $this->random_string(99);
            if( $this->plugin->save() )
            {
               $this->new_message( "Se han regenerado la clave." );
            }
            else
            {
               $this->new_error_msg( "Ha ocurrido un error al regenerar la clave." );
            }
         }
         else if( isset($_GET['delete_update']) )
         {
            /// eliminar archivo de actualización
            if( file_exists('tmp/private_plugins/'.$this->plugin->private_update_name) )
            {
               unlink('tmp/private_plugins/'.$this->plugin->private_update_name);
               $this->plugin->private_update_name = NULL;
               $this->plugin->save();
            }
            else if( file_exists('tmp/'.FS_TMP_NAME.'private_plugins/'.$this->plugin->private_update_name) )
            {
               if( unlink('tmp/'.FS_TMP_NAME.'private_plugins/'.$this->plugin->private_update_name) )
               {
                  $this->new_message('Archivo eliminado correctamente.');
                  $this->plugin->private_update_name = NULL;
                  $this->plugin->save();
               }
               else
                  $this->new_error_msg('Imposible eliminar el archivo.');
            }
            else
            {
               $this->plugin->private_update_name = NULL;
               $this->plugin->save();
            }
         }
         
         $plk0 = new comm3_plugin_key();
         $this->claves = $plk0->all_from_plugin($this->plugin->id);
      }
      else
      {
         $this->new_error_msg('Plugin no encontrado.');
      }
   }
   
   protected function public_core()
   {
      $this->template = FALSE;
      
      $this->plugin = FALSE;
      if( isset($_GET['id']) )
      {
         $commplug = new comm3_plugin();
         $this->plugin = $commplug->get($_REQUEST['id']);
      }
      
      if($this->plugin)
      {
         if( isset($_GET['key']) )
         {
            $this->download_update();
         }
      }
   }
   
   private function download_update()
   {
      /// devolver enlace para el actualizador
      $this->template = FALSE;
      
      
      if($this->plugin->private_update_name)
      {
         if( file_exists('tmp/'.FS_TMP_NAME.'private_plugins/'.$this->plugin->private_update_name) )
         {
            $error = TRUE;
            if($_GET['key'] == $this->plugin->private_update_key)
            {
               $error = FALSE;
               echo file_get_contents('tmp/'.FS_TMP_NAME.'private_plugins/'.$this->plugin->private_update_name);
            }
            else
            {
               /// comprobamos la clave
               $plk0 = new comm3_plugin_key();
               $plk = $plk0->get_by_key($_GET['key']);
               if($plk)
               {
                  if($plk->idplugin == $this->plugin->id)
                  {
                     $plk->descargas++;
                     $plk->save();
                     
                     $error = FALSE;
                     echo file_get_contents('tmp/'.FS_TMP_NAME.'private_plugins/'.$this->plugin->private_update_name);
                  }
               }
            }
            
            if($error)
            {
               header('HTTP/1.0 403 Forbidden');
               die('ERROR');
            }
         }
         else
         {
            header("HTTP/1.0 404 Not Found");
            die('ERROR');
         }
      }
      else
      {
         header("HTTP/1.0 404 Not Found");
         die('ERROR');
      }
   }
   
   private function modificar_plugin()
   {
      if( isset($_POST['new_update']) )
      {
         /// nuevo archivo de actualización
         if( is_uploaded_file($_FILES['private_update']['tmp_name']) )
         {
            if( !file_exists('tmp/'.FS_TMP_NAME.'private_plugins') )
            {
               mkdir('tmp/'.FS_TMP_NAME.'private_plugins');
            }
            else if( is_null($this->plugin->private_update_name) )
            {
               ///
            }
            else if( file_exists('tmp/'.FS_TMP_NAME.'private_plugins/'.$this->plugin->private_update_name) )
            {
               unlink('tmp/'.FS_TMP_NAME.'private_plugins/'.$this->plugin->private_update_name);
            }
            
            $this->plugin->private_update_name = $this->random_string(50);
            if( file_exists('tmp/'.FS_TMP_NAME.'private_plugins/'.$this->plugin->private_update_name) )
            {
               $this->plugin->private_update_name = NULL;
               $this->new_error_msg('De puta casualidad ya hay un archivo con este nombre.');
            }
            else
            {
               copy($_FILES['private_update']['tmp_name'], 'tmp/'.FS_TMP_NAME.'private_plugins/'.$this->plugin->private_update_name);
               
               $this->plugin->version = intval($_POST['version']);
               $this->plugin->estable = isset($_POST['estable']);
               $this->plugin->ultima_modificacion = $this->today();
               
               if( is_null($this->plugin->private_update_key) )
               {
                  $this->plugin->private_update_key = $this->random_string(99);
               }
            }
         }
         else
            $this->new_error_msg('Archivo no subido.');
      }
      else
      {
         /// modificar el plugin
         $this->plugin->descripcion = $_POST['descripcion'];
         $this->plugin->descripcion_html = $_POST['descripcion_html'];
         $this->plugin->link = $_POST['link'];
         $this->plugin->zip_link = $_POST['zip_link'];
         $this->plugin->imagen = $_POST['imagen'];
         $this->plugin->estable = isset($_POST['estable']);
         $this->plugin->oculto = isset($_POST['oculto']);
         $this->plugin->version = intval($_POST['version']);
         $this->plugin->ultima_modificacion = $_POST['ultima_modificacion'];
         $this->plugin->referencia = $_POST['referencia'];
         
         if($this->user->admin)
         {
            $this->plugin->nick = $_POST['autor'];
         }
      }
      
      if( $this->plugin->save() )
      {
         $this->new_message( "Se han modificado los datos del plugin." );
      }
      else
      {
         $this->new_error_msg( "Ha ocurrido un error modificando el plugin." );
      }
   }
}
