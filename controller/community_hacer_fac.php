<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015-2016, Carlos García Gómez. All Rights Reserved. 
 */

require_once __DIR__.'/community_home.php';

/**
 * Description of community_hacer_fac
 *
 * @author carlos
 */
class community_hacer_fac extends community_home
{
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Programa para hacer facturas', 'community', FALSE, FALSE);
   }
   
   protected function public_core()
   {
      parent::public_core();
      
      $this->page_title = 'Programa para hacer facturas | Programa facturacion gratuito';
      $this->page_description = 'Programa para hacer facturas gratis, el mejor programa para hacer '
              .'facturas en cualquier sector empresarial con facturacion electronica. Descargalo Ahora.';
      $this->page_keywords = 'programa facturacion gratuito, programa para hacer facturas, programa para hacer facturas gratis';
      $this->template = 'public/hacer_facturas';
   }
}
