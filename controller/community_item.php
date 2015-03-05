<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
   public $info_ip;
   public $item;
   public $relacionados;
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
      
      if($this->item)
      {
         $this->relacionados = array();
         if( !is_null($this->item->email) )
         {
            foreach( $item->all_by_email($this->item->email) as $it )
            {
               if($it->id != $this->item->id)
               {
                  $this->relacionados[] = $it;
               }
            }
         }
         
         $this->info_ip = array();
         foreach( $item->all_by_ip($this->item->ip) as $it )
         {
            if($it->id != $this->item->id AND $it->info != '')
            {
               $this->info_ip[] = $it->info;
            }
         }
         $stat_item = new comm3_stat_item();
         foreach($stat_item->all_by_ip($this->item->ip) as $si)
         {
            $this->info_ip[] = 'Hay un informe de FacturaScripts '.$si->version.' en esta IP el día '.$si->fecha;
         }
         
         if( isset($_POST['feedback_text']) )
         {
            $this->item->texto = $_POST['feedback_text'];
            $this->item->tipo = $_POST['feedback_type'];
            $this->item->estado = $_POST['feedback_estado'];
            $this->item->privado = isset($_POST['feedback_privado']);
            $this->item->destacado = isset($_POST['feedback_destacado']);
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
            $comment->ip = $this->user->last_ip;
            
            if( $comment->save() )
            {
               $this->item->actualizado = time();
               $this->item->num_comentarios++;
               if( $this->item->save() )
               {
                  $this->new_message('Datos guardados correctamente.');
                  $this->comment_text = '';
                  
                  if( isset($_POST['feedback_sendmail']) )
                  {
                     $this->enviar_email();
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
               if( $comm2->delete() )
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
         
         $this->comments = $comment->get_by_iditem($this->item->id);
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
       * Así luego podemos relacioner sus comentarios y preguntas.
       */
      $this->rid = $this->random_string(30);
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
      
      if($this->item)
      {
         $this->relacionados = array();
         if( !is_null($this->item->email) )
         {
            foreach( $item->all_by_email($this->item->email) as $it )
            {
               if($it->id != $this->item->id)
               {
                  $this->relacionados[] = $it;
               }
            }
         }
         
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
          "<div><iframe width=\"640\" height=\"360\" src=\"http://www.youtube.com/embed/$1\"".
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
   
   private function enviar_email()
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
         
         $mail->Subject = 'Hola '.$this->item->email().', '.$this->user->nick." ha contectado a tu ".$this->item->tipo();
         $mail->AltBody = 'Hola '.$this->item->email().",\n\nTu ".$this->item->tipo().' ha sido contestada por '.
                 $this->user->nick.". Puedes ver la respuesta aquí: https://www.facturascripts.com/comm3/".
                 $this->item->url()."\n\nAtentamente, el cron de FacturaScripts.";
         $mail->WordWrap = 50;
         $mail->MsgHTML( nl2br($mail->AltBody) );
         $mail->AddAddress($this->item->email);
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
