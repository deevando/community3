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

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_questions extends fs_controller
{
   public $mostrar;
   public $page_title;
   public $page_description;
   public $page_keywords;
   public $resultados;
   public $rid;
   public $visitante;
   
   private $offset;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Preguntas', 'comunidad', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      $this->offset = 0;
      if( isset($_GET['offset']) )
      {
         $this->offset = intval($_GET['offset']);
      }
      
      $this->mostrar = 'pendientes';
      if( isset($_GET['mostrar']) )
      {
         $this->mostrar = $_GET['mostrar'];
      }
      
      $item = new comm3_item();
      if($this->mostrar == 'pendientes')
      {
         $this->resultados = $item->pendientes($this->offset, $this->user->nick, $this->user->admin, 'question');
      }
      else if($this->mostrar == 'tuyo')
      {
         $this->resultados = $item->all_by_nick($this->user->nick, $this->offset, 'question');
      }
      else
         $this->resultados = $item->all_by_tipo('question', $this->offset);
   }
   
   protected function public_core()
   {
      $this->page_title = 'Preguntas &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Preguntas a la comunidad FacturaScripts.';
      $this->page_keywords = 'preguntas sobre facturaScripts, dudas con FacturaScripts, dudas con eneboo';
      $this->template = 'public/questions';
      
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
      
      $this->resultados = array();
      if($this->mostrar == 'todo')
      {
         $sql = "SELECT * FROM comm3_items WHERE (privado = false OR rid = ".$this->empresa->var2str($this->rid).") AND tipo = 'question'";
         $data = $this->db->select_limit($sql." ORDER BY actualizado DESC", FS_ITEM_LIMIT, $this->offset);
         if($data)
         {
            foreach($data as $d)
            {
               $this->resultados[] = new comm3_item($d);
            }
         }
      }
      else if($this->mostrar == 'codpais' AND $this->visitante)
      {
         $sql = "SELECT * FROM comm3_items WHERE (privado = false OR rid = ".$this->empresa->var2str($this->rid).") AND tipo = 'question'";
         $sql .= " AND codpais = ".$this->empresa->var2str($this->visitante->codpais)." ORDER BY actualizado DESC";
         $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, $this->offset);
         if($data)
         {
            foreach($data as $d)
            {
               $this->resultados[] = new comm3_item($d);
            }
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
   
   public function num_pendientes($only_public = FALSE)
   {
      if($only_public)
      {
         $total = 0;
         $sql = "SELECT COUNT(*) as total FROM comm3_items WHERE tipo = 'question' AND (estado != 'cerrado' OR estado is NULL) AND privado = false";
         $data = $this->db->select($sql);
         if($data)
         {
            $total = intval($data[0]['total']);
         }
         return $total;
      }
      else
      {
         $item = new comm3_item();
         return $item->num_pendientes($this->user->nick, $this->user->admin, 'question');
      }
   }
}
