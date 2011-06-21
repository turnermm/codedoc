<?php
/**
 * Plugin Now: Inserts a timestamp.
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
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

    function getInfo() {
        return array('author' => 'me',
                     'email'  => 'me@someplace.com',
                     'date'   => '2005-07-28',
                     'name'   => 'Now Plugin',
                     'desc'   => 'Include the current date and time',
                     'url'    => 'http://www.dokuwiki.org/plugin:tutorial');
    }

    function getType() { return 'substition'; }
    function getSort() { return 32; }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~codedoc:.*?~~',$mode,'plugin_codedoc_specials');
    }

    function handle($match, $state, $pos, &$handler) {
        $match = trim(substr($match,10,-2));   
        $type = strtolower($match); 
        if(trim($type) == 'timestamp') {  
          return array($type, $state);
        }
        return array($match,$state);
    }
    
    function render($mode, &$renderer, $data) {
       global $ID;
        if($mode == 'xhtml'){
            list($match, $state) = $data;
            if($match == 'timestamp') {
                 $data = date("F d Y H:i:s.", filemtime(wikiFN($ID)));
            }
            else {

                       $match = preg_replace_callback("/\[\[(.*?)\]\]/",        
                       create_function(
                       '$matches',
                       '$elems = explode("|",$matches[1]);   
                       return html_wikilink($elems[0],$elems[1]);'
                       ),
                       $match);

                 $data = "/* $match */";
            }
           
            
            $renderer->doc .= '<span style="color:blue">' .$data . '</span>';
            return true;
        }
        return false;
    }
}
