<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
 */

/**
 * Description of community_plugin_keys
 *
 * @author carlos
 */
class community_plugin_keys extends fs_controller
{
   public $page_title;
   public $page_description;
   public $page_keywords;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Tus claves de plugins', 'community', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      
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
      $this->template = 'public/plugin_keys';
   }
}
