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
class community_search extends fs_controller
{
   public $page_title;
   public $page_description;
   public $resultados;
   public $rid;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Buscar', 'comunidad', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $item = new comm3_item();
      $this->resultados = array();
      
      if( isset($_REQUEST['query']) )
      {
         $this->query = $_REQUEST['query'];
         $this->resultados = $item->search($this->query);
      }
      else if( isset($_REQUEST['tag']) )
      {
         $this->resultados = $item->all_by_tag($_REQUEST['tag']);
      }
   }
   
   protected function public_core()
   {
      $this->page_title = 'Buscar &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Buscador de la comunidadFacturaScripts.';
      $this->template = 'public/search';
      
      $this->rid = FALSE;
      if( isset($_COOKIE['rid']) )
      {
         $this->rid = $_COOKIE['rid'];
      }
      
      $item = new comm3_item();
      $this->resultados = array();
      
      if( isset($_REQUEST['query']) )
      {
         $this->query = $_REQUEST['query'];
         $this->resultados = $item->search($this->query);
      }
      else if( isset($_REQUEST['tag']) )
      {
         $this->resultados = $item->all_by_tag($_REQUEST['tag']);
      }
   }
}
