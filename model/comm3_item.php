<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
   public $rid;
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
   
   public function __construct($i = FALSE)
   {
      parent::__construct('comm3_items', 'plugins/community3/');
      if($i)
      {
         $this->id = $this->intval($i['id']);
         $this->tipo = $i['tipo'];
         $this->email = $i['email'];
         $this->rid = $i['rid'];
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
      }
      else
      {
         $this->id = NULL;
         $this->tipo = NULL;
         $this->email = NULL;
         $this->rid = NULL;
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
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function url()
   {
      return 'index.php?page=community_item&id='.$this->id;
   }
   
   public function email()
   {
      $aux = explode('@', $this->email);
      if( count($aux) == 2 )
      {
         return $aux[0];
      }
      else
         return '-';
   }
   
   public function tipo()
   {
      if($this->tipo == 'question')
      {
         return 'Pregunta';
      }
      else
         return ucfirst($this->tipo);
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
         return 'bg-danger';
      }
      else if($this->tipo == 'idea')
      {
         return 'bg-success';
      }
      else if($this->tipo == 'question')
      {
         return 'bg-warning';
      }
      else
      {
         return 'bg-info';
      }
   }
   
   private function new_url_title()
   {
      $url_title = substr( strtolower($this->texto), 0, 90 );
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
   
   public function num_comentarios()
   {
      $data = $this->db->select("SELECT COUNT(*) as total FROM comm3_comments WHERE iditem = ".$this->var2str($this->id).";");
      if($data)
      {
         $this->num_comentarios = intval($data[0]['total']);
      }
      
      return $this->num_comentarios;
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

   public function exists()
   {
      if( is_null($this->email) )
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
         $sql = "UPDATE comm3_items SET tipo = ".$this->var2str($this->tipo).", email = ".$this->var2str($this->email).",
            rid = ".$this->var2str($this->rid).", nick = ".$this->var2str($this->nick).",
            codpais = ".$this->var2str($this->codpais).",
            creado = ".$this->var2str($this->creado).", actualizado = ".$this->var2str($this->actualizado).",
            ip = ".$this->var2str($this->ip).", texto = ".$this->var2str($this->texto).",
            info = ".$this->var2str($this->info).", privado = ".$this->var2str($this->privado).",
            destacado = ".$this->var2str($this->destacado).", tags = ".$this->var2str($this->tags).",
            num_comentarios = ".$this->var2str($this->num_comentarios).",
            asignados = ".$this->var2str($this->asignados).", estado = ".$this->var2str($this->estado).",
            url_title = ".$this->var2str($this->url_title)." WHERE id = ".$this->var2str($this->id).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         if( is_null($this->url_title) )
         {
            $this->url_title = $this->new_url_title();
         }
         
         $sql = "INSERT INTO comm3_items (tipo,email,rid,nick,codpais,creado,actualizado,ip,texto,info,privado,
            destacado,url_title,tags,num_comentarios,asignados,estado) VALUES (".$this->var2str($this->tipo).",
            ".$this->var2str($this->email).",".$this->var2str($this->rid).",".$this->var2str($this->nick).",
            ".$this->var2str($this->codpais).",".$this->var2str($this->creado).",".$this->var2str($this->actualizado).",
            ".$this->var2str($this->ip).",".$this->var2str($this->texto).",".$this->var2str($this->info).",
            ".$this->var2str($this->privado).",".$this->var2str($this->destacado).",
            ".$this->var2str($this->url_title).",".$this->var2str($this->tags).",".$this->var2str($this->num_comentarios).",
            ".$this->var2str($this->asignados).",".$this->var2str($this->estado).");";
         
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
   
   public function all($offset = 0)
   {
      $vlist = array();
      
      $sql = "SELECT * FROM comm3_items ORDER BY destacado DESC, actualizado DESC";
      $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if($data)
      {
         foreach($data as $d)
            $vlist[] = new comm3_item($d);
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
            $vlist[] = new comm3_item($d);
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
            $vlist[] = new comm3_item($d);
      }
      
      return $vlist;
   }
   
   public function all_by_ip($ip, $offset = 0)
   {
      $vlist = array();
      
      $sql = "SELECT * FROM comm3_items WHERE ip = ".$this->var2str($ip)." ORDER BY actualizado DESC";
      $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if($data)
      {
         foreach($data as $d)
            $vlist[] = new comm3_item($d);
      }
      
      return $vlist;
   }
   
   public function all_by_rid($rid, $offset = 0)
   {
      $vlist = array();
      
      $sql = "SELECT * FROM comm3_items WHERE rid = ".$this->var2str($rid)." ORDER BY actualizado DESC";
      $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if($data)
      {
         foreach($data as $d)
            $vlist[] = new comm3_item($d);
      }
      
      return $vlist;
   }
   
   public function all_by_nick($nick, $offset = 0)
   {
      $vlist = array();
      
      $sql = "SELECT * FROM comm3_items WHERE nick = ".$this->var2str($nick)." ORDER BY actualizado DESC";
      $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, $offset);
      if($data)
      {
         foreach($data as $d)
            $vlist[] = new comm3_item($d);
      }
      
      return $vlist;
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
            $vlist[] = new comm3_item($d);
      }
      
      return $vlist;
   }
}
