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
require_model('comm3_item.php');
require_model('comm3_visitante.php');

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_colabora extends fs_controller
{
   public $anuncio;
   public $num_parati;
   public $page_title;
   public $page_description;
   public $page_keywords;
   public $parati;
   public $perfil;
   public $resultados;
   public $rid;
   public $tareas_parati;
   public $tus_clientes;
   public $tuyo;
   public $visitante;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Inicio', 'comunidad');
   }
   
   protected function private_core()
   {
      if(FS_HOMEPAGE != 'community_home')
      {
         $this->new_advice('Tienes que seleccionar <b>community_home</b> como'
                 . ' portada en Admin > Panel de control > Avanzado.');
      }
      
      $fsvar = new fs_var();
      $this->anuncio = $fsvar->simple_get('comm3_anuncio');
      $this->perfil = comm3_get_perfil_user($this->user);
      
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
      $this->get_clientes();
      $this->get_tareas();
      $this->get_tareas_parati();
   }
   
   protected function public_core()
   {
      $this->page_title = 'Colabora &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Colabora en el desarrollo de FacturaScripts, forma parte de la comunidad.';
      $this->page_keywords = 'colaborar FacturaScripts, trabajar con FacturaScripts, mejorar FacturaScripts';
      $this->template = 'public/colabora';
      $visit0 = new comm3_visitante();
      $this->visitante = FALSE;
      
      $this->rid = $this->random_string(30);
      if( isset($_GET['exit']) )
      {
         setcookie('rid', $this->rid, time()+FS_COOKIES_EXPIRE, '/');
      }
      else if( isset($_COOKIE['rid']) )
      {
         $this->rid = $_COOKIE['rid'];
         $this->visitante = $visit0->get_by_rid($this->rid);
      }
      else
      {
         setcookie('rid', $this->rid, time()+FS_COOKIES_EXPIRE, '/');
      }
      
      if( isset($_POST['humanity']) )
      {
         if($_POST['humanity'] == '')
         {
            if( !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) )
            {
               $this->new_error_msg('Email no válido.');
            }
            else if($this->visitante)
            {
               $this->visitante->perfil = $_POST['perfil'];
               if( $this->visitante->save() )
               {
                  $this->new_message('Datos guardados correctamente. Ya eres un '
                          . 'colaborador con el perfil <b>'.$_POST['perfil'].'</b>.'
                          . ' ¿Quieres <a href="index.php?page=community_feedback"><b>'
                          . 'enviar algún mensaje</b></a>?');
               }
               else
                  $this->new_error_msg('Error al guardar los datos.');
            }
            else if( $this->email_bloqueado($_POST['email'], $this->rid) )
            {
               $this->new_error_msg('Este email está asignado a un usuario, para poder'
                    . ' usarlo debes iniciar sesión.');
            }
            else
            {
               $this->visitante = new comm3_visitante();
               $this->visitante->rid = $this->rid;
               $this->visitante->email = $_POST['email'];
               $this->visitante->perfil = $_POST['perfil'];
               
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
            $this->new_error_msg('Tienes que borrar el número para demostrar que eres humano.');
         }
      }
      else if( isset($_GET['auth1']) AND isset($_GET['auth2']) )
      {
         $this->check_autorizacion();
      }
      
      $this->get_tuyo();
      $this->get_tareas();
   }
   
   public function perfiles()
   {
      return array(
          'voluntario' => 'Voluntario',
          'programador' => 'Programador',
          'distribuidor' => 'Distribuidor',
          'sysadmin' => 'Sysadmin',
          'contable' => 'Contable',
          '---' => '---',
          'nomolestar' => 'No molestar',
          '---' => '---',
          'premium' => 'Premium',
          'partner' => 'Partner',
          'cliente' => 'Cliente de partner',
      );
   }
   
   private function email_bloqueado($email, $rid)
   {
      $visit0 = new comm3_visitante();
      $visitante = $visit0->get($email);
      if($visitante)
      {
         if( $visitante->rid == $rid AND is_null($visitante->nick) )
         {
            return FALSE;
         }
         else if( $this->empresa->can_send_mail() )
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
            $mail->FromName = $this->empresa->nombre;
            $mail->CharSet = 'UTF-8';
            
            $mail->Subject = 'Hola, tienes que iniciar sesión en facturascripts.com '.date('d-m-Y');
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
            
            $mail->WordWrap = 50;
            $mail->MsgHTML( nl2br($mail->AltBody) );
            $mail->AddAddress($email);
            $mail->IsHTML(TRUE);
         
            if( $mail->Send() )
            {
               $this->new_message('Se te ha enviado un email con instrucciones.');
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
               $this->rid = $visitante->rid;
               setcookie('rid', $this->rid, time()+FS_COOKIES_EXPIRE, '/');
               $this->visitante = $visitante;
               $this->new_message('Sesión iniciada correctamente.');
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
            $this->resultados[] = new comm3_item($d);
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
            $this->parati[] = new comm3_item($d);
      }
      
      $this->num_parati = count($this->parati);
      return $this->parati;
   }
   
   private function get_tuyo()
   {
      $this->tuyo = array();
      
      if( $this->user->exists() )
      {
         $email = comm3_get_email_user($this->user);
         $sql = "SELECT * FROM comm3_items WHERE nick = '".$this->user->nick."'";
         if($email)
         {
            $sql .= " OR email = '".$email."'";
         }
         $sql .= " ORDER BY actualizado DESC;";
      }
      else
      {
         $sql = "SELECT * FROM comm3_items WHERE rid = '".$this->rid."' ORDER BY actualizado DESC;";
      }
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
            $this->tuyo[] = new comm3_item($d);
      }
      
      return $this->tuyo;
   }
   
   private function get_clientes()
   {
      $this->tus_clientes = array();
      
      $sql = "SELECT * FROM comm3_visitantes WHERE autorizado = '".$this->user->nick.
              "' OR autorizado2 = '".$this->user->nick.
              "' OR autorizado3 = '".$this->user->nick.
              "' OR autorizado4 = '".$this->user->nick.
              "' OR autorizado5 = '".$this->user->nick.
              "' ORDER BY email DESC;";
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
            $this->tus_clientes[] = new comm3_visitante($d);
      }
      
      return $this->tus_clientes;
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
            $this->tareas_parati[] = new comm3_item($d);
      }
      
      return $this->tareas_parati;
   }
   
   private function privados2admin()
   {
      foreach($this->user->all() as $user)
      {
         if($user->admin)
         {
            $sql = "UPDATE comm3_items SET asignados = '[".$user->nick."]' WHERE asignados is null".
                 " AND email NOT IN (SELECT email FROM comm3_visitantes WHERE autorizado is not null);";
            $this->db->exec($sql);
            break;
         }
      }
   }
}
