<?php
/**
 * DataTables plugin: Add DataTables support to DokuWiki
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2016, Giuseppe Di Terlizzi
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class syntax_plugin_datatables extends DokuWiki_Syntax_Plugin {

  function getType(){ return 'container';}
  function getAllowedTypes() { return array('container', 'substition'); }
  function getPType(){ return 'block';}
  function getSort(){ return 195; }

  function connectTo($mode) {
    $this->Lexer->addEntryPattern('<(?:DATATABLES?|datatables?)\b.*?>(?=.*?</(?:DATATABLES?|datatables?)>)', $mode, 'plugin_datatables');
  }

  public function postConnect() {
    $this->Lexer->addExitPattern('</(?:DATATABLES?|datatables?)>', 'plugin_datatables');
  }

  function handle($match, $state, $pos, Doku_Handler $handler) {
    $result = [];
    
    switch ($state) {
      case DOKU_LEXER_ENTER:
        $html5_data = array();
        $xml = @simplexml_load_string(str_replace('>', '/>', $match));

        if (is_object($xml)) {
          foreach ($xml->attributes() as $key => $value) {
            $html5_data[] = sprintf("data-%s='%s'", $key, str_replace("'", "&apos;", (string) $value));
          }
        }
        else {
          global $ACT;
  
          if ($ACT == 'preview') {
            msg(sprintf('<strong>DataTable Plugin</strong> - Malformed tag (<code>%s</code>). Please check your code!', hsc($match)), -1);
          }
        }

        $result[] = sprintf('<div class="dt-wrapper" %s>', implode(' ', $html5_data));
        break;

      case DOKU_LEXER_EXIT:
        $result[] = '</div>';
        break;

      case DOKU_LEXER_UNMATCHED:
        $result[] = $match;
        break;
    }

    return $result;
  }

  function render($mode, Doku_Renderer $renderer, $data) {

    if (empty($data) || $mode !== 'xhtml')
      return false;
    
    $renderer->doc .= $data[0];
    return true;
  }

}
