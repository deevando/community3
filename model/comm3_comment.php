<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2015-2016  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of comment
 *
 * @author carlos
 */
class comm3_comment extends fs_model
{
   public $id;
   public $iditem;
   public $email;
   public $rid;
   public $codpais;
   public $nick;
   public $creado;
   public $ip;
   public $texto;
   public $privado;
   public $perfil;
   
   public function __construct($v = FALSE)
   {
      parent::__construct('comm3_comments', 'plugins/community3/');
      if($v)
      {
         $this->id = $this->intval($v['id']);
         $this->iditem = $this->intval($v['iditem']);
         $this->email = $v['email'];
         $this->rid = $v['rid'];
         $this->codpais = $v['codpais'];
         $this->nick = $v['nick'];
         $this->creado = $v['creado'];
         $this->ip = $v['ip'];
         $this->texto = $v['texto'];
         $this->privado = $this->str2bool($v['privado']);
         $this->perfil = $v['perfil'];
      }
      else
      {
         $this->id = NULL;
         $this->iditem = NULL;
         $this->email = NULL;
         $this->rid = NULL;
         $this->codpais = NULL;
         $this->nick = NULL;
         $this->creado = time();
         $this->ip = NULL;
         $this->texto = '';
         $this->privado = FALSE;
         $this->perfil = NULL;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function email()
   {
      if( is_null($this->nick) )
      {
         $aux = explode('@', $this->email);
         if( count($aux) == 2 )
         {
            return $aux[0].'_'.ord( substr($aux[1], 0, 1) );
         }
         else
            return '-';
      }
      else
         return $this->nick;
   }
   
   public function timesince()
   {
      if( !is_null($this->creado) )
      {
         $time = time() - $this->creado;
         
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
   
   public function get($id)
   {
      $data = $this->db->select("SELECT * FROM comm3_comments WHERE id = ".$this->var2str($id).";");
      if($data)
      {
         return new comm3_comment($data[0]);
      }
      else
         return FALSE;
   }
   
   public function get_by_iditem($iditem)
   {
      $vlist = array ();
      
      $data = $this->db->select("SELECT * FROM comm3_comments WHERE iditem = ".$this->var2str($iditem).";");
      if($data)
      {
         foreach($data as $d)
            $vlist[] = new comm3_comment($d);
      }
      
      return $vlist;
   }
   
   public function get_by_rid($rid)
   {
      $data = $this->db->select("SELECT * FROM comm3_comments WHERE rid = ".$this->var2str($rid).";");
      if($data)
      {
         return new comm3_comment($data[0]);
      }
      else
         return FALSE;
   }
   
   public function duplicated()
   {
      $this->texto = $this->no_html($this->texto);
      
      $sql = "SELECT * FROM comm3_comments WHERE email = ".$this->var2str($this->email)
              ." AND iditem = ".$this->var2str($this->iditem)
              ." AND texto = ".$this->var2str($this->texto).";";
      $data = $this->db->select($sql);
      if($data)
      {
         return TRUE;
      }
      else
      {
         return FALSE;
      }
   }
   
   public function exists()
   {
      if( is_null($this->id) )
      {
         return FALSE;
      }
      else
         return $this->db->select("SELECT * FROM comm3_comments WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function save()
   {
      $this->texto = $this->no_html($this->texto);
      
      if( $this->exists() )
      {
         $sql = "UPDATE comm3_comments SET iditem = ".$this->var2str($this->iditem).
                 ", email = ".$this->var2str($this->email).
                 ", rid = ".$this->var2str($this->rid).
                 ", codpais = ".$this->var2str($this->codpais).
                 ", nick = ".$this->var2str($this->nick).
                 ", creado = ".$this->var2str($this->creado).
                 ", ip = ".$this->var2str($this->ip).
                 ", privado = ".$this->var2str($this->privado).
                 ", perfil = ".$this->var2str($this->perfil).
                 ", texto = ".$this->var2str($this->texto).
                 " WHERE id = ".$this->var2str($this->id).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO comm3_comments (iditem,email,rid,codpais,nick,creado,ip,texto,privado,perfil)
            VALUES (".$this->var2str($this->iditem).
                 ",".$this->var2str($this->email).
                 ",".$this->var2str($this->rid).
                 ",".$this->var2str($this->codpais).
                 ",".$this->var2str($this->nick).
                 ",".$this->var2str($this->creado).
                 ",".$this->var2str($this->ip).
                 ",".$this->var2str($this->texto).
                 ",".$this->var2str($this->privado).
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
      return $this->db->exec("DELETE FROM comm3_comments WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function all()
   {
      $vlist = array();
      
      $data = $this->db->select("SELECT * FROM comm3_comments ORDER BY creado DESC;");
      if($data)
      {
         foreach($data as $d)
            $vlist[] = new comm3_comment($d);
      }
      
      return $vlist;
   }
}
