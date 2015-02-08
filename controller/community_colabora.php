<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_model('comm3_visitante.php');

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_colabora extends fs_controller
{
   public $resultados;
   private $rid;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Visitantes', 'comunidad', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      $visitante = new comm3_visitante();
      
      $this->resultados = $visitante->all();
   }
   
   protected function public_core()
   {
      $this->template = 'public/colabora';
      $visit0 = new comm3_visitante();
      
      $this->rid = $this->random_string(30);
      if( isset($_COOKIE['rid']) )
      {
         $this->rid = $_COOKIE['rid'];
      }
      else
      {
         setcookie('rid', $this->rid, time()+FS_COOKIES_EXPIRE, '/');
      }
      
      if( isset($_POST['humanity']) )
      {
         if($_POST['humanity'] == '')
         {
            $visitante = $visit0->get($_POST['email']);
            if($visitante)
            {
               $this->new_error_msg('Este email ya ha sido registrado.');
            }
            else
            {
               $visitante = new comm3_visitante();
               $visitante->email = $_POST['email'];
               $visitante->perfil = $_POST['perfil'];
               
               if( isset($_SERVER['REMOTE_ADDR']) )
               {
                  $visitante->last_ip = $_SERVER['REMOTE_ADDR'];
               }
               
               if( isset($_SERVER['HTTP_USER_AGENT']) )
               {
                  $visitante->last_browser = $_SERVER['HTTP_USER_AGENT'];
               }
               
               if( $visitante->save() )
               {
                  $this->new_message('Datos guardados correctamente.');
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
   }
}
