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

require_once __DIR__.'/community_home.php';
require_model('comm3_stat.php');

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_download extends community_home
{
   public $last_version;
   public $total_descargas;
   public $total_usuarios;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Descargas', 'comunidad', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      parent::private_core();
      
      $this->total_descargas = 0;
      $data = $this->db->select("SELECT SUM(descargas) as descargas FROM comm3_stats;");
      if($data)
      {
         $this->total_descargas = intval($data[0]['descargas']);
      }
   }
   
   protected function public_core()
   {
      parent::public_core();
      
      $this->page_title = 'Descargar FacturaScripts';
      $this->page_description = 'Página de descargas de FacturaScripts.';
      $this->page_keywords = 'facturascripts, eneboo, abanq, woocommerce, prestashop, facturae';
      $this->template = 'public/download';
      
      $this->last_version = $this->cache->get('comm3_last_version');
      if(!$this->last_version)
      {
         $this->last_version = @file_get_contents('https://raw.githubusercontent.com/NeoRazorX/facturascripts_2015/master/VERSION');
         $this->cache->set('comm3_last_version', $this->last_version, 86400);
      }
      
      $last_ip = $this->cache->get('last_download_ip');
      if($last_ip != $_SERVER['REMOTE_ADDR'])
      {
         $stat = new comm3_stat();
         $last_stat = $stat->get($stat->fecha, $this->last_version);
         if(!$last_stat)
         {
            $last_stat = new comm3_stat();
            $last_stat->version = $this->last_version;
         }
         $last_stat->descargas++;
         $last_stat->save();
         
         $this->cache->set('last_download_ip', $_SERVER['REMOTE_ADDR']);
      }
      
      $this->total_descargas = $this->cache->get('total_decargas');
      if(!$this->total_descargas)
      {
         $data = $this->db->select("SELECT SUM(descargas) as descargas FROM comm3_stats;");
         if($data)
         {
            $this->total_descargas = intval($data[0]['descargas']);
            $this->cache->set('total_decargas', $this->total_descargas);
         }
      }
      
      $this->total_usuarios = $this->cache->get('total_usuarios');
      if(!$this->total_usuarios)
      {
         $data = $this->db->select("SELECT COUNT(*) as total FROM comm3_visitantes;");
         if($data)
         {
            $this->total_usuarios = intval($data[0]['total']);
            $this->cache->set('total_usuarios', $this->total_usuarios);
         }
      }
   }
}
