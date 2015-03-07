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

require_model('comm3_item.php');
require_model('comm3_visitante.php');

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_colabora extends fs_controller
{
   public $mostrar_visitantes;
   public $page_title;
   public $page_description;
   public $resultados;
   public $visitante;
   public $visitante_s;
   
   private $rid;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Visitantes', 'comunidad', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      $this->mostrar_visitantes = TRUE;
      $visitante = new comm3_visitante();
      
      if( isset($_GET['email']) )
      {
         $this->visitante_s = $visitante->get($_GET['email']);
         
         $item = new comm3_item();
         $this->resultados = $item->all_by_email($_GET['email']);
         
         $this->mostrar_visitantes = FALSE;
      }
      else
         $this->resultados = $visitante->all();
   }
   
   protected function public_core()
   {
      $this->page_title = 'Colabora &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Colabora en el desarrollo de FacturaScripts, forma parte de la comunidad.';
      $this->template = 'public/colabora';
      $visit0 = new comm3_visitante();
      $this->visitante = FALSE;
      
      $this->rid = $this->random_string(30);
      if( isset($_COOKIE['rid']) )
      {
         $this->rid = $_COOKIE['rid'];
         $this->visitante = $visit0->get_by_rid($this->rid);
      }
      else
      {
         setcookie('rid', $this->rid, time()+FS_COOKIES_EXPIRE, '/');
      }
      
      if( isset($_POST['humanity']) )
      {
         if($_POST['humanity'] == '')
         {
            if($this->visitante)
            {
               $this->visitante->email = $_POST['email'];
               $this->visitante->perfil = $_POST['perfil'];
               if( $this->visitante->save() )
               {
                  $this->new_message('Datos guardados correctamente.');
               }
               else
                  $this->new_error_msg('Error al guardar los datos.');
            }
            else
            {
               $this->visitante = new comm3_visitante();
               $this->visitante->rid = $this->rid;
               $this->visitante->email = $_POST['email'];
               $this->visitante->perfil = $_POST['perfil'];
               
               if( isset($_SERVER['REMOTE_ADDR']) )
               {
                  $this->visitante->last_ip = $_SERVER['REMOTE_ADDR'];
               }
               
               if( isset($_SERVER['HTTP_USER_AGENT']) )
               {
                  $this->visitante->last_browser = $_SERVER['HTTP_USER_AGENT'];
               }
               
               if( $this->visitante->save() )
               {
                  $this->new_message('Datos guardados correctamente.');
               }
               else
                  $this->new_error_msg('Error al guardar los datos.');
            }
         }
         else
         {
            $this->new_error_msg('Tienes que borrar el número para demostrar que eres humano.');
         }
      }
      
      $item = new comm3_item();
      $this->resultados = $item->all_by_rid($this->rid);
   }
   
   public function path()
   {
      if( defined('COMM3_PATH') )
      {
         return COMM3_PATH;
      }
      else
         return '';
   }
}
