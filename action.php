<?php
/**
*    @name action plugin toolbuttondel
*    @description deletes toolbar buttons selected from configuration manager
*    @author Myron Turner <turnermm02@shaw.ca>
*/
if (!defined('DOKU_INC')) die();
class action_plugin_toolbuttondel extends DokuWiki_Action_Plugin {  
    private $select_head = array();
    private $key_type_chars  = array();  // will be merged with autoheads
    private $misc_icons = array();
    
    function register(&$controller) {     
        $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'delete_buttons', array ());
    }


    function delete_buttons(& $event, $param) {       
       $this->parse_options($select_picker);   
       $this->formats($event);
       $this->select_headers($event,$select_picker);
       $this->pickers($event);
        
    }
 
function parse_options(&$select_picker = false) {
    $auto_headers = array('same'=>'8','higher'=>'0','lower'=>'9');
    $key_codes = array(
       'bold' =>'b',
       'italic' =>'i',
       'underline' =>'u',
       'code' =>'c',
       'delete' =>'d',
       'ordered list' =>'-',
       'unordered list' =>'.',
       'signature' =>'y'
   );    
    $misc_names = array('smileys'=>'smiley', 'special'=>'chars','horizontal' =>'hr');
       
    $this->key_type_chars=$this->get_array('key_types',$key_codes);     
    
    $misc = $this->getConf('misc');
    $misc = explode(',',$misc);   
    $_misc = array();
    for($i = 0; $i <count($misc); $i++) {       
       $a = explode(' ', $misc[$i]);
       $_misc[] = $a[0];
    }    
   
    $this->misc_icons=$this->get_array($_misc,$misc_names);     
   
    $headers=array();     
    $autohead=$this->get_array('headers',$auto_headers,$headers);   
    $this->key_type_chars = array_merge($this->key_type_chars,$autohead);
    
    if(!$headers) return;  
    if(in_array('select',$headers)) {
       $select_picker = true;
    }
    for($i=0; $i<=5; $i++) {
       if(in_array($i,$headers)) {
           $this->select_head[] = $i;
       }      
    }

} 

function get_array($conf,$cmp, &$tmp=false) {
   $ar = array();
    if(!is_array($conf)) {
        $str = $this->getConf($conf);
        $conf = explode(',',$str);   
    }
    
   foreach($cmp  as $name=>$val) {
       if(in_array($name, $conf)){
          $ar[] = $val;
       }
    }    

   if(is_array($tmp)) $tmp = $conf;
   return $ar;
}

function pickers(& $event) {
       if(!$this->misc_icons) return;
       $ckeys =  preg_quote(implode(';',$this->misc_icons));      
       $ckeys = str_replace(';','|',$ckeys);


        for($i=0; $i<count($event->data); $i++) {   
        if(preg_match('/'. $ckeys . '/',$event->data[$i]['icon'])) {
           unset($event->data[$i]);
         }           
     }
}
 
 /*
   checks all formats with keys including autoheads, except for the
   selected headlines, each of which is a separate array under a sub array to the Select Headling picker,
   which is named 'list'
 */
function formats(& $event) {      
       if(!$this->key_type_chars) return;
       $ckeys =  preg_quote(implode(';',$this->key_type_chars));      
       $ckeys = str_replace(';','|',$ckeys);
       $tmp = array();
        for($i=0; $i<count($event->data); $i++) {   
            if($ckeys && !preg_match('/'. $ckeys .'/' ,$event->data[$i]['key'])) {
           $tmp[] = $event->data[$i];
          }
          
       }   

       $event->data = $tmp;
      
}
 
 function select_headers(& $event,$select=false) {
    for($i=0; $i<count($event->data); $i++) {   
        if(array_key_exists('class', $event->data[$i]) && $event->data[$i]['class'] == 'pk_hl') {         
              if($select) {
                   unset($event->data[$i]);  
                 }
              else $this->check_selheader_keys($event->data[$i]['list']);
            break;
        }
   }
 }

/*
Assigns to new array because original keeps moving when unset is used on original
*/ 
function check_selheader_keys(& $list) {
   if(!$this->select_head) return;
   $tmp = array();  
   for($i=0; $i<count($list); $i++) {      
       if(!in_array($list[$i]['key'],$this->select_head)) {         
          $tmp[] =   $list[$i];
       }
   }
   
   $list = $tmp;

} 

function write_debug($data) {
  return;
  if (!$handle = fopen(DOKU_INC .'toolbar.txt', 'a')) {
    return;
    }
  if(is_array($data)) {
     $data = print_r($data,true);
  }
    // Write $somecontent to our opened file.
    fwrite($handle, "$data\n");
    fclose($handle);

}

}