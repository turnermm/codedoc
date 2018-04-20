<?php

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');


class syntax_plugin_codedoc_block extends DokuWiki_Syntax_Plugin {
    var $index = 0;
    var $last_id = 0;  
    var $last_header = "";  
    var $geshi = false;
    var $no_numbers = false;
 
    function getType(){ return 'container'; }
    function getPType(){ return 'block'; }
    function getAllowedTypes() { 
      return array('substition');
    }
    function getSort(){ return 25; }

    // override default accepts() method to allow nesting 
    // - ie, to get the plugin to accept its own entry syntax
    function accepts($mode) {
      if ($mode == substr(get_class($this), 7)) return true;
        return parent::accepts($mode);
      }

    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<codedoc.*?>(?=.*?</codedoc>)',$mode,'plugin_codedoc_block');
        $this->Lexer->addEntryPattern('<codetoggle.*?>(?=.*?</codetoggle)',$mode,'plugin_codedoc_block');
    }
    function postConnect() {
        $this->Lexer->addExitPattern('</codedoc>','plugin_codedoc_block');
        $this->Lexer->addExitPattern('</codetoggle>','plugin_codedoc_block');
    }
 
    function handle($match, $state, $pos, Doku_Handler $handler){

        switch ($state) {

          case DOKU_LEXER_ENTER : 
            $type = strtolower(trim(substr($match,8,-1))); 
            $type = str_replace('_no_numbers',"",$type,$count);
            if($count) {
               $type = trim($type);
               $this->no_numbers =  true;
            }
            
            return array($state, trim($type));          
 
          case DOKU_LEXER_UNMATCHED :
            return array($state, $match);
          case DOKU_LEXER_EXIT :            
             return array($state,$match);

          default:
          
            return array($state,$match);
        }
    }
 
    function render($mode, Doku_Renderer $renderer, $data) {

        if($mode == 'xhtml'){

          list($state, $match) = $data;

          switch ($state) {
            case DOKU_LEXER_ENTER :
           
              $id = "";  
              if(preg_match('/^(toggle)(.*)/',$match, $matches)) {
                 if($matches[2]) {
                     $this->last_header = $matches[2];
                     $match = $matches[1];  // class
                 }
                 $this->index++;
                 $id = ' id="codedoc_' . $this->index . '" ';
                 $this->last_id= '"codedoc_' . $this->index . '"';
              }
              if($this->last_id) {
                $show_header ="";
                $show_button = '<span class="codedoc_show" id="s_' . trim($this->last_id,'"') . '">show</span>';
                if($this->last_header) {
                    if($this->no_numbers) {
                        $show_header = "$this->last_header";
                    }
                    else $show_header = "[$this->index]$this->last_header";
                }    
                $renderer->doc .= "\n$show_header <a href='javascript:codedoc_toggle($this->last_id);void 0;'>$show_button</a>";
                $this->last_id = ""; 
                $this->last_header="";
              }
             if(strpos($match,':') != false) {                 
                  list($match, $this->geshi) = explode(':',$match);          
                  $match = "$match " . $this->geshi;                  
             }
             $renderer->doc .= '<pre ' . $id  . 'class="'.$match.'">';
              break;
  
            case DOKU_LEXER_UNMATCHED :
            if($this->geshi) {
               $renderer->doc .= p_xhtml_cached_geshi($match, $this->geshi,'');
            }
             else $renderer->doc .= $renderer->_xmlEntities($match);           
             break;
  
            case DOKU_LEXER_EXIT :
              $renderer->doc .= "</pre>";
               break;
          }
          return true;

        } 
        // unsupported $mode
        return false;
    } 

 function write_debug($what) {

  $handle = fopen('codeblock.txt', 'a');
  fwrite($handle,"$what\n");
  fclose($handle);
 }
}
 
//Setup VIM: ex: et ts=4 enc=utf-8 :
?>
