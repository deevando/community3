<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2015         Francesc Pineda Segarra  shawe.ewahs@gmail.com
 * Copyright (C) 2015-2016    Carlos García Gómez      neorazorx@gmail.com
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

class community_plugins extends community_home
{
   public $lista_plugins;
   public $mis_plugins;
   public $order;
   
   private $plugin;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Plugins', 'comunidad', FALSE, TRUE);
   }
   
   /**
    * Parte privada de la página.
    */
   protected function private_core()
   {
      parent::private_core();
      
      $this->plugin = new comm3_plugin();
      
      if( isset($_GET['json']) )
      {
         /// devolvemos la lista de plugins, quitando los ocultos
         
         $this->template = FALSE;
         header('Access-Control-Allow-Origin: *');
         header('Access-Control-Allow-Methods: GET, POST');
         header('Content-Type: application/json');
         
         /// quitamos la parte privada
         $json = array();
         foreach( $this->plugin->all() as $pl )
         {
            if(!$pl->oculto)
            {
               unset($pl->private_update_name);
               unset($pl->private_update_key);
               unset($pl->oculto);
               $json[] = $pl;
            }
         }
         echo json_encode($json);
      }
      else if( isset($_GET['json2']) )
      {
         /// devolvemos la lista de plugins, incluidos los ocultos
         
         $this->template = FALSE;
         header('Access-Control-Allow-Origin: *');
         header('Access-Control-Allow-Methods: GET, POST');
         header('Content-Type: application/json');
         
         /// quitamos la parte privada
         $json = array();
         foreach( $this->plugin->all() as $pl )
         {
            unset($pl->private_update_name);
            unset($pl->private_update_key);
            $json[] = $pl;
         }
         echo json_encode($json);
      }
      else if( isset( $_POST[ 'nombre' ] ) )
      {
         /* Insertamos elemento nuevo */
         $this->plugin->nick                 = $this->user->nick;
         $this->plugin->nombre               = $_POST['nombre'];
         $this->plugin->descripcion          = $_POST['descripcion'];
         $this->plugin->descripcion_html     = $_POST['descripcion_html'];
         $this->plugin->link                 = $_POST['link'];
         $this->plugin->zip_link             = $_POST['zip_link'];
         $this->plugin->imagen               = $_POST['imagen'];
         $this->plugin->estable              = isset($_POST['estable']);
         $this->plugin->oculto               = isset($_POST['oculto']);
         $this->plugin->version              = intval($_POST['version']);
         $this->plugin->ultima_modificacion  = $_POST['ultima_modificacion'];
         $this->plugin->descargas            = 0;
         
         if( strlen( trim($this->plugin->nombre) ) < 2 )
         {
            $this->new_error_msg( "Debes usar un nombre de más de 2 caracteres." );
         }
         else if( $this->plugin->save() )
         {
            if(!$this->plugin->oculto)
            {
               $item = new comm3_item();
               $item->tipo = 'changelog';
               $item->nick = $this->user->nick;
               $item->ip = $this->user->last_ip;
               $item->texto = 'Nuevo plugin disponible: [b]'.$_POST[ 'nombre' ]."[/b]\n".
                       $_POST[ 'descripcion' ]."\n[url=".$_POST[ 'link' ]."]web[/url]".
                       "\n\nPuedes verlo ya en la sección descargas de tu panel de control.";
               $item->tags = '['.$_POST['nombre'].'_'.$_POST['version'].'],['.$_REQUEST['nombre'].']';
               $item->save();
            }
            
            $this->new_message( "Se ha insertado el plugin correctamente." );
            header('Location: '.$this->plugin->url());
         }
         else
         {
            $this->new_error_msg( "Ha ocurrido un error guardando el plugin." );
         }
      }
      else if( isset( $_GET[ 'delete' ] ) )
      {
         /* Eliminamos un elemento existente */
         $delete_plugin = $this->plugin->get( $_GET[ 'delete' ] );

         if( $delete_plugin )
         {
            if( $delete_plugin->delete() )
            {
               $this->new_message( 'Se ha eliminado el plugin correctamente.' );
            }
            else
            {
               $this->new_error_msg( 'Ha ocurrido un error eliminando el plugin.' );
            }
         }
         else
         {
            $this->new_error_msg( 'Plugin no encontrado.' );
         }
      }
      
      $visit0 = new comm3_visitante();
      $this->mis_plugins = $this->plugin->all_by_dev($this->user->nick);
      
      $this->lista_plugins = array();
      foreach($this->plugin->all() as $pl)
      {
         
         if($this->user->admin)
         {
            $this->lista_plugins[] = $pl;
         }
         else if($pl->oculto)
         {
            /// necesitamos saber si el usuario está autorizado
            $visitante = $visit0->get_by_nick($pl->nick);
            if($visitante)
            {
               if( $visitante->autorizado($this->user->nick) )
               {
                  $this->lista_plugins[] = $pl;
               }
            }
         }
         else
         {
            $this->lista_plugins[] = $pl;
         }
      }
   }
   
   protected function public_core()
   {
      $plugin = new comm3_plugin();
      
      if( isset($_GET['json']) )
      {
         /// devolvemos la lista de plugins, quitando los ocultos
         $this->template = FALSE;
         header('Access-Control-Allow-Origin: *');
         header('Access-Control-Allow-Methods: GET, POST');
         header('Content-Type: application/json');
         
         /// quitamos la parte privada
         $json = array();
         foreach( $plugin->all() as $pl )
         {
            if(!$pl->oculto)
            {
               unset($pl->private_update_name);
               unset($pl->private_update_key);
               unset($pl->oculto);
               $json[] = $pl;
            }
         }
         echo json_encode($json);
      }
      else if( isset($_GET['json2']) )
      {
         /// devolvemos la lista de plugins, ocultos incluidos
         $this->template = FALSE;
         header('Access-Control-Allow-Origin: *');
         header('Access-Control-Allow-Methods: GET, POST');
         header('Content-Type: application/json');
         
         /// quitamos la parte privada
         $json = array();
         foreach( $plugin->all() as $pl )
         {
            unset($pl->private_update_name);
            unset($pl->private_update_key);
            $json[] = $pl;
         }
         echo json_encode($json);
      }
      else
      {
         parent::public_core();
         
         $this->order = 'ultima_modificacion DESC';
         if( isset($_GET['order']) )
         {
            switch($_GET['order'])
            {
               case '1':
                  $this->order = 'lower(nombre) ASC';
                  break;
               
               case '2':
                  $this->order = 'descargas DESC';
                  break;
               
               default:
                  $this->order = 'ultima_modificacion DESC';
                  break;
            }
         }
         
         $this->page_title = 'Catálogo de plugins de FacturaScripts';
         $this->page_description = 'Todos los plugins disponibles actualmente para FacturaScripts.';
         $this->page_keywords = 'plugins facturascripts, plugins eneboo';
         $this->template = 'public/plugins';
         
         $this->lista_plugins = array();
         foreach( $plugin->all($this->order) as $pl )
         {
            if(!$pl->oculto)
            {
               unset($pl->private_update_name);
               unset($pl->private_update_key);
               unset($pl->oculto);
               $this->lista_plugins[] = $pl;
            }
         }
      }
   }
}
