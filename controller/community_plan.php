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
require_model('comm3_relacion.php');

/**
 * Description of community_plan
 *
 * @author carlos
 */
class community_plan extends fs_controller
{
   public $email;
   public $page_title;
   public $page_description;
   public $page_keywords;
   public $resultados;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Plan de desarrollo', 'comunidad', FALSE, FALSE);
   }
   
   public function url()
   {
      if( isset($_REQUEST['email']) )
      {
         return 'index.php?page='.__CLASS__.'&email='.$_REQUEST['email'];
      }
      else
         return parent::url();
   }
   
   protected function private_core()
   {
      $this->email = FALSE;
      if( isset($_REQUEST['email']) )
      {
         $this->email = $_REQUEST['email'];
      }
      
      $this->resultados = array();
      $rid = FALSE;
      if( isset($_REQUEST['email']) )
      {
         $this->resultados = $this->get_relaciones_tareas_email($this->email);
         
         if( isset($_POST['prioridades']) )
         {
            foreach($this->resultados as $res)
            {
               if( isset($_POST['prioridad_'.$res['item']->id]) )
               {
                  $res['item']->prioridad = intval($_POST['prioridad_'.$res['item']->id]);
                  $res['item']->save();
               }
            }
            
            $this->new_message('Datos guardados correctamente.');
            $this->resultados = $this->get_relaciones_tareas_email($this->email);
         }
      }
   }
   
   protected function public_core()
   {
      $this->page_title = 'Plan de desarrollo &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Plan de desarrollo personal de FacturaScripts.';
      $this->page_keywords = 'facturascripts, eneboo, abanq, woocommerce, prestashop, facturae';
      $this->template = 'public/plan';
      
      $this->resultados = array();
      $rid = FALSE;
      if( isset($_COOKIE['rid']) )
      {
         $rid = $_COOKIE['rid'];
         $this->resultados = $this->get_relaciones_tareas($rid);
         
         if( isset($_POST['prioridades']) )
         {
            foreach($this->resultados as $res)
            {
               if( isset($_POST['prioridad_'.$res['relacion']->id]) )
               {
                  $res['relacion']->prioridad = intval($_POST['prioridad_'.$res['relacion']->id]);
                  $res['relacion']->save();
               }
            }
            
            $this->new_message('Datos guardados correctamente.');
            $this->resultados = $this->get_relaciones_tareas($rid);
         }
      }
   }
   
   private function get_relaciones_tareas($rid)
   {
      $rlist = array();
      $rel0 = new comm3_relacion();
      
      $sql = "SELECT * FROM comm3_relaciones WHERE iditem1 IN "
              ."(SELECT id as iditem1 FROM comm3_items WHERE rid = ".$rel0->var2str($rid)." AND estado != 'cerrado')"
              ." OR iditem2 IN "
              ."(SELECT id as iditem2 FROM comm3_items WHERE rid = ".$rel0->var2str($rid)." AND estado != 'cerrado')"
              ." ORDER BY prioridad DESC;";
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $relacion = new comm3_relacion($d);
            $item = $relacion->item1();
            if($item->tipo != 'task')
            {
               $item = $relacion->item2();
            }
            
            $rlist[] = array('relacion' => $relacion, 'item' => $item);
         }
      }
      
      return $rlist;
   }
   
   private function get_relaciones_tareas_email($email)
   {
      $rlist = array();
      $rel0 = new comm3_relacion();
      
      $sql = "SELECT * FROM comm3_relaciones WHERE iditem1 IN "
              ."(SELECT id as iditem1 FROM comm3_items WHERE email = ".$rel0->var2str($email)." AND estado != 'cerrado')"
              ." OR iditem2 IN "
              ."(SELECT id as iditem2 FROM comm3_items WHERE email = ".$rel0->var2str($email)." AND estado != 'cerrado')"
              ." ORDER BY prioridad DESC;";
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $relacion = new comm3_relacion($d);
            $item = $relacion->item1();
            if($item->tipo != 'task')
            {
               $item = $relacion->item2();
            }
            
            $rlist[] = array('relacion' => $relacion, 'item' => $item);
         }
      }
      
      return $rlist;
   }
}
