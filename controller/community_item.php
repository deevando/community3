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

require_once 'extras/phpmailer/class.phpmailer.php';
require_once 'extras/phpmailer/class.smtp.php';
require_once 'plugins/community3/recaptcha/autoload.php';
require_once __DIR__.'/community_home.php';
require_model('comm3_comment.php');
require_model('comm3_relacion.php');
require_model('comm3_stat_item.php');

/**
 * Description of community_item
 *
 * @author carlos
 */
class community_item extends community_home
{
   public $allow_delete;
   public $comments;
   public $comment_text;
   public $comment_email;
   public $emails;
   public $info_ip;
   public $item;
   public $item_visitante;
   public $relaciones;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Item', 'comunidad', FALSE, FALSE);
   }
   
   /**
    * Parte privada de la página.
    */
   protected function private_core()
   {
      parent::private_core();
      
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
      
      if( isset($_REQUEST['buscar_iditem']) )
      {
         $this->buscar_iditem();
      }
      else if($this->item)
      {
         $this->page->title = $this->item->resumen(60);
         
         /// cargamos el visitante que ha creado este item
         $visit0 = new comm3_visitante();
         $this->item_visitante = $visit0->get($this->item->email);
         if($this->item_visitante)
         {
            if(!$this->allow_delete)
            {
               /// el autorizado puede eliminar
               $this->allow_delete = $this->item_visitante->autorizado($this->user->nick);
            }
         }
         
         /// cargamos el visitante asociado al usuario que está viendo este item
         $this->visitante = $visit0->get_by_nick($this->user->nick);
         if($this->visitante)
         {
            $this->visitante->last_login = time();
            $this->visitante->save();
         }
         
         $this->info_ip = array();
         $stat_item = new comm3_stat_item();
         foreach($stat_item->all_by_ip($this->item->ip) as $si)
         {
            $this->info_ip[] = 'Hay un informe de FacturaScripts '.$si->version
                    .' en esta IP el día '.$si->fecha.':<br/>'.$si->plugins;
         }
         
         if( isset($_POST['feedback_text']) )
         {
            $this->item->texto = $_POST['feedback_text'];
            $this->item->tags = $_POST['feedback_tags'];
            $this->item->tipo = $_POST['feedback_type'];
            $this->item->estado = $_POST['feedback_estado'];
            $this->item->privado = isset($_POST['feedback_privado']);
            $this->item->destacado = isset($_POST['feedback_destacado']);
            
            $this->item->asignados = NULL;
            if($_POST['asignados'] != '')
            {
               $this->item->asignados = '['.$_POST['asignados'].']';
            }
            
            $this->item->prioridad = intval($_POST['prioridad']);
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
            $this->comment_text = trim($_POST['comentario']);
            
            $comment->iditem = $this->item->id;
            $comment->texto = $this->comment_text;
            $comment->nick = $this->user->nick;
            $comment->ip = $this->user->last_ip;
            $comment->privado = isset($_POST['privado']);
            
            if($this->visitante)
            {
               $comment->email = $this->visitante->email;
               $comment->perfil = $this->visitante->perfil;
               $comment->codpais = $this->visitante->codpais;
               
               if($this->user->admin)
               {
                  $comment->perfil = 'admin';
               }
            }
            
            if( $this->duplicated_petition($_POST['petid']) OR $comment->duplicated() )
            {
               $this->new_error_msg('Mensaje duplicado.');
            }
            else if( trim($this->comment_text) == '' )
            {
               $this->new_error_msg('No has escrito nada.');
            }
            else if( $comment->save() )
            {
               $this->item->actualizado = time();
               $this->item->num_comentarios++;
               $this->item->ultimo_comentario = $comment->email();
               
               if( $this->item->save() )
               {
                  $this->new_message('Datos guardados correctamente.');
                  $this->comment_text = '';
                  
                  if( isset($_POST['feedback_sendmail']) )
                  {
                     if($_POST['feedback_sendmail'] != '')
                     {
                        $this->enviar_email($_POST['feedback_sendmail']);
                     }
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
         else if( isset($_POST['iditem2']) )
         {
            $relacion = new comm3_relacion();
            $relacion->iditem1 = $this->item->id;
            $relacion->iditem2 = $_POST['iditem2'];
            if( $relacion->save() )
            {
               $this->new_message('Relación guardada correctamente.');
            }
            else
               $this->new_error_msg('Error al guardar la relación.');
         }
         else if( isset($_GET['deleter']) )
         {
            $rel0 = new comm3_relacion();
            $relacion = $rel0->get($_GET['deleter']);
            if($relacion)
            {
               if( $relacion->delete() )
               {
                  $this->new_message('Relación eliminada correctamente.');
               }
               else
                  $this->new_error_msg('Error al eliminar la relación.');
            }
            else
               $this->new_error_msg('Relación no encontrada.');
         }
         
         $this->comments = $comment->get_by_iditem($this->item->id);
         
         $tu_email = FALSE;
         if($this->visitante)
         {
            $tu_email = $this->visitante->email;
         }
         $this->emails = array();
         if( !is_null($this->item->email) AND $this->item->email != '' )
         {
            $this->emails[] = $this->item->email;
         }
         foreach($this->comments as $comm2)
         {
            if( !is_null($comm2->email) AND $comm2->email != '' AND $comm2->email != $tu_email AND !in_array($comm2->email, $this->emails) )
            {
               $this->emails[] = $comm2->email;
            }
         }
         
         $rel0 = new comm3_relacion();
         $this->relaciones = $rel0->all_for($this->item->id);
      }
      else
         $this->new_error_msg('Página no encontrada.');
   }
   
   /**
    * Parte pública de la página.
    */
   protected function public_core()
   {
      parent::public_core();
      
      $this->template = 'public/item';
      $this->item = FALSE;
      $item = new comm3_item();
      $this->comment_text = '';
      $this->comment_email = '';
      if($this->visitante)
      {
         $this->comment_email = $this->visitante->email;
      }
      
      if( isset($_REQUEST['id']) )
      {
         $this->item = $item->get($_REQUEST['id']);
         if( $this->item AND comm3_path() )
         {
            if($this->item->url_title)
            {
               header("Location: ".$this->item->url(TRUE), TRUE, 301);
            }
         }
      }
      else if( isset($_REQUEST['title']) )
      {
         $this->item = $item->get_by_url_title($_REQUEST['title']);
      }
      else if( isset($_REQUEST['tag']) )
      {
         $this->item = $item->get_by_tag($_REQUEST['tag']);
         if( $this->item AND comm3_path() )
         {
            if($this->item->url_title)
            {
               header("Location: ".$this->item->url(TRUE), TRUE, 301);
            }
         }
      }
      
      if($this->item)
      {
         $this->page_title = $this->item->resumen(60);
         $this->page_description = $this->item->resumen(200);
         $this->page_keywords = $this->find_keywords();
         
         if( isset($_POST['comentario']) )
         {
            $this->nuevo_comentario_publico();
         }
         
         $comment = new comm3_comment();
         $this->comments = $comment->get_by_iditem($this->item->id);
      }
      else
      {
         header("HTTP/1.0 404 Not Found");
         $this->new_error_msg('Página no encontrada.');
      }
   }
   
   private function nuevo_comentario_publico()
   {
      $fsvar = new fs_var();
      $recaptcha_key = $fsvar->simple_get('recaptcha');
      $recaptcha = new \ReCaptcha\ReCaptcha($recaptcha_key);
      $recaptcha_resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
      
      $comment = new comm3_comment();
      $this->comment_text = trim($_POST['comentario']);
      
      if( isset($_POST['email']) )
      {
         $this->comment_email = $_POST['email'];
      }
      
      if( $this->duplicated_petition($_POST['petid']) )
      {
         $this->new_error_msg('Mensaje duplicado.');
      }
      else if($this->comment_text == '')
      {
         $this->new_error_msg('No has escrito nada.');
      }
      else if($this->comment_email == '')
      {
         $this->new_error_msg('Debes escribir tu email, es obligatiorio.');
      }
      else if( !filter_var($this->comment_email, FILTER_VALIDATE_EMAIL) )
      {
         $this->new_error_msg('Email no válido. Revísalo.');
      }
      else if( $this->email_bloqueado($this->comment_email, $this->rid) )
      {
         $this->new_error_msg('Este email ya está asignado, debes usar un link para iniciar sesión.');
      }
      else if( $recaptcha_resp->isSuccess() )
      {
         /// necesitamos un visitante para guardar algo
         if( !$this->visitante )
         {
            $this->visitante = new comm3_visitante();
            $this->visitante->rid = $this->rid = $this->random_string(30);
            $this->visitante->email = $this->comment_email;
         }
         
         $comment->iditem = $this->item->id;
         $comment->texto = $_POST['comentario'];
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
            setcookie('rid', $this->rid, time()+FS_COOKIES_EXPIRE, '/');
            
            if( $comment->save() )
            {
               $this->item->actualizado = time();
               $this->item->num_comentarios++;
               $this->item->ultimo_comentario = $comment->email();
               if( $this->item->save() )
               {
                  $this->new_message('Comentario añadido correctamente.');
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
         $this->new_error_msg('Debes demostrar que no eres un robot.');
   }
   
   private function email_bloqueado($email, $rid)
   {
      $visit0 = new comm3_visitante();
      $visitante = $visit0->get($email);
      if($visitante)
      {
         if($visitante->rid == $rid)
         {
            return FALSE;
         }
         else if( $this->empresa->can_send_mail() )
         {
            $mail = new PHPMailer();
            $mail->CharSet = 'UTF-8';
            $mail->WordWrap = 50;
            $mail->isSMTP();
            $mail->SMTPAuth = TRUE;
            $mail->SMTPSecure = $this->empresa->email_config['mail_enc'];
            $mail->Host = $this->empresa->email_config['mail_host'];
            $mail->Port = intval($this->empresa->email_config['mail_port']);
            
            $mail->Username = $this->empresa->email;
            if($this->empresa->email_config['mail_user'] != '')
            {
               $mail->Username = $this->empresa->email_config['mail_user'];
            }
            
            $mail->Password = $this->empresa->email_config['mail_password'];
            $mail->From = $this->empresa->email;
            $mail->FromName = $this->empresa->nombre;
            
            $mail->Subject = 'Hola, tienes que iniciar sesión en facturascripts.com '.date('d-m-Y H:i');
            $mail->AltBody = "Hola,\n\nTú o alguien ha intentado usar este email en"
                    . " facturascripts.com sin haber iniciado sesión.\n";
            
            if( is_null($visitante->nick) )
            {
               $mail->AltBody .= 'Para iniciar sesión debes usar este enlace: '
                       .'https://www.facturascripts.com/comm3/index.php?page=community_colabora&auth1='
                       .base64_encode($visitante->email).'&auth2='.$visitante->rid;
            }
            else
            {
               $mail->AltBody .= 'Tu email está vinculado al usuario '.$visitante->nick.
                    ' y por tanto debes iniciar sesión desde la sección Colabora: '
                       . 'https://www.facturascripts.com/comm3/index.php?page=community_colabora';
            }
            
            $mail->AltBody .= "\n\nAtentamente, el cron de FacturaScripts.";
            $mail->msgHTML( nl2br($mail->AltBody) );
            $mail->addAddress($email);
            $mail->isHTML(TRUE);
            
            if($this->empresa->email_config['mail_bcc'])
            {
               $mail->addBCC($this->empresa->email_config['mail_bcc']);
            }
            
            $SMTPOptions = array();
            if($this->empresa->email_config['mail_low_security'])
            {
               $SMTPOptions = array(
                   'ssl' => array(
                       'verify_peer' => false,
                       'verify_peer_name' => false,
                       'allow_self_signed' => true
                   )
               );
            }
            
            if( $mail->smtpConnect($SMTPOptions) )
            {
               if( $mail->send() )
               {
                  $this->new_message('Se te ha enviado un email con instrucciones.');
               }
               else
                  $this->new_error_msg("Error al enviar el email: " . $mail->ErrorInfo);
            }
            else
               $this->new_error_msg("Error al enviar el email: " . $mail->ErrorInfo);
            
            return TRUE;
         }
         else
         {
            $this->new_error_msg('No se ha podido enviar el email.');
            return TRUE;
         }
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
          "<div class='embed-responsive embed-responsive-16by9'><iframe src=\"//www.youtube.com/embed/$1\"".
             " allowfullscreen></iframe></div>      "
      );
      
      $v = str_replace('&#8203;', '', $v);
      $html = nl2br( preg_replace($a, $b, $v) );
      
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
         $mail = new PHPMailer();
         $mail->CharSet = 'UTF-8';
         $mail->WordWrap = 50;
         $mail->isSMTP();
         $mail->SMTPAuth = TRUE;
         $mail->SMTPSecure = $this->empresa->email_config['mail_enc'];
         $mail->Host = $this->empresa->email_config['mail_host'];
         $mail->Port = intval($this->empresa->email_config['mail_port']);
         
         $mail->Username = $this->empresa->email;
         if($this->empresa->email_config['mail_user'] != '')
         {
            $mail->Username = $this->empresa->email_config['mail_user'];
         }
         
         $mail->Password = $this->empresa->email_config['mail_password'];
         $mail->From = $this->empresa->email;
         $mail->FromName = $this->empresa->nombre;
            
         $mail->Subject = 'Hola, '.$this->user->nick." ha contestado a tu ".$this->item->tipo();
         $mail->AltBody = "Hola,\n\nTu ".$this->item->tipo().' ha sido contestada por '.
                 $this->user->nick.". Puedes ver la respuesta aquí: https://www.facturascripts.com/comm3/".
                 $this->item->url()."\n\n";
         
         $visit0 = new comm3_visitante();
         $visitante = $visit0->get($email);
         if($visitante)
         {
            if( is_null($visitante->nick) )
            {
               $mail->AltBody .= 'Para iniciar sesión debes usar este enlace: '
                       .'https://www.facturascripts.com/comm3/index.php?page=community_colabora&auth1='
                       .base64_encode($visitante->email).'&auth2='.$visitante->rid;
            }
            else
            {
               $mail->AltBody .= 'Tu email está vinculado al usuario '.$visitante->nick.
                    ' y por tanto debes iniciar sesión desde la sección Colabora: '
                       . 'https://www.facturascripts.com/comm3/index.php?page=community_colabora';
            }
         }
         
         $mail->AltBody .= "\n\nAtentamente, el cron de FacturaScripts.";
         $mail->msgHTML( nl2br($mail->AltBody) );
         $mail->addAddress($email);
         $mail->isHTML(TRUE);
         
         $SMTPOptions = array();
         if($this->empresa->email_config['mail_low_security'])
         {
            $SMTPOptions = array(
                   'ssl' => array(
                       'verify_peer' => false,
                       'verify_peer_name' => false,
                       'allow_self_signed' => true
                   )
            );
         }
         
         if( $mail->smtpConnect($SMTPOptions) )
         {
            if( $mail->send() )
            {
               $this->new_message('Mensaje enviado correctamente.');
            }
            else
               $this->new_error_msg("Error al enviar el email: " . $mail->ErrorInfo);
         }
         else
            $this->new_error_msg("Error al enviar el email: " . $mail->ErrorInfo);
      }
   }
   
   private function buscar_iditem()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;
      
      $json = array();
      $item0 = new comm3_item();
      foreach($item0->search($_REQUEST['buscar_iditem']) as $item)
      {
         $json[] = array('value' => $item->resumen(), 'data' => $item->id);
      }
      
      header('Content-Type: application/json');
      echo json_encode( array('query' => $_REQUEST['buscar_iditem'], 'suggestions' => $json) );
   }
   
   private function find_keywords()
   {
      $keys = '';
      
      $avaliable = array(
          'eneboo', 'abanq', 'facturaplus', 'factusol', 'programa de facturación gratis',
          'programa de contabilidad', 'programas de facturación y contabilidad',
          'prestashop', 'woocommerce', 'facturae', 'crm', 'hostinger', 'sat'
      );
      
      foreach($avaliable as $av)
      {
         if( strpos( strtolower($this->item->texto), $av) !== FALSE )
         {
            if($keys == '')
            {
               $keys = $av;
            }
            else
               $keys .= ', '.$av;
         }
      }
      
      return $keys;
   }
   
   public function usuarios_disponibles()
   {
      $disponibles = array();
      
      if($this->user->admin)
      {
         foreach($this->user->all() as $user)
         {
            $disponibles[] = $user->nick;
         }
      }
      else if($this->visitante)
      {
         if($this->visitante->nick)
         {
            $disponibles[] = $this->visitante->nick;
         }
         
         if($this->visitante->autorizado)
         {
            $disponibles[] = $this->visitante->autorizado;
         }
         
         if($this->visitante->autorizado2)
         {
            $disponibles[] = $this->visitante->autorizado2;
         }
         
         if($this->visitante->autorizado3)
         {
            $disponibles[] = $this->visitante->autorizado3;
         }
         
         if($this->visitante->autorizado4)
         {
            $disponibles[] = $this->visitante->autorizado4;
         }
         
         if($this->visitante->autorizado5)
         {
            $disponibles[] = $this->visitante->autorizado5;
         }
      }
      
      return $disponibles;
   }
   
   public function programadores_disponibles()
   {
      $lista = array();
      
      if($this->item_visitante)
      {
         $sql = "SELECT * FROM comm3_visitantes WHERE perfil = 'freelance'";
         
         if($this->item_visitante->codpais)
         {
            $sql .= " AND codpais = ".$this->item_visitante->var2str($this->item_visitante->codpais).";";
         }
         
         $data = $this->db->select($sql);
         if($data)
         {
            foreach($data as $d)
            {
               $lista[] = new comm3_visitante($d);
            }
         }
      }
      
      return $lista;
   }
}
