<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015-2016, Carlos García Gómez. All Rights Reserved. 
 */

require_model('comm3_comment.php');
require_model('comm3_item.php');
require_model('comm3_plugin.php');
require_model('comm3_plugin_key.php');
require_model('comm3_stat.php');
require_model('comm3_stat_item.php');
require_model('comm3_visitante.php');

class cron_comm3
{
   public function __construct()
   {
      $item = new comm3_item();
      $item->cron_job();
      $comment = new comm3_comment();
      foreach($item->all() as $it)
      {
         /**
          * Para no saturar, solamente obtenemos el código de pais
          * si no lo tenemos y si al tirar un dado de 4 caras, sale la 0
          */
         if( is_null($it->codpais) AND mt_rand(0, 3) == 0)
         {
            $location = $this->get_location($it->ip);
            if($location)
            {
               $it->codpais = $location->{'countryCode'};
               $it->save();
            }
            echo '.';
         }
         else
         {
            /// comprobamos que la propiedad último comentario sea correcta
            $it->ultimo_comentario = NULL;
            foreach( $comment->get_by_iditem($it->id) as $comm)
            {
               $it->ultimo_comentario = $comm->email();
            }
            $it->save();
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
      foreach($stat_item->all() as $sti0)
      {
         /**
          * Para no saturar, solamente obtenemos el código de pais
          * si no lo tenemos y si al tirar un dado de 4 caras, sale la 0
          */
         if( is_null($sti0->codpais) AND mt_rand(0, 3) == 0)
         {
            $location = $this->get_location($sti0->ip);
            if($location)
            {
               $sti0->codpais = $location->{'countryCode'};
               $sti0->save();
            }
            echo '.';
         }
      }
      
      /// obtenemos las descargas de los plugins
      $plugin = new comm3_plugin();
      foreach($stat_item->agrupado_plugins() as $key => $value)
      {
         $plug = $plugin->get_by_nombre($key);
         if($plug)
         {
            /*
            $plug->descargas = intval($value['total']);
            $plug->save();
             * 
             */
         }
      }
      
      $visit0 = new comm3_visitante();
      foreach($visit0->all(0, 500) as $vi)
      {
         /**
          * Para no saturar, solamente obtenemos la localización
          * si no la tenemos y si al tirar un dado de 4 caras, sale la 0
          */
         if( (is_null($vi->codpais) OR is_null($vi->provincia)) AND !is_null($vi->last_ip) AND mt_rand(0, 3) == 0)
         {
            $location = $this->get_location($vi->last_ip);
            if($location)
            {
               $vi->codpais = $location->{'countryCode'};
               $vi->provincia = $location->{'regionName'};
               $vi->ciudad = $location->{'cityName'};
            }
         }
         
         // Obtenemos las interacciones
         $vi->interacciones();
         $vi->compras();
         $vi->save();
         echo '.';
      }
   }
   
   private function get_location($ip)
   {
      if($ip != 'desconocida' AND $ip != '::1')
      {
         $key = '20b96dca8b9a5d37b0355e9461c66e76eed30a2274422fa6213d9de6ffb2b34e';
         $data = @file_get_contents('http://api.ipinfodb.com/v3/ip-city/?key='.$key.'&ip='.$ip.'&format=json');
         if($data)
         {
            return json_decode($data);
         }
         else
            return FALSE;
      }
      else
         return FALSE;
   }
}

new cron_comm3();