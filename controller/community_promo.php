<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2016  Carlos Garcia Gomez  neorazorx@gmail.com
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

require_model('comm3_visitante.php');

/**
 * Description of community_promo
 *
 * @author carlos
 */
class community_promo extends fs_controller
{
   public $page_title;
   public $page_description;
   public $page_keywords;
   public $visitante;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Promociones FacturaScripts', 'community', FALSE, FALSE);
   }
   
   protected function public_core()
   {
      $this->page_title = 'Promociones FacturaScripts';
      $this->page_description = 'Tu negocio y tu tienda PrestaShop conectados y perfectamente sincronizados.';
      $this->page_keywords = 'prestashop erp, sincronizar prestashop, facturascripts prestashop';
      $this->template = 'public/promo';
      $this->visitante = FALSE;
      
      if( isset($_COOKIE['rid']) )
      {
         $visit0 = new comm3_visitante();
         $this->visitante = $visit0->get_by_rid($_COOKIE['rid']);
      }
   }
}
