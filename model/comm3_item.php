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

require_model('comm3_visitante.php');

/**
 * Description of item
 *
 * @author carlos
 */
class comm3_item extends fs_model
{
   public $id;
   public $tipo;
   public $email;
   public $nick;
   public $codpais;
   public $creado;
   public $actualizado;
   public $ip;
   public $texto;
   public $info;
   public $privado;
   public $destacado;
   public $url_title;
   public $tags;
   public $num_comentarios;
   public $asignados;
   public $estado;
   public $ultimo_comentario;
   public $prioridad;
   public $perfil;
   
   public function __construct($i = FALSE)
   {
      parent::__construct('comm3_items');
      if($i)
      {
         $this->id = $this->intval($i['id']);
         $this->tipo = $i['tipo'];
         $this->email = $i['email'];
         $this->nick = $i['nick'];
         $this->codpais = $i['codpais'];
         $this->creado = intval($i['creado']);
         $this->actualizado = intval($i['actualizado']);
         $this->ip = $i['ip'];
         $this->texto = $i['texto'];
         $this->info = $i['info'];
         $this->privado = $this->str2bool($i['privado']);
         $this->destacado = $this->str2bool($i['destacado']);
         $this->url_title = $i['url_title'];
         $this->tags = $i['tags'];
         $this->num_comentarios = intval($i['num_comentarios']);
         $this->asignados = $i['asignados'];
         $this->estado = $i['estado'];
         $this->ultimo_comentario = $i['ultimo_comentario'];
         
         $this->prioridad = 0;
         if( isset($i['prioridad']) )
         {
            $this->prioridad = intval($i['prioridad']);
         }
         
         $this->perfil = $i['perfil'];
      }
      else
      {
         $this->id = NULL;
         $this->tipo = 'question';
         $this->email = NULL;
         $this->nick = NULL;
         $this->codpais = NULL;
         $this->creado = time();
         $this->actualizado = time();
         $this->ip = NULL;
         $this->texto = '';
         $this->info = '';
         $this->privado = FALSE;
         $this->destacado = FALSE;
         $this->url_title = NULL;
         $this->tags = NULL;
         $this->num_comentarios = 0;
         $this->asignados = NULL;
         $this->estado = 'nuevo';
         $this->ultimo_comentario = NULL;
         $this->prioridad = 0;
         $this->perfil = NULL;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function url($sitemap = FALSE)
   {
      if($sitemap)
      {
         if(comm3_path() AND $this->url_title)
         {
            return '/foro/'.$this->url_title;
         }
         else
         {
            return 'index.php?page=community_item&amp;id='.$this->id;
         }
      }
      else
      {
         return 'index.php?page=community_item&id='.$this->id;
      }
   }
   
   /**
    * Devuelve TRUE si el contenido debe ser oculto para el visitante.
    * @param comm3_visitante $visitante
    * @return boolean
    */
   public function ocultar_publico($visitante)
   {
      if($this->privado)
      {
         if(!$visitante)
         {
            return TRUE;
         }
         else if( !is_null($this->email) AND $this->email == $visitante->email )
         {
            return FALSE;
         }
         else
            return TRUE;
      }
      else
         return FALSE;
   }
   
   /**
    * Devuelve TRUE si el contenido debe ser oculto para el usuario.
    * @param fs_user $user
    * @return boolean
    */
   public function ocultar_privado($user)
   {
      if($user->admin)
      {
         return FALSE;
      }
      else if($this->privado)
      {
         if( !is_null($this->nick) AND $this->nick == $user->nick)
         {
            return FALSE;
         }
         else if($this->asignados == '['.$user->nick.']')
         {
            return FALSE;
         }
         else if( !is_null($this->email) )
         {
            $vis0 = new comm3_visitante();
            $visitante = $vis0->get($this->email);
            if($visitante)
            {
               return !$visitante->autorizado($user->nick);
            }
            else
               return TRUE;
         }
         else
            return TRUE;
      }
      else
         return FALSE;
   }
   
   public function email()
   {
      if( !is_null($this->nick) )
      {
         return $this->nick;
      }
      else
      {
         $aux = explode('@', $this->email);
         if( count($aux) == 2 )
         {
            return $aux[0].'_'.ord( substr($aux[1], 0, 1) );
         }
         else
            return '-';
      }
   }
   
   public function tipo()
   {
      if($this->tipo == 'question')
      {
         return 'Pregunta';
      }
      else if($this->tipo == 'page')
      {
         return 'Documentación';
      }
      else if($this->tipo == 'changelog')
      {
         return 'Actualización';
      }
      else if($this->tipo == 'task')
      {
         return 'Tarea';
      }
      else
         return ucfirst($this->tipo);
   }
   
   public function resumen($len = 200)
   {
      $a = array(
          "/\[i\](.*?)\[\/i\]/is",
          "/\[b\](.*?)\[\/b\]/is",
          "/\[u\](.*?)\[\/u\]/is",
          "/\[big\](.*?)\[\/big\]/is",
          "/\[small\](.*?)\[\/small\]/is",
          "/\[code\](.*?)\[\/code\]/is",
          "/\[img\](.*?)\[\/img\]/is",
          "/\[url\](.*?)\[\/url\]/is",
          "/\[url=(.*?)\](.*?)\[\/url\]/is",
          "/\[youtube\](.*?)\[\/youtube\]/is",
          "/\s\s+/"
      );
      $b = array(
          " $1 ",
          " $1 ",
          " $1 ",
          " $1 ",
          " $1 ",
          " $1 ",
          " ",
          " ",
          " ",
          "  ",
          " ",
      );
      $texto = trim( preg_replace($a, $b, $this->texto) );
      
      return $this->true_text_break($texto, $len);
   }
   
   private function true_text_break($desc, $max_t_width=500)
   {
      if( mb_strlen($desc) <= $max_t_width )
      {
         return $desc;
      }
      else
      {
         $description = '';
         
         foreach(explode(' ', $desc) as $aux)
         {
            if( mb_strlen($description.' '.$aux) < $max_t_width-3 )
            {
               if($description == '')
               {
                  $description = $aux;
               }
               else
                  $description .= ' ' . $aux;
            }
            else
               break;
         }
         
         return $description.'...';
      }
   }
   
   public function creado()
   {
      return date('d-m-Y H:i:s', $this->creado);
   }
   
   public function actualizado()
   {
      return date('d-m-Y H:i:s', $this->actualizado);
   }
   
   public function timesince()
   {
      if( !is_null($this->actualizado) )
      {
         $time = time() - $this->actualizado;
         
         if($time <= 60)
         {
            $rounded = round($time/60,0);
            if ($rounded==0)
            {
               return 'ahora mismo';
            }
            else if ($rounded==1)
            {
               return 'hace '.$rounded.' segundo';
            }
            else
            {
               return 'hace '.$rounded.' segundos';
            }
         }
         else if(60 < $time && $time <= 3600)
         {
            $rounded = round($time/60,0);
            if ($rounded==1)
            {
               return 'hace '.$rounded.' minuto';
            }
            else
            {
               return 'hace '.$rounded.' minutos';
            }
         }
         else if(3600 < $time && $time <= 86400)
         {
            $rounded = round($time/3600,0);
            if ($rounded==1)
            {
               return 'hace '.$rounded.' hora';
            }
            else
            {
               return 'hace '.$rounded.' horas';
            }
         }
         else if(86400 < $time && $time <= 604800)
         {
            $rounded = round($time/86400,0);
            if ($rounded==1)
            {
               return 'hace '.$rounded.' día';
            }
            else
            {
               return 'hace '.$rounded.' días';
            }
         }
         else if(604800 < $time && $time <= 2592000)
         {
            $rounded = round($time/604800,0);
            if ($rounded==1)
            {
               return 'hace '.$rounded.' semana';
            }
            else
            {
               return 'hace '.$rounded.' semanas';
            }
         }
         else if(2592000 < $time && $time <= 29030400)
         {
            $rounded = round($time/2592000,0);
            if ($rounded==1)
            {
               return 'hace '.$rounded.' mes';
            }
            else
            {
               return 'hace '.$rounded.' meses';
            }
         }
         else if($time > 29030400)
         {
            return 'hace más de un año';
         }
      }
      else
         return 'fecha desconocida';
   }
   
   public function bootstrap_class()
   {
      if($this->estado == 'cerrado')
      {
         return '';
      }
      else if($this->tipo == 'error')
      {
         return 'danger';
      }
      else if($this->tipo == 'idea')
      {
         return 'success';
      }
      else if($this->tipo == 'question')
      {
         return 'warning';
      }
      else
      {
         return 'info';
      }
   }
   
   private function new_url_title()
   {
      $url_title = substr( strtolower($this->texto), 0, 60 );
      $changes = array('/à/' => 'a', '/á/' => 'a', '/â/' => 'a', '/ã/' => 'a', '/ä/' => 'a',
          '/å/' => 'a', '/æ/' => 'ae', '/ç/' => 'c', '/è/' => 'e', '/é/' => 'e', '/ê/' => 'e',
          '/ë/' => 'e', '/ì/' => 'i', '/í/' => 'i', '/î/' => 'i', '/ï/' => 'i', '/ð/' => 'd',
          '/ñ/' => 'n', '/ò/' => 'o', '/ó/' => 'o', '/ô/' => 'o', '/õ/' => 'o', '/ö/' => 'o',
          '/ő/' => 'o', '/ø/' => 'o', '/ù/' => 'u', '/ú/' => 'u', '/û/' => 'u', '/ü/' => 'u',
          '/ű/' => 'u', '/ý/' => 'y', '/þ/' => 'th', '/ÿ/' => 'y', '/ñ/' => 'ny',
          '/&quot;/' => '-'
      );
      $url_title = preg_replace(array_keys($changes), $changes, $url_title);
      $url_title = preg_replace('/[^a-z0-9]/i', '-', $url_title);
      $url_title = preg_replace('/-+/', '-', $url_title);
      
      if( substr($url_title, 0, 1) == '-' )
         $url_title = substr($url_title, 1);
      
      if( substr($url_title, -1) == '-' )
         $url_title = substr($url_title, 0, -1);
      
      $url_title .= '-'.mt_rand(0, 999).'.html';
      
      return $url_title;
   }
   
   public function get($id)
   {
      $data = $this->db->select("SELECT * FROM comm3_items WHERE id = ".$this->var2str($id).";");
      if($data)
      {
         return new comm3_item($data[0]);
      }
      else
         return FALSE;
   }
   
   public function get_by_url_title($title)
   {
      $data = $this->db->select("SELECT * FROM comm3_items WHERE url_title = ".$this->var2str($title).";");
      if($data)
      {
         return new comm3_item($data[0]);
      }
      else
         return FALSE;
   }
   
   public function get_by_tag($tag)
   {
      $data = $this->db->select("SELECT * FROM comm3_items WHERE tags LIKE '%[".str_replace("'", '', $tag)."]%';");
      if($data)
      {
         return new comm3_item($data[0]);
      }
      else
         return FALSE;
   }

   public function exists()
   {
      if( is_null($this->id) )
      {
         return FALSE;
      }
      else
         return $this->db->select("SELECT * FROM comm3_items WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function save()
   {
      $this->texto = $this->no_html($this->texto);
      $this->info = $this->no_html($this->info);
      
      if( $this->exists() )
      {
         $sql = "UPDATE comm3_items SET tipo = ".$this->var2str($this->tipo).
                 ", email = ".$this->var2str($this->email).
                 ", nick = ".$this->var2str($this->nick).
                 ", codpais = ".$this->var2str($this->codpais).
                 ", creado = ".$this->var2str($this->creado).
                 ", actualizado = ".$this->var2str($this->actualizado).
                 ", ip = ".$this->var2str($this->ip).
                 ", texto = ".$this->var2str($this->texto).
                 ", info = ".$this->var2str($this->info).
                 ", privado = ".$this->var2str($this->privado).
                 ", destacado = ".$this->var2str($this->destacado).
                 ", tags = ".$this->var2str($this->tags).
                 ", num_comentarios = ".$this->var2str($this->num_comentarios).
                 ", asignados = ".$this->var2str($this->asignados).
                 ", estado = ".$this->var2str($this->estado).
                 ", ultimo_comentario = ".$this->var2str($this->ultimo_comentario).
                 ", url_title = ".$this->var2str($this->url_title).
                 ", prioridad = ".$this->var2str($this->prioridad).
                 ", perfil = ".$this->var2str($this->perfil).
                 " WHERE id = ".$this->var2str($this->id).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         if( is_null($this->url_title) )
         {
            $this->url_title = $this->new_url_title();
         }
         
         $sql = "INSERT INTO comm3_items (tipo,email,nick,codpais,creado,actualizado,
            ip,texto,info,privado,destacado,url_title,tags,num_comentarios,asignados,
            estado,ultimo_comentario,prioridad,perfil) VALUES ".
                 "(".$this->var2str($this->tipo).
                 ",".$this->var2str($this->email).
                 ",".$this->var2str($this->nick).
                 ",".$this->var2str($this->codpais).
                 ",".$this->var2str($this->creado).
                 ",".$this->var2str($this->actualizado).
                 ",".$this->var2str($this->ip).
                 ",".$this->var2str($this->texto).
                 ",".$this->var2str($this->info).
                 ",".$this->var2str($this->privado).
                 ",".$this->var2str($this->destacado).
                 ",".$this->var2str($this->url_title).
                 ",".$this->var2str($this->tags).
                 ",".$this->var2str($this->num_comentarios).
                 ",".$this->var2str($this->asignados).
                 ",".$this->var2str($this->estado).
                 ",".$this->var2str($this->ultimo_comentario).
                 ",".$this->var2str($this->prioridad).
                 ",".$this->var2str($this->perfil).");";
         
         if( $this->db->exec($sql) )
         {
            $this->id = $this->db->lastval();
            return TRUE;
         }
         else
            return FALSE;
      }
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM comm3_items WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function all($offset = 0, $limit = FS_ITEM_LIMIT)
   {
      $vlist = array();
      
      $sql = "SELECT * FROM comm3_items ORDER BY actualizado DESC";
      $data = $this->db->select_limit($sql, $limit, $offset);
      if($data)
      {
         foreach($data as $d)
         {
            $vlist[] = new comm3_item($d);
         }
      }
      
      return $vlist;
   }
   
   public function all_by_tipo($tipo, $offset = 0)
   {
      $vlist = array();
      
      $sql = "SELECT * FROM comm3_items WHERE tipo = ".$this->var2str($tipo)." ORDER BY destacado DESC, actualizado DESC";
      $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if($data)
      {
         foreach($data as $d)
         {
            $vlist[] = new comm3_item($d);
         }
      }
      
      return $vlist;
   }
   
   public function all_by_email($email, $offset = 0)
   {
      $vlist = array();
      
      $sql = "SELECT * FROM comm3_items WHERE email = ".$this->var2str($email)." ORDER BY actualizado DESC";
      $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if($data)
      {
         foreach($data as $d)
         {
            $vlist[] = new comm3_item($d);
         }
      }
      
      return $vlist;
   }
   
   public function all_by_nick($nick, $offset = 0, $tipo = FALSE)
   {
      $vlist = array();
      
      $sql = "SELECT * FROM comm3_items WHERE nick = ".$this->var2str($nick);
      if($tipo)
      {
         $sql .= " AND tipo = ".$this->var2str($tipo);
      }
      $sql .= " ORDER BY actualizado DESC";
      
      $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if($data)
      {
         foreach($data as $d)
         {
            $vlist[] = new comm3_item($d);
         }
      }
      
      return $vlist;
   }
   
   public function all_by_tag($tag, $offset = 0)
   {
      $ilist = array();
      
      $sql = "SELECT * FROM comm3_items WHERE tags LIKE '%[".str_replace("'", '', $tag)."]%' ORDER BY actualizado DESC";
      $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if($data)
      {
         foreach($data as $d)
         {
            $ilist[] = new comm3_item($d);
         }
      }
      
      return $ilist;
   }
   
   /**
    * Devuelve todos los elementos de visitantes que tengan a $nick como autorizado.
    * @param type $nick
    * @param type $offset
    * @return \comm3_item
    */
   public function all_for_nick($nick, $offset = 0)
   {
      $vlist = array();
      
      $sql = "SELECT * FROM comm3_items WHERE asignados = '[".$nick."]' OR (tipo != 'task' AND email IN".
              " (SELECT email FROM comm3_visitantes WHERE autorizado = ".$this->var2str($nick).
              " OR autorizado2 = ".$this->var2str($nick).
              " OR autorizado3 = ".$this->var2str($nick).
              " OR autorizado4 = ".$this->var2str($nick).
              " OR autorizado5 = ".$this->var2str($nick).")) ORDER BY destacado DESC, actualizado DESC";
      $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if($data)
      {
         foreach($data as $d)
         {
            $vlist[] = new comm3_item($d);
         }
      }
      
      return $vlist;
   }
   
   public function all_by_visitante($visitante, $offset = 0)
   {
      $vlist = array();
      $sql = "SELECT * FROM comm3_items WHERE email = ".$this->var2str($visitante->email).
              " OR nick = ".$this->var2str($visitante->nick).
              " ORDER BY actualizado DESC";
      
      $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if($data)
      {
         foreach($data as $d)
         {
            $vlist[] = new comm3_item($d);
         }
      }
      
      return $vlist;
   }
   
   public function pendientes($offset = 0, $nick = FALSE, $admin = FALSE, $tipo = FALSE)
   {
      $vlist = array();
      
      $sql = "SELECT * FROM comm3_items WHERE (estado != 'cerrado' OR estado is NULL)";
      if($nick)
      {
         $sql .= " AND (privado = false OR asignados = '[".$nick."]' OR email IN".
                 " (SELECT email FROM comm3_visitantes WHERE autorizado = ".$this->var2str($nick).
                 " OR autorizado2 = ".$this->var2str($nick).
                 " OR autorizado3 = ".$this->var2str($nick).
                 " OR autorizado4 = ".$this->var2str($nick).
                 " OR autorizado5 = ".$this->var2str($nick)."))";
      }
      if($tipo)
      {
         $sql .= " AND tipo = ".$this->var2str($tipo);
      }
      else
      {
         $sql .= " AND tipo != 'task'";
      }
      $sql .= " ORDER BY destacado DESC, prioridad DESC, actualizado DESC";
      
      $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if($data)
      {
         foreach($data as $d)
         {
            $vlist[] = new comm3_item($d);
         }
      }
      
      return $vlist;
   }
   
   public function pendientes_by_tipo($tipo, $offset = 0)
   {
      $vlist = array();      
      $sql = "SELECT * FROM comm3_items WHERE tipo = ".$this->var2str($tipo).
              " AND (estado != 'cerrado' OR estado is NULL) ORDER BY destacado DESC, prioridad DESC, actualizado DESC";
      
      $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if($data)
      {
         foreach($data as $d)
         {
            $vlist[] = new comm3_item($d);
         }
      }
      
      return $vlist;
   }
   
   public function num_pendientes($nick = FALSE, $admin = FALSE, $tipo = FALSE)
   {
      $num = 0;
      
      $sql = "SELECT count(id) as num FROM comm3_items WHERE (estado != 'cerrado' OR estado is NULL)";
      if($nick)
      {
         $sql .= " AND (privado = false OR asignados = '[".$nick."]' OR email IN".
                 " (SELECT email FROM comm3_visitantes WHERE autorizado = ".$this->var2str($nick).
                 " OR autorizado2 = ".$this->var2str($nick).
                 " OR autorizado3 = ".$this->var2str($nick).
                 " OR autorizado4 = ".$this->var2str($nick).
                 " OR autorizado5 = ".$this->var2str($nick)."))";
      }
      if($tipo)
      {
         $sql .= " AND tipo = ".$this->var2str($tipo);
      }
      else
      {
         $sql .= " AND tipo != 'task'";
      }
      $sql .= ";";
      
      $data = $this->db->select($sql);
      if($data)
      {
         $num = intval($data[0]['num']);
      }
      
      return $num;
   }
   
   public function search($query, $offset = 0)
   {
      $vlist = array();
      $query = $this->no_html( strtolower( trim( str_replace(' ', '%', $query) ) ) );
      
      $sql = "SELECT * FROM comm3_items WHERE lower(texto) LIKE '%".$query."%' ORDER BY actualizado DESC";
      $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if($data)
      {
         foreach($data as $d)
         {
            $vlist[] = new comm3_item($d);
         }
      }
      
      return $vlist;
   }
   
   public function cron_job()
   {
      $sql = "UPDATE comm3_items SET estado = 'cerrado', destacado = false "
              . "WHERE estado != 'cerrado' AND actualizado < ".$this->var2str( strtotime('-6 months') ).";";
      $this->db->exec($sql);
   }
}
