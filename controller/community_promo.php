<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2016, Carlos García Gómez. All Rights Reserved. 
 */

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
   }
}
