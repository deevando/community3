<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015-2016, Carlos García Gómez. All Rights Reserved. 
 */

/**
 * Description of community_contabilidad
 *
 * @author carlos
 */
class community_contabilidad extends fs_controller
{
   public $page_title;
   public $page_description;
   public $page_keywords;
   public $visitante;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Programa para hacer facturas', 'community', FALSE, FALSE);
   }
   
   protected function public_core()
   {
      $this->page_title = 'Software contabilidad | Programa facturacion autonomos';
      $this->page_description = 'Potente programa de facturacion para autonomos, que incluye software'
              .' de contabilidad, descargate el mejor programa de facturacion gratuito ahora.';
      $this->page_keywords = 'programa facturacion autonomos, software contabilidad, programa contabilidad autonomos';
      $this->template = 'public/contabilidad';
      $this->visitante = FALSE;
   }
}
