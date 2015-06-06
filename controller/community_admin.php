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
 * Description of community_admin
 *
 * @author carlos
 */
class community_admin extends fs_controller
{
   public $anuncio;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Admininstración', 'comunidad', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->anuncio = '';
      
      if($this->user->admin)
      {
         $fsvar = new fs_var();
         
         if( isset($_POST['anuncio']) )
         {
            $this->anuncio = $_POST['anuncio'];
            if( $fsvar->simple_save('comm3_anuncio', $this->anuncio) )
            {
               $this->new_message('Datos guardados correctamente.');
            }
            else
               $this->new_error_msg('Error al guardar los datos');
         }
         else
         {
            $this->anuncio = $fsvar->simple_get('comm3_anuncio');
         }
      }
      else
      {
         $this->new_error_msg('Solos los administradores pueden acceder a esta página.');
      }
   }
}
