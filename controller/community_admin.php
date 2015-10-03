<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2015  Carlos Garcia Gomez  neorazorx@gmail.com
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

/**
 * Description of community_admin
 *
 * @author carlos
 */
class community_admin extends fs_controller
{
   public $anuncio;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Admininstración', 'comunidad', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->check_menu();
      $this->anuncio = '';
      
      if($this->user->admin)
      {
         $fsvar = new fs_var();
         
         if( isset($_POST['anuncio']) )
         {
            $this->anuncio = $_POST['anuncio'];
            if( $fsvar->simple_save('comm3_anuncio', $this->anuncio) )
            {
               $this->new_message('Datos guardados correctamente.');
            }
            else
               $this->new_error_msg('Error al guardar los datos');
         }
         else
         {
            $this->anuncio = $fsvar->simple_get('comm3_anuncio');
         }
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
}
