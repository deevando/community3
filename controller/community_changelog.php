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

require_model('comm3_item.php');

/**
 * Description of community_changelog
 *
 * @author carlos
 */
class community_changelog extends fs_controller
{
   public $page_title;
   public $page_description;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Changelog', 'comunidad', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      if( isset($_REQUEST['version']) )
      {
         $item0 = new comm3_item();
         
         $item = FALSE;
         if( isset($_REQUEST['plugin']) )
         {
            $item = $item0->get_by_tag($_REQUEST['plugin'].'_'.$_REQUEST['version']);
         }
         else
         {
            $item = $item0->get_by_tag('FS'.$_REQUEST['version']);
         }
         
         if($item)
         {
            header('Location: '.$item->url());
         }
         else
         {
            $item0->tipo = 'changelog';
            $item0->nick = $this->user->nick;
            $item0->ip = $this->user->last_ip;
            
            if( isset($_REQUEST['plugin']) )
            {
               $item0->texto = 'Novedades del plugin [b]'.$_REQUEST['plugin'].'[/b], versión [b]'.$_REQUEST['version'].'[/b]:';
               $item0->tags = '['.$_REQUEST['plugin'].'_'.$_REQUEST['version'].'],['.$_REQUEST['plugin'].']';
            }
            else
            {
               $item0->texto = 'Novedades de [b]FacturaScripts '.$_REQUEST['version'].'[/b]:';
               $item0->tags = '[FS'.$_REQUEST['version'].'],[FacturaScripts]';
            }
            
            $item0->save();
            header('Location: '.$item0->url());
         }
      }
   }
   
   protected function public_core()
   {
      if( isset($_REQUEST['version']) )
      {
         $item0 = new comm3_item();
         
         $item = FALSE;
         if( isset($_REQUEST['plugin']) )
         {
            $item = $item0->get_by_tag($_REQUEST['plugin'].'_'.$_REQUEST['version']);
         }
         else
         {
            $item = $item0->get_by_tag('FS'.$_REQUEST['version']);
         }
         
         if($item)
         {
            header('Location: '.$item->url());
         }
         else
         {
            header('Location: index.php?page=community_all');
         }
      }
      else
      {
         $this->page_title = 'Actualizaciones &lsaquo; Comunidad FacturaScripts';
         $this->page_description = 'Historial con las últimas actualizaciones de FacturaScripts';
         $this->template = 'public/changelog';
         
         $this->rid = FALSE;
         if( isset($_COOKIE['rid']) )
         {
            $this->rid = $_COOKIE['rid'];
         }
         
         $this->offset = 0;
         if( isset($_GET['offset']) )
         {
            $this->offset = intval($_GET['offset']);
         }
         
         $item = new comm3_item();
         $this->resultados = $item->all_by_tipo('changelog', $this->offset);
      }
   }
   
   public function path()
   {
      if( defined('COMM3_PATH') )
      {
         return COMM3_PATH;
      }
      else
         return '';
   }
   
   public function anterior_url()
   {
      $url = '';
      
      if($this->offset > 0)
      {
         $url = $this->url()."&offset=".($this->offset-FS_ITEM_LIMIT);
      }
      
      return $url;
   }
   
   public function siguiente_url()
   {
      $url = '';
      
      if( count($this->resultados) == FS_ITEM_LIMIT )
      {
         $url = $this->url()."&offset=".($this->offset+FS_ITEM_LIMIT);
      }
      
      return $url;
   }
}
