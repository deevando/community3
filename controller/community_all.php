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

require_model('comm3_comment.php');
require_model('comm3_item.php');
require_model('comm3_visitor.php');

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_all extends fs_controller
{
   public $mostrar;
   public $num_pendientes;
   public $page_title;
   public $page_description;
   public $perfil;
   public $resultados;
   public $rid;
   public $visitante;
   
   private $offset;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Todo', 'comunidad', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      $item = new comm3_item();
      
      $this->mostrar = 'parati';
      if( isset($_GET['mostrar']) )
      {
         $this->mostrar = $_GET['mostrar'];
      }
      else if($this->user->admin)
      {
         $this->mostrar = 'pendiente';
      }
      
      $this->offset = 0;
      if( isset($_GET['offset']) )
      {
         $this->offset = intval($_GET['offset']);
      }
      
      $this->perfil = comm3_get_perfil_user($this->user);
      
      if( isset($_GET['delete']) )
      {
         $item2 = $item->get($_GET['delete']);
         if($item2)
         {
            if( $item2->delete() )
            {
               $this->new_message('Página eliminada correctamente.');
            }
            else
            {
               $this->new_error_msg('Error al eliminar la página.');
            }
         }
         else
         {
            $this->new_error_msg('Página no encontrada.');
         }
      }
      
      $this->num_pendientes = $item->num_pendientes($this->user->nick, $this->user->admin);
      
      if($this->mostrar == 'parati')
      {
         $this->resultados = $item->all_for_nick($this->user->nick, $this->offset);
      }
      else if($this->mostrar == 'pendiente')
      {
         $this->resultados = $item->pendientes($this->offset, $this->user->nick, $this->user->admin);
      }
      else if($this->mostrar == 'mix')
      {
         $this->resultados = array();
         $emails = array();
         foreach($item->pendientes($this->offset, $this->user->nick, $this->user->admin) as $res)
         {
            if( !in_array($res->email(), $emails) )
            {
               $this->resultados[] = $res;
               $emails[] = $res->email();
            }
         }
      }
      else
         $this->resultados = $item->all($this->offset);
   }
   
   protected function public_core()
   {
      $this->page_title = 'Todo &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Todas las preguntas, ideas e informes de errores de FacturaScripts';
      $this->template = 'public/all';
      
      $this->offset = 0;
      if( isset($_GET['offset']) )
      {
         $this->offset = intval($_GET['offset']);
      }
      
      $this->rid = FALSE;
      if( isset($_COOKIE['rid']) )
      {
         $this->rid = $_COOKIE['rid'];
         $visitante = new comm3_visitante();
         $this->visitante = $visitante->get_by_rid($this->rid);
      }
      
      $this->mostrar = 'todo';
      if( isset($_GET['mostrar']) )
      {
         $this->mostrar = $_GET['mostrar'];
      }
      
      /// mostramos los resultados
      $this->resultados = array();
      $sql = "SELECT * FROM comm3_items WHERE ";
      if($this->mostrar == 'mio')
      {
         $sql .= "rid = ".$this->empresa->var2str($this->rid)." ORDER BY actualizado DESC";
      }
      else if($this->mostrar == 'codpais')
      {
         $sql .= "privado = false AND codpais = ".$this->empresa->var2str($this->visitante->codpais)." ORDER BY actualizado DESC";
      }
      else
      {
         $sql .= "privado = false OR rid = ".$this->empresa->var2str($this->rid)." ORDER BY actualizado DESC";
      }
      $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, $this->offset);
      if($data)
      {
         foreach($data as $d)
         {
            $this->resultados[] = new comm3_item($d);
         }
      }
   }
   
   public function anterior_url()
   {
      $url = '';
      
      if($this->offset > 0)
      {
         $url = $this->url()."&offset=".($this->offset-FS_ITEM_LIMIT).'&mostrar='.$this->mostrar;
      }
      
      return $url;
   }
   
   public function siguiente_url()
   {
      $url = '';
      
      if( count($this->resultados) == FS_ITEM_LIMIT )
      {
         $url = $this->url()."&offset=".($this->offset+FS_ITEM_LIMIT).'&mostrar='.$this->mostrar;
      }
      
      return $url;
   }
}
