<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
 */

require_model('comm3_comment.php');
require_model('comm3_item.php');
require_model('comm3_stat.php');
require_model('comm3_stat_item.php');

class cron_comm3
{
   public function __construct()
   {
      $item = new comm3_item();
      foreach($item->all() as $it)
      {
         $it->num_comentarios();
         $it->save();
      }
      
      $stat = new comm3_stat();
      $stat_item = new comm3_stat_item();
      
      foreach($stat_item->agrupado() as $value)
      {
         $st0 = $stat->get($value['fecha'], $value['version']);
         if($st0)
         {
            $st0->activos = $value['activos'];
            $st0->save();
         }
         else
         {
            $st0 = new comm3_stat();
            $st0->fecha = $value['fecha'];
            $st0->version = $value['version'];
            $st0->activos = $value['activos'];
            $st0->save();
         }
      }
   }
}

new cron_comm3();