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
 * Description of community_changelog
 *
 * @author carlos
 */
class community_changelog extends fs_controller
{
   public $page_title;
   public $page_description;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Changelog', 'comunidad', FALSE, FALSE);
   }
   
   protected function public_core()
   {
      $this->page_title = 'Cambios &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Todos los cambios en FacturaScripts y sus plugins.';
      $this->template = 'public/portada';
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
