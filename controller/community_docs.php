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

/**
 * Description of community_docs
 *
 * @author carlos
 */
class community_docs extends fs_controller
{
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Documentación', 'comunidad');
   }
   
   protected function public_core()
   {
      $this->page_title = 'Todo &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Todas las preguntas, ideas e informes de errores de FacturaScripts';
      $this->page_keywords = 'facturascripts, eneboo, abanq, woocommerce, prestashop, facturae';
      $this->template = 'public/docs';
   }
}
