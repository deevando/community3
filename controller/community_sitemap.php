<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2015-2016  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_model('comm3_item.php');
require_model('comm3_plugin.php');

/**
 * Description of community_sitemap
 *
 * @author carlos
 */
class community_sitemap extends fs_controller
{
   public function __construct()
   {
      parent::__construct(__CLASS__, 'sitemap', 'comunidad', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->sitemap();
   }
   
   protected function public_core()
   {
      $this->sitemap();
   }
   
   private function sitemap()
   {
      $this->template = FALSE;
      
      header("Content-type: text/xml");
      echo '<?xml version="1.0" encoding="UTF-8"?>';
      echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
         <url>
            <loc>https://www.facturascripts.com/documentacion</loc>
            <lastmod>2015-10-09</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.5</priority>
         </url>
         <url>
            <loc>https://www.facturascripts.com/plugins</loc>
            <lastmod>2015-10-09</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.5</priority>
         </url>
         <url>
            <loc>https://www.facturascripts.com/programa-para-hacer-facturas</loc>
            <lastmod>2015-10-09</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.5</priority>
         </url>
         <url>
            <loc>https://www.facturascripts.com/software-contabilidad</loc>
            <lastmod>2015-10-09</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.5</priority>
         </url>';
      
      /// plugins
      $comm3plugin = new comm3_plugin();
      foreach($comm3plugin->all() as $plug)
      {
         if($plug->zip_link == '' AND !$plug->oculto)
         {
            echo '<url><loc>',$plug->link,'</loc><lastmod>',
                    Date('Y-m-d', strtotime($plug->ultima_modificacion)),'</lastmod><changefreq>always</changefreq><priority>0.7</priority></url>';
         }
      }
      
      /// preguntas, errores, ideas...
      $comm3item = new comm3_item();
      foreach($comm3item->all(0, 500) as $it)
      {
         if(!$it->privado)
         {
            echo '<url><loc>https://www.facturascripts.com',$it->url(TRUE),'</loc><lastmod>',
                    Date('Y-m-d', $it->actualizado),'</lastmod><changefreq>always</changefreq><priority>0.8</priority></url>';
         }
      }
      
      echo '</urlset>';
   }
}
