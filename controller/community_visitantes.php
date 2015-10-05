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
 * Description of community_visitors
 *
 * @author carlos
 */
class community_visitantes extends fs_controller
{
   public $autorizados;
   public $filtro_query;
   public $filtro_perfil;
   public $filtro_codpais;
   public $filtro_provincia;
   public $filtro_ciudad;
   public $filtro_orden;
   public $perfil;
   public $resultados;
   public $offset;
   public $visitante;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Usuarios', 'comunidad', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $visitante = new comm3_visitante();
      $this->perfil = comm3_get_perfil_user($this->user);
      $this->visitante = FALSE;
      
      $this->offset = 0;
      if( isset($_GET['offset']) )
      {
         $this->offset = intval($_GET['offset']);
      }
      
      $this->filtro_query = '';
      $this->filtro_perfil = '---';
      $this->filtro_codpais = '---';
      $this->filtro_provincia = '---';
      $this->filtro_ciudad = '---';
      $this->filtro_orden = 'last_login DESC';
      
      if( isset($_GET['nuevo_email']) )
      {
         /// nuevo visitante / cliente de partner
         
         if( filter_var($_GET['nuevo_email'], FILTER_VALIDATE_EMAIL) )
         {
            $visitante->email = $_GET['nuevo_email'];
            $visitante->rid = $this->random_string();
            $visitante->autorizado = $this->user->nick;
            $visitante->perfil = 'cliente';
            $visitante->privado = TRUE;
            
            if( $visitante->exists() )
            {
               $this->new_error_msg('El email ya está asignado.');
            }
            else if( $visitante->save() )
            {
               header( 'Location: '.$visitante->url() );
            }
            else
               $this->new_error_msg('Error al guardar los datos.');
         }
         else
            $this->new_error_msg('Email no válido.');
         
         $this->resultados = $visitante->search_for_user($this->user->admin, $this->user->nick);
      }
      else if( isset($_REQUEST['email']) OR isset($_REQUEST['nick']) )
      {
         if( isset($_REQUEST['email']) )
         {
            $this->visitante = $visitante->get($_REQUEST['email']);
         }
         else
         {
            $this->visitante = $visitante->get_by_nick($_REQUEST['nick']);
         }
         
         if($this->visitante)
         {
            $this->template = 'community_visitante';
            
            $this->autorizados = array();
            
            if( isset($_POST['perfil']) )
            {
               if($this->user->admin OR $this->visitante->autorizado($this->user->nick) )
               {
                  $this->visitante->perfil = $_POST['perfil'];
                  $this->visitante->privado = isset($_POST['privado']);
                  
                  $this->visitante->nick = NULL;
                  if($_POST['nick'] != '')
                  {
                     $this->visitante->nick = $_POST['nick'];
                  }
                  
                  $this->visitante->autorizado = NULL;
                  if($_POST['autorizado'] != '')
                  {
                     $this->visitante->autorizado = $_POST['autorizado'];
                  }
                  
                  $this->visitante->autorizado2 = NULL;
                  if($_POST['autorizado2'] != '')
                  {
                     $this->visitante->autorizado2 = $_POST['autorizado2'];
                  }
                  
                  $this->visitante->autorizado3 = NULL;
                  if($_POST['autorizado3'] != '')
                  {
                     $this->visitante->autorizado3 = $_POST['autorizado3'];
                  }
                  
                  $this->visitante->autorizado4 = NULL;
                  if($_POST['autorizado4'] != '')
                  {
                     $this->visitante->autorizado4 = $_POST['autorizado4'];
                  }
                  
                  $this->visitante->autorizado5 = NULL;
                  if($_POST['autorizado5'] != '')
                  {
                     $this->visitante->autorizado5 = $_POST['autorizado5'];
                  }
                  
                  if( $this->visitante->save() )
                  {
                     $this->new_message('Datos guardados correctamente.');
                  }
                  else
                     $this->new_error_msg('Error al guardar los datos.');
               }
               else
                  $this->new_error_msg('No estás autorizado.');
            }
            
            if( $this->user->admin OR $this->visitante->autorizado($this->user->nick) )
            {
               $item = new comm3_item();
               $this->resultados = $item->all_by_visitante($this->visitante, $this->offset);
               $this->autorizados = $this->visitante->search_for_user(FALSE, $this->visitante->nick);
            }
            else
            {
               $this->new_error_msg('No tienes permiso para ver estos datos.');
               $this->template = 'community_visitantes';
               $this->resultados = $visitante->search_for_user($this->user->admin, $this->user->nick);
            }
         }
         else
         {
            $this->new_error_msg('Visitante no encontrado.');
         }
      }
      else if( isset($_GET['delete']) )
      {
         $vis = $visitante->get($_GET['delete']);
         if($vis)
         {
            if(!$this->user->admin AND $vis->autorizado != $this->user->nick)
            {
               $this->new_error_msg('No tienes permiso para eliminar estos datos.');
            }
            else if( $vis->delete() )
            {
               $this->new_message('Visitante eliminado correctamente.');
            }
            else
               $this->new_error_msg('Error al eliminar el visitante.');
         }
         else
            $this->new_error_msg('Visitante no encontrado.');
         
         $this->resultados = $visitante->search_for_user($this->user->admin, $this->user->nick);
      }
      else if( isset($_POST['filtro_query']) )
      {
         $this->filtro_query = $_POST['filtro_query'];
         $this->filtro_perfil = $_POST['filtro_perfil'];
         $this->filtro_codpais = $_POST['filtro_codpais'];
         $this->filtro_provincia = $_POST['filtro_provincia'];
         $this->filtro_ciudad = $_POST['filtro_ciudad'];
         $this->filtro_orden = $_POST['filtro_orden'];
         
         $this->resultados = $visitante->search_for_user(
                 $this->user->admin,
                 $this->user->nick,
                 $this->filtro_query,
                 $this->filtro_perfil,
                 $this->filtro_codpais,
                 $this->filtro_provincia,
                 $this->filtro_ciudad,
                 $this->filtro_orden
         );
      }
      else
      {
         $this->resultados = $visitante->search_for_user($this->user->admin, $this->user->nick);
      }
   }
   
   protected function public_core()
   {
      header('Location: index.php?page=community_home');
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
   
   public function paises()
   {
      $paises = array();
      
      $data = $this->db->select("SELECT DISTINCT codpais FROM comm3_visitantes ORDER BY codpais ASC;");
      if($data)
      {
         foreach($data as $d)
         {
            if($d['codpais'] != '')
            {
               $paises[] = $d['codpais'];
            }
         }
      }
      
      return $paises;
   }
   
   public function provincias()
   {
      $provincias = array();
      
      $data = $this->db->select("SELECT DISTINCT provincia FROM comm3_visitantes ORDER BY provincia ASC;");
      if($data)
      {
         foreach($data as $d)
         {
            if($d['provincia'] != '')
            {
               $provincias[] = $d['provincia'];
            }
         }
      }
      
      return $provincias;
   }
   
   public function ciudades()
   {
      $ciudad = array();
      
      $data = $this->db->select("SELECT DISTINCT ciudad FROM comm3_visitantes ORDER BY ciudad ASC;");
      if($data)
      {
         foreach($data as $d)
         {
            if($d['ciudad'] != '')
            {
               $ciudad[] = $d['ciudad'];
            }
         }
      }
      
      return $ciudad;
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
}
