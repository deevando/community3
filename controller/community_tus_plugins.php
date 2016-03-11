<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015-2016, Carlos García Gómez. All Rights Reserved. 
 */

require_once 'extras/phpmailer/class.phpmailer.php';
require_once 'extras/phpmailer/class.smtp.php';
require_once __DIR__.'/community_home.php';
require_model('comm3_plugin_key.php');

/**
 * Description of community_tus_plugins
 *
 * @author carlos
 */
class community_tus_plugins extends community_home
{
   public $claves;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Tus claves de plugins', 'comunidad', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      parent::private_core();
      
      $this->share_extension();
      
      $plk0 = new comm3_plugin_key();
      $this->claves = $plk0->all_from_email($this->user->email);
      
      /// añadimos la clave para el plugin adminlte, si no está
      $encontrado = FALSE;
      foreach($this->claves as $clave)
      {
         if($clave->idplugin == 58)
         {
            $encontrado = TRUE;
            break;
         }
      }
      
      if(!$encontrado)
      {
         $plk = new comm3_plugin_key();
         $plk->email = $this->user->email;
         $plk->idplugin = 58;
         $plk->plugin = 'adminlte';
         if( $plk->save() )
         {
            $this->claves[] = $plk;
         }
      }
   }
   
   private function share_extension()
   {
      $fsext = new fs_extension();
      $fsext->name = 'tus_claves';
      $fsext->from = __CLASS__;
      $fsext->to = 'community_plugins';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-eye-open"></span>'
              . '<span class="hidden-xs">&nbsp; Tus claves</span>';
      $fsext->save();
   }
   
   protected function public_core()
   {
      parent::public_core();
      
      $this->page_title = 'FacturaScripts: Programa de facturacion gratis | Software contabilidad';
      $this->page_description = 'FacturaScripts es un programa de facturacion y contabilidad gratis'
              . ' para pymes con asesoramiento profesional. Descárgalo ahora, es software libre.';
      $this->page_keywords = 'programa de facturacion gratis, programas de contabilidad,'
              . ' programas de facturación y contabilidad, programa contabilidad gratis,'
              . ' programa facturacion gratuito, programa para hacer facturas,'
              . ' programa para hacer facturas gratis, programa facturacion autonomos,'
              . ' software contabilidad, programa contabilidad autonomos';
      $this->template = 'public/tus_plugins';
      
      if( isset($_GET['exit']) )
      {
         $this->rid = FALSE;
         $this->visitante = FALSE;
         setcookie('rid', $this->rid, time()+FS_COOKIES_EXPIRE, '/');
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
            $visit0 = new comm3_visitante();
            
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
               
               setcookie('rid', $this->visitante->rid, time()+FS_COOKIES_EXPIRE, '/');
            }
         }
      }
      
      if($this->visitante)
      {
         $plk0 = new comm3_plugin_key();
         $this->claves = $plk0->all_from_email($this->visitante->email);
         
         /// añadimos la clave para el plugin adminlte, si no está
         $encontrado = FALSE;
         foreach($this->claves as $clave)
         {
            if($clave->idplugin == 58)
            {
               $encontrado = TRUE;
               break;
            }
         }
         
         if(!$encontrado)
         {
            $plk = new comm3_plugin_key();
            $plk->email = $this->visitante->email;
            $plk->idplugin = 58;
            $plk->plugin = 'adminlte';
            if( $plk->save() )
            {
               $this->claves[] = $plk;
            }
         }
      }
   }
   
   private function comprobar_email($visitante)
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
         $mail->msgHTML( nl2br($mail->AltBody) );
         $mail->addAddress($visitante->email);
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
      }
      else
      {
         $this->new_error_msg('No se ha podido enviar el email.');
      }
   }
}
