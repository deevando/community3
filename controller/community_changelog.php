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

require_model('comm3_item.php');

/**
 * Description of community_changelog
 *
 * @author carlos
 */
class community_changelog extends fs_controller
{
   public $nuevo_item;
   public $offset;
   public $page_title;
   public $page_description;
   public $page_keywords;
   public $resultados;
   public $rid;
   public $visitante;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Changelog', 'comunidad', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $item0 = new comm3_item();
      $this->nuevo_item = FALSE;
      
      if( isset($_REQUEST['version']) )
      {
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
            $this->nuevo_item = new comm3_item();
            if( isset($_REQUEST['plugin']) )
            {
               $this->nuevo_item->texto = 'Novedades del plugin [b]'.$_REQUEST['plugin'].'[/b], versión [b]'.$_REQUEST['version'].'[/b]:';
               $this->nuevo_item->tags = '['.$_REQUEST['plugin'].'_'.$_REQUEST['version'].'],['.$_REQUEST['plugin'].']';
            }
            else
            {
               $this->nuevo_item->texto = 'Novedades de [b]FacturaScripts '.$_REQUEST['version'].'[/b]:';
               $this->nuevo_item->tags = '[FS'.$_REQUEST['version'].'],[FacturaScripts]';
            }
         }
      }
      else if( isset($_POST['texto']) AND isset($_POST['tags']) )
      {
         $this->nuevo_item = new comm3_item();
         $this->nuevo_item->tipo = 'changelog';
         $this->nuevo_item->nick = $this->user->nick;
         $this->nuevo_item->ip = $this->user->last_ip;
         $this->nuevo_item->texto = $_POST['texto'];
         $this->nuevo_item->tags = $_POST['tags'];
         $this->nuevo_item->save();
         header('Location: '.$this->nuevo_item->url());
      }
      
      $this->offset = 0;
      if( isset($_GET['offset']) )
      {
         $this->offset = intval($_GET['offset']);
      }
      
      $this->resultados = $item0->all_by_tipo('changelog', $this->offset);
   }
   
   protected function public_core()
   {
      $this->visitante = FALSE;
      
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
         $this->page_title = 'Noticias de FacturaScripts';
         $this->page_description = 'Historial con las últimas noticias, novedades y actualizaciones de FacturaScripts';
         $this->page_keywords = 'noticias facturascripts, novedades facturascripts, actualizaciones facturascripts';
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
