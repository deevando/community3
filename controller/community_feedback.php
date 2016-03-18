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

require_once 'extras/phpmailer/class.phpmailer.php';
require_once 'extras/phpmailer/class.smtp.php';
require_once 'plugins/community3/recaptcha/autoload.php';
require_once __DIR__.'/community_home.php';
require_model('comm3_plugin.php');
require_model('comm3_relacion.php');

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_feedback extends community_home
{
   public $feedback_email;
   public $feedback_type;
   public $feedback_text;
   public $feedback_iditem;
   public $feedback_info;
   public $feedback_contacto;
   public $feedback_plugin;
   public $feedback_privado;
   public $feedback_prioridad;
   public $plugins;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Escribir en la comunidad...', 'comunidad', FALSE, FALSE, TRUE);
   }
   
   protected function private_core()
   {
      parent::private_core();
      
      $this->feedback_type = 'question';
      $this->feedback_text = '';
      $this->feedback_iditem = '';
      $this->feedback_info = '';
      $this->feedback_privado = TRUE;
      $this->feedback_prioridad = 3;
      
      if($this->visitante)
      {
         /// modificamos la prioridad en función del perfil
         if($this->visitante->perfil == 'partner')
         {
            $this->feedback_prioridad = 5;
         }
      }
      
      if( isset($_POST['feedback_type']) )
      {
         $this->feedback_type = $_POST['feedback_type'];
         $this->feedback_text = trim($_POST['feedback_text']);
         $this->feedback_info = $_POST['feedback_info'];
         $this->feedback_privado = isset($_POST['feedback_privado']);
         
         $item = new comm3_item();
         $item->nick = $this->user->nick;
         $item->email = $this->user->email;
         
         if($this->visitante)
         {
            $item->perfil = $this->visitante->perfil;
         }
         
         /// ¿Se escribe en el nombre de un cliente?
         if( isset($_POST['autor']) )
         {
            $visit0 = new comm3_visitante();
            $cliente = $visit0->get($_POST['autor']);
            if($cliente)
            {
               $item->email = $cliente->email;
               $item->rid = $cliente->rid;
               $item->nick = $cliente->nick;
               $item->perfil = $cliente->perfil;
            }
         }
         
         $item->tipo = $this->feedback_type;
         $item->privado = $this->feedback_privado;
         $item->texto = $this->feedback_text;
         
         if($_POST['asignados'] != '')
         {
            $item->asignados = '['.$_POST['asignados'].']';
         }
         
         if( isset($_POST['prioridad']) )
         {
            $item->prioridad = intval($_POST['prioridad']);
         }
         
         $item->ip = $_SERVER['REMOTE_ADDR'];
         $item->info = $this->feedback_info;
         $item->info .= $_SERVER['HTTP_USER_AGENT'];
         
         if($item->texto == '')
         {
            $this->new_error_msg('No has escrito nada.');
         }
         else if( $item->save() )
         {
            if($_POST['feedback_iditem'] != '')
            {
               $rel = new comm3_relacion();
               $rel->iditem1 = $_POST['feedback_iditem'];
               $rel->iditem2 = $item->id;
               if( $rel->save() )
               {
                  $this->new_message('Datos guardados correctamente.');
                  header('Location: '.$item->url() );
               }
               else
                  $this->new_error_msg('Imposible guardar la relación.');
            }
            else
            {
               $this->new_message('Datos guardados correctamente.');
               header('Location: '.$item->url() );
            }
         }
         else
            $this->new_error_msg('Error al guardar los datos 2.');
      }
      else if( isset($_GET['feedback_type']) )
      {
         $this->feedback_type = $_GET['feedback_type'];
         $this->feedback_privado = isset($_GET['feedback_privado']);
         
         if($this->feedback_type == 'task')
         {
            $this->feedback_privado = TRUE;
         }
         
         if( isset($_GET['feedback_iditem']) )
         {
            $this->feedback_iditem = $_GET['feedback_iditem'];
         }
      }
   }
   
   protected function public_core()
   {
      parent::public_core();
      
      $this->page_title = 'Feedback &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Aporta feedback a la comunidad FacturaScripts.';
      
      $this->template = 'public/feedback';
      $this->feedback_email = '';
      $this->feedback_type = 'question';
      $this->feedback_text = '';
      $this->feedback_info = '';
      $this->feedback_privado = isset($_REQUEST['feedback_privado']);
      $this->feedback_contacto = FALSE;
      $this->feedback_plugin = '';
      
      if( isset($_REQUEST['feedback_contacto']) )
      {
         $this->feedback_contacto = TRUE;
         $this->template = 'public/feedback2';
      }
      
      $plugin0 = new comm3_plugin();
      $this->plugins = $plugin0->all();
      
      if($this->visitante)
      {
         $this->feedback_email = $this->visitante->email;
      }
      
      if( isset($_POST['feedback_type']) )
      {
         if( isset($_POST['feedback_email']) )
         {
            $this->feedback_email = $_POST['feedback_email'];
         }
         $this->feedback_type = $_POST['feedback_type'];
         $this->feedback_text = trim($_POST['feedback_text']);
         $this->feedback_info = $_POST['feedback_info'];
         
         if( isset($_POST['feedback_plugin']) )
         {
            $this->feedback_plugin = $_POST['feedback_plugin'];
         }
         
         $fsvar = new fs_var();
         $recaptcha_key = $fsvar->simple_get('recaptcha');
         $recaptcha = new \ReCaptcha\ReCaptcha($recaptcha_key);
         $recaptcha_resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
         
         if($this->feedback_text == '')
         {
            $this->new_error_msg('No has escrito nada.');
         }
         else if( mb_strlen($this->feedback_text) < 90 )
         {
            $this->new_error_msg('Has escrito muy poco. Detalla tu pregunta, error o sugerencia si quieres que la contestemos.');
         }
         else if($this->feedback_email == '')
         {
            $this->new_error_msg('Debes escribir tu email, es obligatiorio.');
         }
         else if( !filter_var($this->feedback_email, FILTER_VALIDATE_EMAIL) )
         {
            $this->new_error_msg('Email no válido. Revísalo.');
         }
         else if( !$recaptcha_resp->isSuccess() )
         {
            $this->new_error_msg('Debes marcar que no eres un robot.');
         }
         else if( $this->email_bloqueado($this->feedback_email, $this->rid) )
         {
            $this->new_error_msg('Este email ya está asignado a un usuario, debes usar un link iniciar sesión.');
         }
         else
         {
            /// necesitamos un visitante para guardar algo
            if( !$this->visitante )
            {
               $this->visitante = new comm3_visitante();
               $this->visitante->email = $this->feedback_email;
               $this->visitante->rid = $this->rid = $this->random_string(30);
            }
            
            $item = new comm3_item();
            $item->email = $this->visitante->email;
            $item->rid = $this->visitante->rid;
            $item->tipo = $this->feedback_type;
            $item->texto = $this->feedback_text;
            $item->info = $this->feedback_info;
            
            if($this->visitante->privado)
            {
               $item->privado = TRUE;
            }
            else
               $item->privado = $this->feedback_privado;
            
            if( isset($_SERVER['REMOTE_ADDR']) )
            {
               $this->visitante->last_ip = $_SERVER['REMOTE_ADDR'];
               $item->ip = $_SERVER['REMOTE_ADDR'];
            }
            
            if( isset($_SERVER['HTTP_USER_AGENT']) )
            {
               $this->visitante->last_browser = $_SERVER['HTTP_USER_AGENT'];
               $item->info .= $_SERVER['HTTP_USER_AGENT'];
            }
            
            if( $this->visitante->save() )
            {
               setcookie('rid', $this->visitante->rid, time()+FS_COOKIES_EXPIRE, '/');
               
               /// modificamos la prioridad en función del perfil
               $item->perfil = $this->visitante->perfil;
               if($item->perfil == 'premium' OR $item->perfil == 'cliente')
               {
                  $item->prioridad = 4;
               }
               else if(2*$this->visitante->compras > $this->visitante->interacciones)
               {
                  $item->prioridad = 2;
               }
               else if($this->visitante->interacciones < 5)
               {
                  $item->prioridad = 1;
               }
               
               /// asignamos el item al usuario del plugin
               if($this->feedback_plugin != '')
               {
                  $plugin = $plugin0->get($this->feedback_plugin);
                  if($plugin)
                  {
                     $item->asignados = '['.$plugin->nick.']';
                  }
               }
               
               if( $item->save() )
               {
                  $this->new_message('Datos guardados correctamente.');
                  header('Location: '.$item->url() );
               }
               else
                  $this->new_error_msg('Error al guardar los datos 2.');
            }
            else
               $this->new_error_msg('Error al guardar los datos.');
         }
      }
      else if( isset($_GET['feedback_type']) )
      {
         $this->feedback_type = $_GET['feedback_type'];
      }
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
            
            $mail->Subject = 'Hola, tienes que iniciar sesión en facturascripts.com '.date('d-m-Y');
            $mail->AltBody = "Hola,\n\nTú o alguien ha intentado usar este email en"
                    . " facturascripts.com sin haber iniciado sesión.\n";
            
            if( is_null($visitante->nick) )
            {
               $mail->AltBody .= 'Para iniciar sesión debes usar este enlace: '
                       .'https://www.facturascripts.com/index.php?page=community_colabora&auth1='
                       .base64_encode($visitante->email).'&auth2='.$visitante->rid;
            }
            else
            {
               $mail->AltBody .= 'Tu email está vinculado al usuario '.$visitante->nick.
                    ' y por tanto debes iniciar sesión desde la sección Colabora: '
                       . 'https://www.facturascripts.com/index.php?page=community_colabora';
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
   
   public function clientes()
   {
      return $this->visitante->search_for_user(FALSE, $this->visitante->nick);
   }
}
