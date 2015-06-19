<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2015  Carlos Garcia Gomez  neorazorx@gmail.com
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

require_once 'extras/phpmailer/class.phpmailer.php';
require_once 'extras/phpmailer/class.smtp.php';
require_model('comm3_comment.php');
require_model('comm3_item.php');
require_model('comm3_stat_item.php');
require_model('comm3_visitante.php');

/**
 * Description of community_item
 *
 * @author carlos
 */
class community_item extends fs_controller
{
   public $allow_delete;
   public $comments;
   public $comment_text;
   public $comment_email;
   public $emails;
   public $info_ip;
   public $item;
   public $item_visitante;
   public $page_title;
   public $page_description;
   public $page_keywords;
   public $rid;
   public $visitante;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Item', 'comunidad', FALSE, FALSE);
   }
   
   /**
    * Parte privada de la página.
    */
   protected function private_core()
   {
      /// ¿El usuario tiene permiso para eliminar en esta página?
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      
      $this->item = FALSE;
      $item = new comm3_item();
      $comment = new comm3_comment();
      
      if( isset($_REQUEST['id']) )
      {
         $this->item = $item->get($_REQUEST['id']);
      }
      else if( isset($_REQUEST['title']) )
      {
         $this->item = $item->get_by_url_title($_REQUEST['title']);
      }
      else if( isset($_REQUEST['tag']) )
      {
         $this->item = $item->get_by_tag($_REQUEST['tag']);
      }
      
      if($this->item)
      {
         $this->page->title = $this->title($this->item->texto);
         
         $visit0 = new comm3_visitante();
         $this->item_visitante = $visit0->get($this->item->email);
         if($this->item_visitante)
         {
            if(!$this->allow_delete)
            {
               /// el autorizado puede eliminar
               $this->allow_delete = ($this->item_visitante->autorizado == $this->user->nick);
            }
         }
         else if( filter_var($this->item->email, FILTER_VALIDATE_EMAIL) )
         {
            $this->item_visitante = new comm3_visitante();
            $this->item_visitante->email = $this->item->email;
            $this->item_visitante->codpais = $this->item->codpais;
            $this->item_visitante->last_ip = $this->item->ip;
            $this->item_visitante->rid = $this->random_string(30);
            $this->item_visitante->save();
         }
         
         $this->info_ip = array();
         $stat_item = new comm3_stat_item();
         foreach($stat_item->all_by_ip($this->item->ip) as $si)
         {
            $this->info_ip[] = 'Hay un informe de FacturaScripts '.$si->version.' en esta IP el día '.$si->fecha;
         }
         
         if( isset($_POST['feedback_text']) )
         {
            $this->item->texto = $_POST['feedback_text'];
            $this->item->tags = $_POST['feedback_tags'];
            $this->item->tipo = $_POST['feedback_type'];
            $this->item->estado = $_POST['feedback_estado'];
            $this->item->privado = isset($_POST['feedback_privado']);
            $this->item->destacado = isset($_POST['feedback_destacado']);
            $this->item->actualizado = time();
            
            if( $this->item->save() )
            {
               $this->new_message('Datos modificados correctamente.');
            }
            else
               $this->new_error_msg('Error al modificar los datos.');
         }
         else if( isset($_POST['comentario']) )
         {
            $this->comment_text = $_POST['comentario'];
            
            $comment->iditem = $this->item->id;
            $comment->texto = $this->comment_text;
            $comment->nick = $this->user->nick;
            $comment->email = comm3_get_email_user($this->user);
            $comment->ip = $this->user->last_ip;
            $comment->privado = isset($_POST['privado']);
            $comment->perfil = comm3_get_perfil_user($this->user);
            
            if( $comment->save() )
            {
               $this->item->actualizado = time();
               $this->item->num_comentarios++;
               $this->item->ultimo_comentario = $comment->email();
               
               if( $this->item->save() )
               {
                  $this->new_message('Datos guardados correctamente.');
                  $this->comment_text = '';
                  
                  if($_POST['feedback_sendmail'] != '')
                  {
                     $this->enviar_email($_POST['feedback_sendmail']);
                  }
               }
               else
                  $this->new_error_msg('Error al guardar los datos 2.');
            }
            else
               $this->new_error_msg('Error al guardar los datos.');
         }
         else if( isset($_GET['delete']) )
         {
            $comm2 = $comment->get($_GET['delete']);
            if($comm2)
            {
               if(!$this->allow_delete)
               {
                  $this->new_error_msg('No tienes permiso para eliminar estos datos.');
               }
               else if( $comm2->delete() )
               {
                  $this->new_message('Comentario eliminado correctamente.');
               }
               else
               {
                  $this->new_error_msg('Error al eliminar el comentario.');
               }
            }
            else
            {
               $this->new_error_msg('Comentario no encontrado.');
            }
         }
         else if( isset($_GET['cerrar']) )
         {
            $this->item->estado = 'cerrado';
            $this->item->save();
         }
         
         $this->comments = $comment->get_by_iditem($this->item->id);
         
         $this->emails = array();
         if( !is_null($this->item->email) AND $this->item->email != '' )
         {
            $this->emails[] = $this->item->email;
         }
         foreach($this->comments as $comm2)
         {
            if( !is_null($comm2->email) AND $comm2->email != '' AND !in_array($comm2->email, $this->emails) )
            {
               $this->emails[] = $comm2->email;
            }
         }
      }
      else
         $this->new_error_msg('Página no encontrada.');
   }
   
   /**
    * Parte pública de la página.
    */
   protected function public_core()
   {
      $this->template = 'public/item';
      $this->item = FALSE;
      $item = new comm3_item();
      $comment = new comm3_comment();
      $this->comment_text = '';
      $this->comment_email = '';
      $visit0 = new comm3_visitante();
      $this->visitante = FALSE;
      
      /**
       * Necesitamos un identificador para el visitante.
       * Así luego podemos relacionar sus comentarios y preguntas.
       */
      if( isset($_COOKIE['rid']) )
      {
         $this->rid = $_COOKIE['rid'];
         $this->visitante = $visit0->get_by_rid($this->rid);
         if($this->visitante)
         {
            $this->comment_email = $this->visitante->email;
         }
      }
      else
      {
         $this->rid = $this->random_string(30);
         setcookie('rid', $this->rid, time()+FS_COOKIES_EXPIRE, '/');
      }
      
      if( isset($_REQUEST['id']) )
      {
         $this->item = $item->get($_REQUEST['id']);
      }
      else if( isset($_REQUEST['title']) )
      {
         $this->item = $item->get_by_url_title($_REQUEST['title']);
      }
      else if( isset($_REQUEST['tag']) )
      {
         $this->item = $item->get_by_tag($_REQUEST['tag']);
      }
      
      if($this->item)
      {
         $this->page_title = $this->title($this->item->texto);
         $this->page_description = $this->title($this->item->texto, 200);
         $this->page_keywords = 'facturascripts, eneboo, abanq, woocommerce, prestashop, facturae';
         
         if( isset($_POST['comentario']) )
         {
            $this->comment_text = $_POST['comentario'];
            
            if( isset($_POST['email']) )
            {
               $this->comment_email = $_POST['email'];
            }
            
            if($this->comment_email == '')
            {
               $this->new_error_msg('Debes escribir tu email, es obligatiorio.');
            }
            else if( !filter_var($this->comment_email, FILTER_VALIDATE_EMAIL) )
            {
               $this->new_error_msg('Email no válido. Revísalo.');
            }
            else if( $this->email_bloqueado($this->comment_email) )
            {
               $this->new_error_msg('Este email está asignado a un usuario, para poder'
                       . ' usarlo debes iniciar sesión en la sección colabora.');
            }
            else if($_POST['comment_human'] == '')
            {
               /// necesitamos un visitante para guardar algo
               if( !$this->visitante )
               {
                  $this->visitante = new comm3_visitante();
                  $this->visitante->rid = $this->rid;
                  $this->visitante->email = $this->comment_email;
               }
               
               $comment->iditem = $this->item->id;
               $comment->texto = $_POST['comentario'];
               $comment->rid = $this->rid;
               $comment->email = $this->visitante->email;
               
               if( isset($_SERVER['REMOTE_ADDR']) )
               {
                  $this->visitante->last_ip = $_SERVER['REMOTE_ADDR'];
                  $comment->ip = $_SERVER['REMOTE_ADDR'];
               }
               
               if( isset($_SERVER['HTTP_USER_AGENT']) )
               {
                  $this->visitante->last_browser = $_SERVER['HTTP_USER_AGENT'];
               }
               
               if( $this->visitante->save() )
               {
                  if( $comment->save() )
                  {
                     $this->item->actualizado = time();
                     $this->item->num_comentarios++;
                     $this->item->ultimo_comentario = $comment->email();
                     if( $this->item->save() )
                     {
                        $this->new_message('Datos guardados correctamente.');
                        $this->comment_text = '';
                     }
                     else
                        $this->new_error_msg('Error al guardar los datos 3.');
                  }
                  else
                     $this->new_error_msg('Error al guardar los datos 2.');
               }
               else
                  $this->new_error_msg('Error al guardar los datos.');
            }
            else
               $this->new_error_msg('Debes borrar el número para demostrar que eres humano.');
         }
         
         $this->comments = $comment->get_by_iditem($this->item->id);
      }
      else
         $this->new_error_msg('Página no encontrada.');
   }
   
   private function email_bloqueado($email)
   {
      $visit0 = new comm3_visitante();
      $visitante = $visit0->get($email);
      if($visitante)
      {
         return !is_null($visitante->nick);
      }
      else
         return FALSE;
   }
   
   public function bbcode2html($v)
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
          "/\[youtube\](.*?)\[\/youtube\]/is"
      );
      $b = array(
          "<i>$1</i>",
          "<b>$1</b>",
          "<u>$1</u>",
          "<h2 style='margin: 0px 0px 5px 0px;'>$1</h2>",
          "<small>$1</small>",
          "<pre>$1</pre>",
          "<a href=\"$1\" target=\"_blank\" class=\"thumbnail\"><img src=\"$1\" alt=\"image\"/></a>",
          "<a href=\"$1\">$1</a>",
          "<a href=\"$1\">$2</a>",
          "<div><iframe width=\"640\" height=\"360\" src=\"//www.youtube.com/embed/$1\"".
             " allowfullscreen></iframe></div>"
      );
      
      $html = nl2br( preg_replace($a, $b, str_replace('&#8203;', '', $v) ) );
      
      /// eliminamos los <br /> dentro de los <pre></pre>
      if( strstr($html, '<pre>') )
      {
         $html2 = '';
         $code = FALSE;
         for($i=0; $i<mb_strlen($html); $i++)
         {
            if($code)
            {
               if( substr($html, $i, 6) == '<br />' )
               {
                  $html2 .= '';
                  $i += 5;
               }
               else if( substr($html, $i, 6) == '</pre>' )
               {
                  $code = FALSE;
                  $html2 .= '</pre>';
                  $i += 5;
               }
               else
                  $html2 .= substr($html, $i, 1);
            }
            else if( substr($html, $i, 5) == '<pre>' )
            {
               $code = TRUE;
               $html2 .= '<pre>';
               $i += 4;
            }
            else
               $html2 .= substr($html, $i, 1);
         }
         
         return $html2;
      }
      else
         return $html;
   }
   
   /// dado un texto con bbcode devuelve el mismo texto sin las etiquetas bbcode
   public function nobbcode($t)
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
          "/\[youtube\](.*?)\[\/youtube\]/is"
      );
      $b = array(
          " $1 ",
          " $1 ",
          " $1 ",
          " $1 ",
          " $1 ",
          " $1 ",
          " $1 ",
          " $1 ",
          " $2 ",
          " http://www.youtube.com/$1 "
      );
      return preg_replace($a, $b, $t);
   }
   
   public function title($texto, $len=60)
   {
      $title = str_replace("\n", ' ', $this->nobbcode($texto) );
      
      if( strlen($title) > $len )
      {
         return substr($title, 0, $len);
      }
      else
         return $title;
   }
   
   public function item_tags()
   {
      $tag_list = array();
      
      foreach( explode(',', $this->item->tags) as $tag )
      {
         $tag_list[] = str_replace( array('[',']') , array('',''), $tag);
      }
      
      return $tag_list;
   }
   
   private function enviar_email($email)
   {
      if( $this->empresa->can_send_mail() )
      {
         /// obtenemos la configuración extra del email
         $mailop = array(
             'mail_host' => 'smtp.gmail.com',
             'mail_port' => '465',
             'mail_user' => '',
             'mail_enc' => 'ssl'
         );
         $fsvar = new fs_var();
         $mailop = $fsvar->array_get($mailop, FALSE);
         
         $mail = new PHPMailer();
         $mail->IsSMTP();
         $mail->SMTPAuth = TRUE;
         $mail->SMTPSecure = $mailop['mail_enc'];
         $mail->Host = $mailop['mail_host'];
         $mail->Port = intval($mailop['mail_port']);
         
         $mail->Username = $this->empresa->email;
         if($mailop['mail_user'] != '')
         {
            $mail->Username = $mailop['mail_user'];
         }
         
         $mail->Password = $this->empresa->email_password;
         $mail->From = $this->empresa->email;
         $mail->FromName = $this->user->nick;
         $mail->CharSet = 'UTF-8';
         
         $mail->Subject = 'Hola, '.$this->user->nick." ha contestado a tu ".$this->item->tipo();
         $mail->AltBody = "Hola,\n\nTu ".$this->item->tipo().' ha sido contestada por '.
                 $this->user->nick.". Puedes ver la respuesta aquí: https://www.facturascripts.com/comm3/".
                 $this->item->url()."\n\nAtentamente, el cron de FacturaScripts.";
         $mail->WordWrap = 50;
         $mail->MsgHTML( nl2br($mail->AltBody) );
         $mail->AddAddress($email);
         $mail->IsHTML(TRUE);
         
         if( $mail->Send() )
         {
            $this->new_message('Mensaje enviado correctamente.');
         }
         else
            $this->new_error_msg("Error al enviar el email: " . $mail->ErrorInfo);
      }
   }
}
