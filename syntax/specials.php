<?php
/**
 * Plugin Now: Inserts a timestamp.
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Myron Turner <tunermm02@shaw.ca>
 */

// must be run within DokuWiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once DOKU_PLUGIN.'syntax.php';

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_codedoc_specials extends DokuWiki_Syntax_Plugin {


    function getType() { return 'substition'; }
    function getSort() { return 32; }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~codedoc:.*?~~',$mode,'plugin_codedoc_specials');
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        $match = trim(substr($match,10,-2));   
        $type = strtolower($match); 
        if(trim($type) == 'timestamp') {  
          return array($type, $state);
        }
        return array($match,$state);
    }
    
    function render($mode, Doku_Renderer $renderer, $data) {
       global $ID;
        if($mode == 'xhtml'){
            list($match, $state) = $data;
            if(preg_match('/\s*xref:(.*)/i',$match,$matches)) {
                 $renderer->doc .= '<a name="' . trim(strtolower($matches[1])) . '">&nbsp;</a>';
                 return true;
            }

            if(preg_match('/\s*clean:(.*)/i',$match,$matches)) {
                 $data = $matches[1];
                 $class="codedoc_clean";
            }

            elseif($match == 'timestamp') {
                 $data = date("F d Y H:i:s.", filemtime(wikiFN($ID)));
            }
            elseif($match == 'user') {
                 global $INFO;
                 $userinfo = 	$INFO['userinfo'];
                 if(isset($userinfo) && isset($userinfo['name'])) {
				        $data = $userinfo['name'];
                   }	
                   else $data = "";				   
            }
            else {
                       $NL = '/* ';
                       $ENDL = ' */'; 
                       $match = preg_replace_callback("/\[\[(.*?)\]\]/",        
                       create_function(
                       '$matches',
                       '$elems = explode("|",$matches[1]);  
                       return html_wikilink($elems[0],$elems[1]);'
                       ),
                       $match);
                 if(strpos($match, '<br') || strpos($match, '<BR') || strpos($match, "\n")) {
                   $NL .= "\n";
                   $ENDL = "\n**/";
                 }
                 else if(preg_match("/<(em|b)/",$match)) {
                     $NL = '';
                    $ENDL = ''; 
                    $match = preg_replace("/<(b|em|code)>/", "<$1 class = 'codedoc_hilite'>",$match);
                 }
                 $data = "$NL $match $ENDL";
                 $class='codedoc_hilite';
            }
            $renderer->doc .= '<span class="' . $class. '">' .$data . '</span>';       
            return true;
        }
        return false;
    }
}


