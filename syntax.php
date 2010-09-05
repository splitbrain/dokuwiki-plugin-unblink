<?php
/**
 * DokuWiki Plugin unblink (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';

class syntax_plugin_unblink extends DokuWiki_Syntax_Plugin {
    function getType() {
        return 'substition';
    }

    function getPType() {
        return 'normal';
    }

    function getSort() {
        return 150;
    }


    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\[\[user>.+?\]\]',$mode,'plugin_unblink');
    }

    function handle($match, $state, $pos, &$handler){
        $match = trim(substr($match,7,-2));


        list($login,$title) = explode('|',$match,2);

        return compact('login','title');
    }

    function render($mode, &$R, $data) {
        global $auth;
        global $conf;
        extract($data);

        if($mode != 'xhtml' || is_null($auth)){
            $R->cdata($title?$title:$login);
            return true;
        }

        // fetch userinfo
        $uinfo = $auth->getUserData($login);

        // nothing found? render as text
        if(!$uinfo){
            $R->cdata($title?$title:$login);
            return true;
        }

        if(!$title){
            if($this->getConf('usefullname')){
                $title = $uinfo['name'];
            }else{
                $title = $login;
            }
        }
        if(!$title) $title = $login;

        if($uinfo['avatar'] && $uinfo['avatar'] != 'gravatar'){
            $img = $this->getConf('avatarurl').$uinfo['avatar'];
        }else{
            $img = $this->getConf('gravatar').md5($uinfo['mail']);
        }

        $R->doc .= '<a href="'.$this->getConf('profileurl').$uinfo['uid'].'" class="unblink_plugin">';
        $R->doc .= hsc($title);

        $R->doc .= '<span class="unblink_popup" title="Visit Profile">';
        $R->doc .= '<img src="'.hsc($img).'" class="medialeft" width="64" height="64" alt="" />';
        $R->doc .= '<b>'.hsc($uinfo['name']).'</b><br />';
        if($uinfo['name'] != $login) $R->doc .= '<i>'.hsc($login).'</i><br />';
        $R->doc .= '<br />';
        $R->doc .= hsc($uinfo['location']);
        $R->doc .= '</span>';

        $R->doc .= '</a>';

        return true;
    }
}

// vim:ts=4:sw=4:et:enc=utf-8:
