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
      /**
       * Este es un pequeño hack para poner el número de comentarios de cada item,
       * ya que se me olvidó hacerlo en el proceso de importación.
       */
      $item = new comm3_item();
      $comment = new comm3_comment();
      foreach($item->all( mt_rand(0, 1000) ) as $it)
      {
         $num = 0;
         $last_email = NULL;
         foreach($comment->get_by_iditem($it->id) as $comm)
         {
            $num++;
            $last_email = $comm->email();
         }
         
         if( $it->num_comentarios != $num OR $it->ultimo_comentario != $last_email )
         {
            $it->num_comentarios = $num;
            $it->ultimo_comentario = $last_email;
            $it->save();
            echo '.';
         }
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