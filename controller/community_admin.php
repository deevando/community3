<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2015-2016  Carlos Garcia Gomez  neorazorx@gmail.com
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

require_model('comm3_partner.php');
require_model('comm3_plugin.php');
require_model('comm3_plugin_key.php');
require_model('comm3_visitante.php');

/**
 * Description of community_admin
 *
 * @author carlos
 */
class community_admin extends fs_controller
{
   public $anuncio;
   public $partners;
   public $recaptcha;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Comunidad', 'admin');
   }
   
   protected function private_core()
   {
      $this->check_menu();
      $this->check_users();
      $this->anuncio = '';
      $partner0 = new comm3_partner();
      
      if($this->user->admin)
      {
         $fsvar = new fs_var();
         
         if( isset($_POST['anuncio']) )
         {
            if( $fsvar->simple_save('comm3_anuncio', $_POST['anuncio']) )
            {
               $this->new_message('Datos guardados correctamente.');
            }
            else
               $this->new_error_msg('Error al guardar los datos');
         }
         else if( isset($_POST['csv']) )
         {
            if( is_uploaded_file($_FILES['fcsv']['tmp_name']) )
            {
               $this->importar_pedidos_tienda();
            }
            else
               $this->new_error_msg('No has seleccionado ningún archivo.');
         }
         else if( isset($_POST['recaptcha']) )
         {
            if( $fsvar->simple_save('recaptcha', $_POST['recaptcha']) )
            {
               $this->new_message('Datos guardados correctamente.');
            }
            else
               $this->new_error_msg('Error al guardar los datos');
         }
         else if( isset($_POST['nombre']) )
         {
            $partner = $partner0->get($_POST['nombre']);
            if(!$partner)
            {
               $partner = new comm3_partner();
               $partner->nombre = $_POST['nombre'];
            }
            
            $partner->nombrecomercial = $_POST['nombrecomercial'];
            $partner->descripcion = $_POST['descripcion'];
            $partner->link = $_POST['link'];
            $partner->administrador = $_POST['administrador'];
            
            if( $partner->save() )
            {
               $this->new_message("Datos guardados correctamente.");
            }
            else
            {
               $this->new_error_msg("Error al guardar los datos.");
            }
         }
         else if( isset($_GET['deletep']) )
         {
            $partner = $partner0->get($_GET['deletep']);
            if($partner)
            {
               if( $partner->delete() )
               {
                  $this->new_message('Partner eliminado correctamente.');
               }
            }
         }
         
         $this->anuncio = $fsvar->simple_get('comm3_anuncio');
         $this->partners = $partner0->all();
         $this->recaptcha = $fsvar->simple_get('recaptcha');
      }
      else
      {
         $this->new_error_msg('Solos los administradores pueden acceder a esta página.');
      }
   }
   
   protected function public_core()
   {
      header('Location: index.php?page=community_home');
   }
   
   private function check_menu()
   {
      if( !$this->page->get('community_colabora') )
      {
         if( file_exists(__DIR__) )
         {
            $excluir = array(__CLASS__.'.php', 'community_sitemap.php', 'community_rss.php');
            
            /// activamos las páginas del plugin
            foreach( scandir(__DIR__) as $f)
            {
               if( is_string($f) AND strlen($f) > 0 AND !is_dir($f) AND !in_array($f, $excluir) )
               {
                  $page_name = substr($f, 0, -4);
                  
                  require_once __DIR__.'/'.$f;
                  $new_fsc = new $page_name();
                  
                  if( !$new_fsc->page->save() )
                  {
                     $this->new_error_msg("Imposible guardar la página ".$page_name);
                  }
                  
                  unset($new_fsc);
               }
            }
         }
         else
         {
            $this->new_error_msg('No se encuentra el directorio '.__DIR__);
         }
         
         $this->load_menu(TRUE);
      }
   }
   
   /**
    * Comprobamos que todos los usuarios tengan un email asocuado
    */
   private function check_users()
   {
      $visit0 = new comm3_visitante();
      
      foreach($this->user->all() as $user)
      {
         if(!$user->email)
         {
            /// buscamos al usuario en los visitantes
            $visitante = $visit0->get_by_nick($user->nick);
            if($visitante)
            {
               $user->email = $visitante->email;
               $user->save();
            }
            else
            {
               $this->new_error_msg('El usuario '.$user->nick.' no tiene un email asociado.'
                       . ' Tendrá problemas para usar la comunidad.');
            }
         }
      }
   }
   
   private function importar_pedidos_tienda()
   {
      $nuevas = 0;
      
      $fcsv = fopen($_FILES['fcsv']['tmp_name'], 'r');
      if($fcsv)
      {
         $plug0 = new comm3_plugin();
         $plugins = $plug0->all();
         $plk0 = new comm3_plugin_key();
         $visit0 = new comm3_visitante();
         
         $i = 0;
         while( !feof($fcsv) )
         {
            $aux = trim( fgets($fcsv) );
            if($aux != '' AND $i > 0)
            {
               $linea = explode(';', $aux);
               
               /// ¿Existe el visitante?
               if($linea[0] != '')
               {
                  $visitante = $visit0->get($linea[0]);
                  if(!$visitante)
                  {
                     $visitante = new comm3_visitante();
                     $visitante->email = $linea[0];
                     $visitante->rid = $this->random_string(30);
                     $visitante->save();
                  }
                  
                  foreach($plugins as $plug)
                  {
                     if($linea[1] == '')
                     {
                        break;
                     }
                     else if($plug->referencia == $linea[1] OR $plug->nombre == $linea[1])
                     {
                        $encontrado = FALSE;
                        foreach($plk0->all_from_email($linea[0]) as $plk)
                        {
                           if($plk->idplugin == $plug->id AND $plk->fecha == $linea[3] AND $plk->hora == $linea[4])
                           {
                              $encontrado = TRUE;
                              break;
                           }
                        }
                        
                        if(!$encontrado)
                        {
                           $plk = new comm3_plugin_key();
                           $plk->email = $linea[0];
                           $plk->idplugin = $plug->id;
                           $plk->plugin = $plug->nombre;
                           $plk->fecha = $linea[3];
                           $plk->hora = $linea[4];
                           if( $plk->save() )
                           {
                              $nuevas++;
                           }
                        }
                     }
                  }
               }
            }
            
            $i++;
         }
      }
      
      $this->new_message($nuevas.' claves añadidas');
   }
}
