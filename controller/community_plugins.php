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

require_model('comm3_item.php');
require_model('comm3_plugin.php');

class community_plugins extends fs_controller
{
   public $plugin;
   public $lista_plugins;
   public $mis_plugins;
   public $editar_plugin;
   public $ver_plugin;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Plugins', 'comunidad', FALSE, TRUE);
   }
   
   /**
    * Parte privada de la página.
    */
   protected function private_core()
   {
      $this->editar_plugin = FALSE;
      $this->ver_plugin = FALSE;
      
      $this->plugin = new comm3_plugin();
      
      if( isset($_GET['json']) )
      {
         $this->template = FALSE;
         
         header('Access-Control-Allow-Origin: *');
         header('Access-Control-Allow-Methods: GET, POST');
         header('Content-Type: application/json');
         echo json_encode( $this->plugin->all() );
      }
      else if( isset($_POST['id']) )
      {
         /* Modificar un elemento existente */
         $this->editar_plugin = $this->plugin->get($_POST['id']);
         
         if ( $this->editar_plugin )
         {
            $this->editar_plugin->descripcion            = $_POST[ 'descripcion' ];
            $this->editar_plugin->link                   = $_POST[ 'link' ];
            $this->editar_plugin->zip_link               = $_POST[ 'zip_link' ];
            $this->editar_plugin->estable                = ($_POST[ 'estable' ] == 'TRUE');
            $this->editar_plugin->version                = intval($_POST[ 'version' ]);
            $this->editar_plugin->ultima_modificacion    = $_POST[ 'ultima_modificacion' ];

            if ( $this->editar_plugin->save() )
            {
               $this->new_message( "Se han modificado los datos del plugin." );

               $this->editar_plugin = $this->plugin->get( $_POST[ 'id' ] );
            }
            else
            {
               $this->new_error_msg( "Ha ocurrido un error modificando el plugin." );
            }
         }
         else
         {
            $this->new_error_msg( "Ha ocurrido un error accidiendo el plugin." );
         }
      }
      else if ( isset( $_POST[ 'nombre' ] ) )
      {
         /* Insertamos elemento nuevo */
         $this->plugin->nick                 = $this->user->nick;
         $this->plugin->nombre               = $_POST[ 'nombre' ];
         $this->plugin->descripcion          = $_POST[ 'descripcion' ];
         $this->plugin->link                 = $_POST[ 'link' ];
         $this->plugin->zip_link             = $_POST[ 'zip_link' ];
         $this->plugin->estable              = ($_POST[ 'estable' ] == 'TRUE');
         $this->plugin->version              = intval($_POST[ 'version' ]);
         $this->plugin->ultima_modificacion  = $_POST[ 'ultima_modificacion' ];
         $this->plugin->descargas            = 0;
         
         if( strlen( trim($this->plugin->nombre) ) < 2 )
         {
            $this->new_error_msg( "Debes usar un nombre de más de 2 caracteres." );
         }
         else if( $this->plugin->save() )
         {
            $this->new_message( "Se ha insertado el plugin correctamente." );
            
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
         else
         {
            $this->new_error_msg( "Ha ocurrido un error guardando el plugin." );
         }
      }
      else if ( isset( $_GET[ 'id' ] ) )
      {
         /* Obtenemos elemento recibido */
         if($this->user->nick == $this->plugin->get( $_GET[ 'id' ] )->nick OR $this->user->admin)
         {
            $this->editar_plugin = $this->plugin->get( $_GET[ 'id' ] );
         }
         else
         {
            $this->ver_plugin = $this->plugin->get( $_GET[ 'id' ] );
         }
      }
      else if ( isset( $_GET[ 'delete' ] ) )
      {
         /* Eliminamos un elemento existente */
         $delete_plugin = $this->plugin->get( $_GET[ 'delete' ] );

         if ( $delete_plugin )
         {
            if ( $delete_plugin->delete() )
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

      /* Mostraremos siempre la lista */
      $this->lista_plugins = $this->plugin->all();
      $this->mis_plugins = $this->plugin->all_by_dev($this->user->nick);
   }
   
   protected function public_core()
   {
      $this->template = FALSE;
      
      if( isset($_GET['json']) )
      {
         $plugin = new comm3_plugin();
         
         $this->template = FALSE;
         header('Access-Control-Allow-Origin: *');
         header('Access-Control-Allow-Methods: GET, POST');
         header('Content-Type: application/json');
         echo json_encode( $plugin->all() );
      }
   }
}
