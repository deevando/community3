<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015-2016, Carlos García Gómez. All Rights Reserved. 
 */

require_model('comm3_item.php');

/**
 * Description of comm3_relacion
 *
 * @author carlos
 */
class comm3_relacion extends fs_model
{
   public $id;
   public $iditem1;
   public $iditem2;
   public $prioridad;
   
   public function __construct($r = FALSE)
   {
      parent::__construct('comm3_relaciones');
      if($r)
      {
         $this->id = $this->intval($r['id']);
         $this->iditem1 = $this->intval($r['iditem1']);
         $this->iditem2 = $this->intval($r['iditem2']);
         $this->prioridad = intval($r['prioridad']);
      }
      else
      {
         $this->id = NULL;
         $this->iditem1 = NULL;
         $this->iditem2 = NULL;
         $this->prioridad = 0;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function item($exclude = FALSE)
   {
      $it0 = new comm3_item();
      if($exclude != $this->iditem1)
      {
         return $it0->get($this->iditem1);
      }
      else
      {
         return $it0->get($this->iditem2);
      }
   }
   
   public function item1()
   {
      $it0 = new comm3_item();
      return $it0->get($this->iditem1);
   }
   
   public function item2()
   {
      $it0 = new comm3_item();
      return $it0->get($this->iditem2);
   }
   
   public function get($id)
   {
      $data = $this->db->select("SELECT * FROM comm3_relaciones WHERE id = ".$this->var2str($id).";");
      if($data)
      {
         return new comm3_relacion($data[0]);
      }
      else
         return FALSE;
   }
   
   public function exists()
   {
      if( is_null($this->id) )
      {
         return FALSE;
      }
      else
      {
         return $this->db->select("SELECT * FROM comm3_relaciones WHERE id = ".$this->var2str($this->id).";");
      }
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE comm3_relaciones SET prioridad = ".$this->var2str($this->prioridad).
                 " WHERE id = ".$this->var2str($this->id).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO comm3_relaciones (iditem1,iditem2,prioridad) VALUES (".
                 $this->var2str($this->iditem1).",".
                 $this->var2str($this->iditem2).",".
                 $this->var2str($this->prioridad).");";
         
         if( $this->db->exec($sql) )
         {
            $this->id = $this->db->lastval();
            return TRUE;
         }
         else
            return FALSE;
      }
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM comm3_relaciones WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function all_for($iditem)
   {
      $rlist = array();
      $sql = "SELECT * FROM comm3_relaciones WHERE iditem1 = ".$this->var2str($iditem)
              ." OR iditem2 = ".$this->var2str($iditem).";";
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $rlist[] = new comm3_relacion($d);
         }
      }
      
      return $rlist;
   }
}
