<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function comm3_path()
{
   if( defined('COMM3_PATH') )
   {
      return COMM3_PATH;
   }
   else
      return '';
}

function comm3_get_perfil_user($user)
{
   require_model('comm3_visitante.php');
   
   $perfil = 'desconocido';
   if($user->admin)
   {
      $perfil = 'admin';
   }
   else
   {
      $visitante = new comm3_visitante();
      $v = $visitante->get_by_nick($user->nick);
      if($v)
      {
         $perfil = $v->perfil;
      }
   }
   
   return $perfil;
}