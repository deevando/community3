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
      foreach($item->all( mt_rand(0, 500) ) as $it)
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
         /// Guardamos en stats los datos agrupados de versión, fecha, descargas y activos
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
      
      /// Obtenemos el código de país de los stats individuales
      foreach($stat_item->all( mt_rand(0, 1000) ) as $sti0)
      {
         /**
          * Para no saturar, solamente obtenemos el código de pais
          * si no lo tenemos y si al tirar un dado de 4 caras, sale la 0
          */
         if( is_null($sti0->codpais) AND mt_rand(0, 2) == 0)
         {
            $sti0->codpais = $this->get_country($sti0->ip);
            $sti0->save();
            echo '.';
         }
      }
   }
   
   private function get_country($ip)
   {
      if($ip != 'desconocida')
      {
         $json = json_decode( file_get_contents('http://freegeoip.net/json/'.$ip) );
         return $json->{'country_code'};
      }
      else
         return NULL;
   }
}

new cron_comm3();