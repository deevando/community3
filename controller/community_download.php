<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_model('comm3_stat.php');

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_download extends fs_controller
{
   public $last_version;
   public $page_title;
   public $page_description;
   public $total_descargas;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Descargas', 'comunidad', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      
   }
   
   protected function public_core()
   {
      $this->page_title = 'Descargas &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Página de descargas de FacturaScripts.';
      $this->template = 'public/download';
      
      $this->last_version = $this->cache->get('comm3_last_version');
      if(!$this->last_version)
      {
         $this->last_version = file_get_contents('https://raw.githubusercontent.com/NeoRazorX/facturascripts_2015/master/VERSION');
         $this->cache->set('comm3_last_version', $this->last_version, 86400);
      }
      
      $stat = new comm3_stat();
      $last_stat = $stat->get($stat->fecha, $this->last_version);
      if(!$last_stat)
      {
         $last_stat = new comm3_stat();
         $last_stat->version = $this->last_version;
      }
      $last_stat->descargas++;
      $last_stat->save();
      
      $this->total_descargas = 0;
      $data = $this->db->select("SELECT SUM(descargas) as descargas FROM comm3_stats;");
      if($data)
      {
         $this->total_descargas = intval($data[0]['descargas']);
      }
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
