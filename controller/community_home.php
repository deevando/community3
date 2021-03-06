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
require_model('comm3_partner.php');
require_model('comm3_visitante.php');

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_home extends fs_controller
{
   public $anuncio;
   public $page_title;
   public $page_description;
   public $page_keywords;
   public $rid;
   public $visitante;
   
   public function __construct($name = __CLASS__, $title = 'home', $folder = '', $admin = FALSE, $shmenu = TRUE, $important = FALSE)
   {
      if($name == 'comunity_home')
      {
         $shmenu = FALSE;
      }
      
      parent::__construct($name, $title, $folder, $admin, $shmenu, $important);
   }
   
   protected function private_core()
   {
      $visit0 = new comm3_visitante();
      $this->visitante = $visit0->get($this->user->email);
      if($this->visitante)
      {
         if($this->user->admin)
         {
            $this->visitante->perfil = 'admin';
         }
      }
   }
   
   protected function public_core()
   {
      $this->page_title = 'FacturaScripts: Programa de facturacion gratis | Software contabilidad';
      $this->page_description = 'FacturaScripts es un programa de facturacion y contabilidad gratis'
              . ' para pymes con asesoramiento profesional. Descárgalo ahora, es software libre.';
      $this->page_keywords = 'programa de facturacion gratis, programas de contabilidad,'
              . ' programas de facturación y contabilidad, programa contabilidad gratis,'
              . ' programa facturacion gratuito, programa para hacer facturas,'
              . ' programa para hacer facturas gratis, programa facturacion autonomos,'
              . ' software contabilidad, programa contabilidad autonomos';
      $this->template = 'public/portada';
      
      $this->rid = FALSE;
      $this->visitante = FALSE;
      if( isset($_COOKIE['rid']) )
      {
         $visit0 = new comm3_visitante();
         $this->visitante = $visit0->get_by_rid($_COOKIE['rid']);
      }
      
      if($this->visitante)
      {
         $this->rid = $this->visitante->rid;
         $this->visitante->last_login = time();
         $this->visitante->save();
      }
      
      $fsvar = new fs_var();
      $this->anuncio = $fsvar->simple_get('comm3_anuncio');
   }
   
   public function noticias()
   {
      $noticias = array();
      $item0 = new comm3_item();
      $all = $item0->all_by_tipo('changelog');
      
      for($i = 0; $i < 10; $i++)
      {
         if( isset($all[$i]) )
         {
            $noticias[] = $all[$i];
         }
      }
      
      return $noticias;
   }
   
   public function partners()
   {
      $part0 = new comm3_partner();
      $partners = $part0->all();
      shuffle($partners);
      
      return $partners;
   }
}
