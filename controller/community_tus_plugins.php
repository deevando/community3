<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
 */

require_once 'extras/phpmailer/class.phpmailer.php';
require_once 'extras/phpmailer/class.smtp.php';
require_model('comm3_plugin_key.php');
require_model('comm3_visitante.php');

/**
 * Description of community_tus_plugins
 *
 * @author carlos
 */
class community_tus_plugins extends fs_controller
{
   public $claves;
   public $page_title;
   public $page_description;
   public $page_keywords;
   public $visitante;
   
   private $rid;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Tus claves de plugins', 'community', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $plk0 = new comm3_plugin_key();
      $this->claves = $plk0->all_from_email($this->user->email);
   }
   
   protected function public_core()
   {
      $this->page_title = 'FacturaScripts: Programa de facturacion gratis | Software contabilidad';
      $this->page_description = 'FacturaScripts es un programa de facturacion y contabilidad gratis'
              . ' para pymes con asesoramiento profesional. Descárgalo ahora, es software libre.';
      $this->page_keywords = 'programa de facturacion gratis, programas de contabilidad,'
              . ' programas de facturación y contabilidad, programa contabilidad gratis,'
              . ' programa facturacion gratuito, programa para hacer facturas,'
              . ' programa para hacer facturas gratis, programa facturacion autonomos,'
              . ' software contabilidad, programa contabilidad autonomos';
      $this->template = 'public/tus_plugins';
      
      $visit0 = new comm3_visitante();
      $this->visitante = FALSE;
      
      $this->rid = FALSE;
      if( isset($_GET['exit']) )
      {
         setcookie('rid', $this->rid, time()+FS_COOKIES_EXPIRE, '/');
      }
      else if( isset($_COOKIE['rid']) )
      {
         $this->rid = $_COOKIE['rid'];
         $this->visitante = $visit0->get_by_rid($this->rid);
      }
      
      if( isset($_POST['email']) )
      {
         if( !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) )
         {
            $this->new_error_msg('Email no válido.');
         }
         else
         {
            /// ¿Existe el visitante?
            $visit2 = $visit0->get($_POST['email']);
            if($visit2)
            {
               /// el visitante existe
               $this->comprobar_email($visit2);
            }
            else
            {
               $this->visitante = new comm3_visitante();
               $this->visitante->rid = $this->random_string(30);
               $this->visitante->email = $_POST['email'];
               $this->visitante->save();
            }
         }
      }
      
      if($this->visitante)
      {
         $plk0 = new comm3_plugin_key();
         $this->claves = $plk0->all_from_email($this->visitante->email);
      }
   }
   
   private function comprobar_email($visitante)
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
         $mail->AddAddress($visitante->email);
         $mail->IsHTML(TRUE);
         
         if( $mail->Send() )
         {
            $this->new_message('Se te ha enviado un email con instrucciones.');
         }
         else
            $this->new_error_msg("Error al enviar el email: " . $mail->ErrorInfo);
      }
      else
      {
         $this->new_error_msg('No se ha podido enviar el email.');
      }
   }
}
