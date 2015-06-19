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
   public $autorizados;
   public $filtro_query;
   public $filtro_perfil;
   public $filtro_codpais;
   public $filtro_orden;
   public $page_title;
   public $page_description;
   public $page_keywords;
   public $perfil;
   public $resultados;
   public $offset;
   public $rid;
   public $visitante;
   public $visitante_s;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Colabora', 'comunidad', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $visitante = new comm3_visitante();
      $this->perfil = comm3_get_perfil_user($this->user);
      
      $this->offset = 0;
      if( isset($_GET['offset']) )
      {
         $this->offset = intval($_GET['offset']);
      }
      
      if( isset($_GET['nuevo_email']) )
      {
         if( filter_var($_GET['nuevo_email'], FILTER_VALIDATE_EMAIL) )
         {
            $visitante->email = $_GET['nuevo_email'];
            $visitante->rid = '-1';
            $visitante->autorizado = $this->user->nick;
            $visitante->perfil = 'cliente';
            
            if( $visitante->exists() )
            {
               $this->new_error_msg('El email ya está asignado.');
            }
            else if( $visitante->save() )
            {
               header( 'Location: '.$visitante->url() );
            }
            else
               $this->new_error_msg('Error al guardar los datos.');
         }
         else
            $this->new_error_msg('Email no válido.');
         
         $this->resultados = $visitante->search_for_user($this->user->admin, $this->user->nick);
      }
      else if( isset($_REQUEST['email']) OR isset($_REQUEST['nick']) )
      {
         $this->template = 'community_colabora2';
         
         if( isset($_REQUEST['email']) )
         {
            $this->visitante_s = $visitante->get($_REQUEST['email']);
         }
         else
         {
            $this->visitante_s = $visitante->get_by_nick($_REQUEST['nick']);
         }
         
         $this->autorizados = array();
         
         if( isset($_POST['perfil']) )
         {
            if($this->user->admin OR $this->visitante_s->autorizado($this->user->nick) )
            {
               $this->visitante_s->perfil = $_POST['perfil'];
               $this->visitante_s->privado = isset($_POST['privado']);
               
               $this->visitante_s->nick = NULL;
               if($_POST['nick'] != '')
               {
                  $this->visitante_s->nick = $_POST['nick'];
               }
               
               $this->visitante_s->autorizado = NULL;
               if($_POST['autorizado'] != '')
               {
                  $this->visitante_s->autorizado = $_POST['autorizado'];
               }
               
               $this->visitante_s->autorizado2 = NULL;
               if($_POST['autorizado2'] != '')
               {
                  $this->visitante_s->autorizado2 = $_POST['autorizado2'];
               }
               
               $this->visitante_s->autorizado3 = NULL;
               if($_POST['autorizado3'] != '')
               {
                  $this->visitante_s->autorizado3 = $_POST['autorizado3'];
               }
               
               $this->visitante_s->autorizado4 = NULL;
               if($_POST['autorizado4'] != '')
               {
                  $this->visitante_s->autorizado4 = $_POST['autorizado4'];
               }
               
               $this->visitante_s->autorizado5 = NULL;
               if($_POST['autorizado5'] != '')
               {
                  $this->visitante_s->autorizado5 = $_POST['autorizado5'];
               }
               
               if( $this->visitante_s->save() )
               {
                  $this->new_message('Datos guardados correctamente.');
               }
               else
                  $this->new_error_msg('Error al guardar los datos.');
            }
            else
               $this->new_error_msg('No estás autorizado.');
         }
         
         if(!$this->visitante_s)
         {
            $item = new comm3_item();
            if( isset($_REQUEST['email']) )
            {
               $this->resultados = $item->all_by_email($_REQUEST['email'], $this->offset);
            }
            else
            {
               $this->resultados = $item->all_by_nick($_REQUEST['nick'], $this->offset);
            }
         }
         else if( $this->user->admin OR $this->visitante_s->autorizado($this->user->nick) )
         {
            $item = new comm3_item();
            $this->resultados = $item->all_by_visitante($this->visitante_s, $this->offset);
            $this->autorizados = $this->visitante_s->search_for_user(FALSE, $this->visitante_s->nick);
         }
         else
         {
            $this->new_error_msg('No tienes permiso para ver estos datos.');
            $this->template = 'community_colabora';
            $this->resultados = $visitante->search_for_user($this->user->admin, $this->user->nick);
         }
      }
      else if( isset($_GET['delete']) )
      {
         $vis = $visitante->get($_GET['delete']);
         if($vis)
         {
            if(!$this->user->admin AND $vis->autorizado != $this->user->nick)
            {
               $this->new_error_msg('No tienes permiso para eliminar estos datos.');
            }
            else if( $vis->delete() )
            {
               $this->new_message('Visitante eliminado correctamente.');
            }
            else
               $this->new_error_msg('Error al eliminar el visitante.');
         }
         else
            $this->new_error_msg('Visitante no encontrado.');
         
         $this->resultados = $visitante->search_for_user($this->user->admin, $this->user->nick);
      }
      else if( isset($_POST['filtro_query']) )
      {
         $this->filtro_query = $_POST['filtro_query'];
         $this->filtro_perfil = $_POST['filtro_perfil'];
         $this->filtro_codpais = $_POST['filtro_codpais'];
         $this->filtro_orden = $_POST['filtro_orden'];
         
         $this->resultados = $visitante->search_for_user(
                 $this->user->admin,
                 $this->user->nick,
                 $this->filtro_query,
                 $this->filtro_perfil,
                 $this->filtro_codpais,
                 $this->filtro_orden
         );
      }
      else
      {
         $this->filtro_query = '';
         $this->filtro_perfil = '---';
         $this->filtro_codpais = '---';
         $this->filtro_orden = 'first_login DESC';
         
         $this->resultados = $visitante->search_for_user($this->user->admin, $this->user->nick);
      }
   }
   
   protected function public_core()
   {
      $this->page_title = 'Colabora &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Colabora en el desarrollo de FacturaScripts, forma parte de la comunidad.';
      $this->page_keywords = 'facturascripts, eneboo, abanq, woocommerce, prestashop, facturae';
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
            if( !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) )
            {
               $this->new_error_msg('Email no válido.');
            }
            else if($this->visitante)
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
   
   public function perfiles()
   {
      return array(
          'voluntario' => 'Voluntario',
          'programador' => 'Programador',
          'distribuidor' => 'Distribuidor',
          'sysadmin' => 'Sysadmin',
          'contable' => 'Contable',
          '---' => '---',
          'premium' => 'Premium',
          'partner' => 'Partner',
          'cliente' => 'Cliente de partner',
      );
   }
   
   public function paises()
   {
      $paises = array();
      
      $data = $this->db->select("SELECT DISTINCT codpais FROM comm3_visitantes ORDER BY codpais ASC;");
      if($data)
      {
         foreach($data as $d)
         {
            if($d['codpais'] != '')
            {
               $paises[] = $d['codpais'];
            }
         }
      }
      
      return $paises;
   }
   
   public function url()
   {
      $email = '';
      if( isset($_GET['email']) )
      {
         $email = $_GET['email'];
      }
      
      $nick = '';
      if( isset($_GET['nick']) )
      {
         $nick = $_GET['nick'];
      }
      
      if($email != '')
      {
         return 'index.php?page='.__CLASS__.'&email='.$email;
      }
      else if($nick != '')
      {
         return 'index.php?page='.__CLASS__.'&nick='.$nick;
      }
      else
         return parent::url();
   }
}
