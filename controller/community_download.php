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

require_model('comm3_stat.php');

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_download extends fs_controller
{
   public $last_version;
   public $page_title;
   public $page_description;
   public $total_descargas;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Descargas', 'comunidad', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      
   }
   
   protected function public_core()
   {
      $this->page_title = 'Descargas &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Página de descargas de FacturaScripts.';
      $this->template = 'public/download';
      
      $this->last_version = $this->cache->get('comm3_last_version');
      if(!$this->last_version)
      {
         $this->last_version = file_get_contents('https://raw.githubusercontent.com/NeoRazorX/facturascripts_2015/master/VERSION');
         $this->cache->set('comm3_last_version', $this->last_version, 86400);
      }
      
      $stat = new comm3_stat();
      $last_stat = $stat->get($stat->fecha, $this->last_version);
      if(!$last_stat)
      {
         $last_stat = new comm3_stat();
         $last_stat->version = $this->last_version;
      }
      $last_stat->descargas++;
      $last_stat->save();
      
      $this->total_descargas = 0;
      $data = $this->db->select("SELECT SUM(descargas) as descargas FROM comm3_stats;");
      if($data)
      {
         $this->total_descargas = intval($data[0]['descargas']);
      }
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
