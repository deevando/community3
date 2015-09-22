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
 * Description of community_rss
 *
 * @author carlos
 */
class community_rss extends fs_controller
{
   public function __construct()
   {
      parent::__construct(__CLASS__, 'RSS', 'comunidad', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->rss();
   }
   
   protected function public_core()
   {
      $this->rss();
   }
   
   private function rss()
   {
      $this->template = FALSE;
      
      header("Content-type: text/xml");
      echo '<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
<channel>
  <title>FacturaScripts</title>
  <link>http://www.facturascripts.com</link>
  <description>FacturaScripts es un programa de facturacion y contabilidad gratis'
      . ' para pymes con asesoramiento profesional. Descárgalo ahora, es software libre.</description>';
      
      $comm3item = new comm3_item();
      foreach($comm3item->all() as $it)
      {
            echo '<item>
      <title>'.$it->resumen(60).'</title>
      <link>https://www.facturascripts.com/comm3/'.$it->url(TRUE).'</link>
      <description>'.$it->resumen(300).'</description>
      </item>';
      }
      
      echo '</channel></rss>';
   }
}
