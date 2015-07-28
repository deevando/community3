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

require_model('comm3_plugin.php');

/**
 * Description of community_edit_plugin
 *
 * @author carlos
 */
class community_edit_plugin extends fs_controller
{
   public $plugin;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Editar plugin', 'community', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->plugin = FALSE;
      if( isset($_REQUEST['id']) )
      {
         $commplug = new comm3_plugin();
         $this->plugin = $commplug->get($_REQUEST['id']);
      }
      
      if($this->plugin)
      {
         if( isset($_GET['key']) )
         {
            $this->template = FALSE;
            
            $error = TRUE;
            if($_GET['key'] == $this->plugin->private_update_key)
            {
               if($this->plugin->private_update_name)
               {
                  if( file_exists('tmp/'.FS_TMP_NAME.'private_plugins/'.$this->plugin->private_update_name) )
                  {
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
         else if( $this->plugin->oculto AND !$this->user->admin AND $this->user->nick != $this->plugin->nick )
         {
            $this->new_error_msg('No tienes permiso para ver este plugin.');
            $this->plugin = FALSE;
         }
         else if( !$this->user->admin AND $this->user->nick != $this->plugin->nick )
         {
            $this->new_advice('No tienes permiso para editar este plugin.');
         }
         else if( isset($_POST['id']) )
         {
            if( isset($_POST['new_update']) )
            {
               if( is_uploaded_file($_FILES['private_update']['tmp_name']) )
               {
                  if( !file_exists('tmp/'.FS_TMP_NAME.'private_plugins') )
                  {
                     mkdir('tmp/'.FS_TMP_NAME.'private_plugins');
                  }
                  else if( is_null($this->plugin->private_update_name) )
                  {
                     
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
                     
                     $this->plugin->version              = intval($_POST['version']);
                     $this->plugin->estable              = isset($_POST['estable']);
                     $this->plugin->ultima_modificacion  = $this->today();
                     
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
               $this->plugin->descripcion            = $_POST['descripcion'];
               $this->plugin->link                   = $_POST['link'];
               $this->plugin->zip_link               = $_POST['zip_link'];
               $this->plugin->estable                = isset($_POST['estable']);
               $this->plugin->oculto                 = isset($_POST['oculto']);
               $this->plugin->version                = intval($_POST['version']);
               $this->plugin->ultima_modificacion    = $_POST['ultima_modificacion'];
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
         else if( isset($_GET['rekey']) )
         {
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
         $error = TRUE;
         if($_GET['key'] == $this->plugin->private_update_key)
         {
            if($this->plugin->private_update_name)
            {
               if( file_exists('tmp/'.FS_TMP_NAME.'private_plugins/'.$this->plugin->private_update_name) )
               {
                  $error = FALSE;
                  echo file_get_contents('tmp/'.FS_TMP_NAME.'private_plugins/'.$this->plugin->private_update_name);
               }
            }
         }
         
         if($error)
         {
            header('HTTP/1.0 403 Forbidden');
            header('Location: index.php?page=community_home');
            die('ERROR');
         }
      }
   }
}
