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

function comm3_path()
{
   if( defined('COMM3_PATH') )
   {
      return COMM3_PATH;
   }
   else
      return '';
}

function comm3_url($url)
{
   if( defined('COMM3_PATH') )
   {
      if($url == 'index.php')
      {
         return '/';
      }
      else if($url == 'index.php?page=community_plugins')
      {
         return '/plugins';
      }
      else if($url == 'index.php?page=community_download')
      {
         return '/descargar';
      }
      else if($url == 'index.php?page=community_errors')
      {
         return '/errores';
      }
      else if($url == 'index.php?page=community_all')
      {
         return '/foro';
      }
      else if($url == 'index.php?page=community_ideas')
      {
         return '/ideas';
      }
      else if($url == 'index.php?page=community_changelog')
      {
         return '/noticias';
      }
      else if($url == 'index.php?page=community_questions')
      {
         return '/preguntas';
      }
      else
         return COMM3_PATH.$url;
   }
   else
      return $url;
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

function comm3_get_email_user($user)
{
   require_model('comm3_visitante.php');
   
   $email = NULL;
   $visitante = new comm3_visitante();
   $v = $visitante->get_by_nick($user->nick);
   if($v)
   {
      $email = $v->email;
   }
   
   return $email;
}