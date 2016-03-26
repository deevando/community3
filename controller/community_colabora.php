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

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_colabora extends community_home
{
   public $num_parati;
   public $parati;
   public $resultados;
   public $tareas_parati;
   public $tuyo;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Inicio', 'comunidad');
   }
   
   protected function private_core()
   {
      parent::private_core();
      
      if(FS_HOMEPAGE != 'community_home')
      {
         $this->new_advice('Tienes que seleccionar <b>community_home</b> como portada en'
                 . ' <a href="index.php?page=admin_home#avanzado">Admin &gt; Panel de control &gt; Avanzado</a>.');
      }
      
      if( isset($_GET['delete']) )
      {
         $item = new comm3_item();
         $item2 = $item->get($_GET['delete']);
         if($item2)
         {
            if( $item2->delete() )
            {
               $this->new_message('Página eliminada correctamente.');
            }
            else
            {
               $this->new_error_msg('Error al eliminar la página.');
            }
         }
         else
         {
            $this->new_error_msg('Página no encontrada.');
         }
      }
      
      $this->privados2admin();
      
      $this->get_parati();
      $this->get_tuyo();
      $this->get_tareas();
      $this->get_tareas_parati();
   }
   
   protected function public_core()
   {
      parent::public_core();
      
      $this->page_title = 'Colabora &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Colabora en el desarrollo de FacturaScripts, forma parte de la comunidad.';
      $this->page_keywords = 'colaborar FacturaScripts, trabajar con FacturaScripts, mejorar FacturaScripts';
      $this->template = 'public/colabora';
      
      if( isset($_GET['exit']) )
      {
         $this->rid = $this->visitante = FALSE;
         setcookie('rid', $this->rid, time()-FS_COOKIES_EXPIRE, '/');
      }
      
      if( isset($_POST['email']) )
      {
         $fsvar = new fs_var();
         $recaptcha_key = $fsvar->simple_get('recaptcha');
         $recaptcha = new \ReCaptcha\ReCaptcha($recaptcha_key);
         $recaptcha_resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
         
         if( $recaptcha_resp->isSuccess() )
         {
            if( !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) )
            {
               $this->new_error_msg('Email no válido.');
            }
            else if( $this->email_bloqueado($_POST['email']) )
            {
               $this->new_error_msg('Este email ya está asignado, debes usar un link para iniciar sesión.');
            }
            else
            {
               $this->visitante = new comm3_visitante();
               $this->visitante->rid = $this->rid = $this->random_string(30);
               $this->visitante->email = $_POST['email'];
               
               if($_POST['perfil'] != 'partner')
               {
                  $this->visitante->perfil = $_POST['perfil'];
               }
               
               if( isset($_SERVER['REMOTE_ADDR']) )
               {
                  $this->visitante->last_ip = $_SERVER['REMOTE_ADDR'];
               }
               
               if( isset($_SERVER['HTTP_USER_AGENT']) )
               {
                  $this->visitante->last_browser = $_SERVER['HTTP_USER_AGENT'];
               }
               
               if( $this->visitante->save() )
               {
                  setcookie('rid', $this->rid, time()+FS_COOKIES_EXPIRE, '/');
                  $this->new_message('Datos guardados correctamente. Ya eres un '
                          . 'colaborador con el perfil <b>'.$_POST['perfil'].'</b>.'
                          . ' ¿Quieres <a href="index.php?page=community_feedback"><b>'
                          . 'enviar algún mensaje</b></a>?');
               }
               else
                  $this->new_error_msg('Error al guardar los datos.');
            }
         }
         else
         {
            $this->new_error_msg('Tienes que marcar que no eres un robot.');
         }
      }
      else if( isset($_POST['perfil']) )
      {
         if($this->visitante)
         {
            $this->visitante->perfil = 'voluntario';
            if($_POST['perfil'] != 'partner' AND $_POST['perfil'] != '---')
            {
               $this->visitante->perfil = $_POST['perfil'];
            }
            
            if( $this->visitante->save() )
            {
               $this->new_message('Datos guardados correctamente.');
            }
            else
               $this->new_error_msg('Error al guardar los datos.');
         }
      }
      else if( isset($_GET['auth1']) AND isset($_GET['auth2']) )
      {
         $this->check_autorizacion();
      }
      
      $this->get_tuyo();
      $this->get_tareas();
   }
   
   public function perfiles($publicos = FALSE)
   {
      if($publicos)
      {
         return array(
             'voluntario' => 'Voluntario',
             'programador' => 'Programador',
             'freelance' => 'Freelance',
             '---' => '---',
             'nomolestar' => 'No molestar'
         );
      }
      else
      {
         return array(
             'voluntario' => 'Voluntario',
             'programador' => 'Programador',
             'freelance' => 'Freelance',
             '---' => '---',
             'nomolestar' => 'No molestar',
             '---' => '---',
             'premium' => 'Premium',
             'partner' => 'Partner',
             'cliente' => 'Cliente de partner',
         );
      }
   }
   
   private function email_bloqueado($email)
   {
      $visit0 = new comm3_visitante();
      $visitante = $visit0->get($email);
      if($visitante)
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
         }
         else
         {
            $this->new_error_msg('No se ha podido enviar el email.');
         }
         
         return TRUE;
      }
      else
         return FALSE;
   }
   
   private function check_autorizacion()
   {
      $visit0 = new comm3_visitante();
      $visitante = $visit0->get( base64_decode($_GET['auth1']) );
      if($visitante)
      {
         if( is_null($visitante->nick) )
         {
            if($visitante->rid == $_GET['auth2'])
            {
               $this->rid = $visitante->rid = $this->random_string(30);
               if( $visitante->save() )
               {
                  setcookie('rid', $visitante->rid, time()+FS_COOKIES_EXPIRE, '/');
                  $this->visitante = $visitante;
                  $this->new_message('Sesión iniciada correctamente.');
               }
               else
                  $this->new_error_msg('Error al guardar los datos de sesión.');
            }
            else
               $this->new_error_msg('Datos incorrectos.');
         }
         else
            $this->new_error_msg('Debes iniciar sesión con el usuario <b>'.$visitante->nick.'</b>.');
      }
      else
         $this->new_error_msg('Visitante no encontrado.');
   }
   
   private function get_tareas()
   {
      $this->resultados = array();
      $sql = "SELECT * FROM comm3_items WHERE tipo = 'task' AND (estado != 'cerrado'"
              . " OR estado is NULL) AND privado = false ORDER BY prioridad DESC;";
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $this->resultados[] = new comm3_item($d);
         }
      }
      
      return $this->resultados;
   }
   
   private function get_parati()
   {
      $this->parati = array();
      $sql = "SELECT * FROM comm3_items WHERE tipo != 'task' AND (estado != 'cerrado' OR estado is NULL)".
              " AND (asignados = '[".$this->user->nick."]' OR email IN".
              " (SELECT email FROM comm3_visitantes WHERE autorizado = '".$this->user->nick.
              "' OR autorizado2 = '".$this->user->nick.
              "' OR autorizado3 = '".$this->user->nick.
              "' OR autorizado4 = '".$this->user->nick.
              "' OR autorizado5 = '".$this->user->nick.
              "')) AND (ultimo_comentario IS NULL OR ultimo_comentario != '".$this->user->nick."')".
              " ORDER BY destacado DESC, prioridad DESC, actualizado DESC;";
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $this->parati[] = new comm3_item($d);
         }
      }
      
      $this->num_parati = count($this->parati);
      return $this->parati;
   }
   
   private function get_tuyo()
   {
      $this->tuyo = array();
      
      $sql = FALSE;
      if( $this->user->exists() )
      {
         $sql = "SELECT * FROM comm3_items WHERE nick = ".$this->user->var2str($this->user->nick);
         if($this->user->email)
         {
            $sql .= " OR email = ".  $this->user->var2str($this->user->email);
         }
         $sql .= " ORDER BY actualizado DESC;";
      }
      else if($this->visitante)
      {
         $sql = "SELECT * FROM comm3_items WHERE email = ".$this->user->var2str($this->visitante->email)
                 ." ORDER BY actualizado DESC;";
      }
      
      if($sql)
      {
         $data = $this->db->select($sql);
         if($data)
         {
            foreach($data as $d)
            {
               $this->tuyo[] = new comm3_item($d);
            }
         }
      }
      
      return $this->tuyo;
   }
   
   private function get_tareas_parati()
   {
      $this->tareas_parati = array();
      $sql = "SELECT * FROM comm3_items WHERE tipo = 'task' AND (estado != 'cerrado'"
              . " OR estado is NULL) AND asignados = '[".$this->user->nick."]'"
              . " ORDER BY prioridad DESC;";
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $this->tareas_parati[] = new comm3_item($d);
         }
      }
      
      return $this->tareas_parati;
   }
   
   /**
    * Asignamos a admin, o a quien corresponda los items sin asignar.
    */
   private function privados2admin()
   {
      $sql = "SELECT * FROM comm3_items WHERE asignados is null AND email NOT IN "
              . "(SELECT email FROM comm3_visitantes WHERE autorizado is not null);";
      
      $data = $this->db->select($sql);
      if($data)
      {
         $visitante = new comm3_visitante();
         
         foreach($data as $d)
         {
            $item = new comm3_item($d);
            
            if($item->privado)
            {
               /// si es privado se asigna a admin
               foreach($this->user->all() as $user)
               {
                  if($user->admin)
                  {
                     $item->asignados = '['.$user->nick.']';
                  }
               }
            }
            else
            {
               /// sino se asigna al primer partner del pais
               foreach($visitante->search_for_user(TRUE, FALSE, '', 'partner', $item->codpais) as $visit)
               {
                  if($visit->nick)
                  {
                     $item->asignados = '['.$visit->nick.']';
                     break;
                  }
               }
               
               /// sino se asigna al primer programador o voluntario del pais
               if( is_null($item->asignados) )
               {
                  foreach($visitante->search_for_user(TRUE, FALSE, '', '---', $item->codpais) as $visit)
                  {
                     if($visit->nick)
                     {
                        $item->asignados = '['.$visit->nick.']';
                        break;
                     }
                  }
               }
               
               /// sino, se asigna al admin
               if( is_null($item->asignados) )
               {
                  foreach($this->user->all() as $user)
                  {
                     if($user->admin)
                     {
                        $item->asignados = '['.$user->nick.']';
                     }
                  }
               }
            }
            
            $item->save();
         }
      }
   }
}
