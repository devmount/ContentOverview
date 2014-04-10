<?php if(!defined('IS_CMS')) die();

/**
 * Plugin:   contentOverview
 * @author:  HPdesigner (hpdesigner[at]web[dot]de)
 * @version: v0.0.2013-09-10
 * @license: GPL
 *
 * Plugin created by DEVMOUNT
 * www.devmount.de
 *
**/

class contentOverview extends Plugin {

    function getContent($value) {

        // get mode
        switch ($value) {
            case 'tiles': return $this->catTiles(); break;
            case 'list': return $this->catList(); break;
            case 'links': return $this->catLinks(); break;
            default: return $this->noMode(); break;
        }

        return null;
    }

    function catTiles() {
        global $CatPage;

        // get conf
        $tilenumber = $this->settings->get("tilenumber");
        $col = 15/$tilenumber;

        // get pages of current cat
        $pagearray = $CatPage->get_PageArray(CAT_REQUEST, array(EXT_PAGE, EXT_HIDDEN));
        $cat = substr(CAT_REQUEST,strpos(CAT_REQUEST, '%2F')+3); // real cat name without '/'

        // remove page = category
        for ($i=0; $i < count($pagearray); $i++) if ($cat == $pagearray[$i]) unset($pagearray[$i]);
        $pagearray = array_values($pagearray);

        $content = '';

        // build tiles content
        for ($i=0; $i < count($pagearray); $i++) {
            $pagename = $pagearray[$i];
            $catname = CAT_REQUEST;
            // if pagename contains Kategorie, extract the name, rebuild the category
            if (strpos($pagearray[$i], '%2F') !== false) {
                $pagename = substr($pagearray[$i],strpos($pagearray[$i], '%2F')+3); // real page name without '/'
                $catname = $pagearray[$i];
            }
            // build rows TODO fix
            if ($i % $tilenumber == 0) $content .= '<div class="row">';
            $content .= '<div class="large-' . $col . ' large-uncentered columns box">';
            $content .= '[seite=<span>'. urldecode($pagename) . '</span>|@='. $catname . ':'. $pagename . '=@]';
            $content .= '</div>';
            if ($i % $tilenumber == $tilenumber-1) $content .= '</div>';
        }

        $content .= '</div></div><br style="clear:both;" />';

        return $content;
    }

    function catList() {
        global $CatPage;

        // get conf
        $catdescriptions = explode('\r\n', $this->settings->get("catdescriptions"));

        // get pages of current cat
        $pagearray = $CatPage->get_PageArray(CAT_REQUEST, array(EXT_PAGE));
        $cat = substr(CAT_REQUEST,strripos(CAT_REQUEST, '%2F')+3);

        $content = '';

        foreach ($pagearray as $page) {
            $pagename = $page;
            $catname = CAT_REQUEST;
            // if pagename contains Kategorie, extract the name, rebuild the category
            if (strpos($page, '%2F') !== false) {
                $pagename = substr($page,strpos($page, '%2F')+3); // real page name without '/'
                $catname = $page;
            }
            if ($cat != $page) {
                $content .= '<div class="row collapse listboxrow">';
                $content .=     '<div class="large-5 columns listbox">';
                $content .=     '[seite=<span>'. urldecode($pagename) . '</span>|@='. $catname . ':'. $pagename . '=@]';
                $content .=     '</div>';
                $content .=     '<div class="large-10 columns listboxdescription">';
                $description = $this->settings->get('conf_' . $page);
                if (isset($description)) $content .= $description;
                $content .=     '</div>';
                $content .= '</div>';
            }
        }

        return $content;
    }

    function catLinks() {
        global $CatPage;

        // get conf
        $catdescriptions = explode('\r\n', $this->settings->get("catdescriptions"));

        // get pages of current cat
        $pagearray = $CatPage->get_PageArray(CAT_REQUEST, array(EXT_PAGE));
        $cat = substr(CAT_REQUEST,strripos(CAT_REQUEST, '%2F')+3);

        $content = '';

        foreach ($pagearray as $page) {
            $pagename = $page;
            $catname = CAT_REQUEST;
            // if pagename contains Kategorie, extract the name, rebuild the category
            if (strpos($page, '%2F') !== false) {
                $pagename = substr($page,strpos($page, '%2F')+3); // real page name without '/'
                $catname = $page;
            }
            if ($cat != $page and PAGE_REQUEST != $page) {
                $content .= '[liste|[seite=<span>'. urldecode($pagename) . '</span>|@='. $catname . ':'. $pagename . '=@]]';
            }
        }

        return $content;
    }

    function noMode() {
        $content = 'Bitte einen gültigen Modus angeben.';
        return $content;
    }


    function getConfig() {
        global $CatPage;
        $pages = array();
        $catarr = $CatPage->get_CatArray();
        foreach ($catarr as $cat) {
            $pagearr = $CatPage->get_PageArray($cat);
            foreach ($pagearr as $page) {
                $pages[$page] = '';
            }
        }

        $config = array();

        // Number of tiles in one line
        $config['tilenumber']  = array(
            "type" => "text",
            "description" => 'Anzahl an Kacheln in einer Reihe',
            "maxlength" => "100",
            "size" => "4",
            "regex" => "/^[1-9]{1,2}$/",
            "regex_error" => 'Bitte eine Zahl zwischen 1 und 9 eingeben'
        );

        // Categories / Page Description
        foreach ($pages as $page => $value) {
            $config['conf_' . $page] = array(
                "type" => "textarea",
                "description" => '<b>' . urldecode($page) . '</b> Beischreibung',
                "cols" => "98",
                "rows" => "3"
            );
        }

        // Template
        $config['--template~~'] = '
                <div class="mo-in-li-l" style="width:34%;">{tilenumber_description}</div>
                <div class="mo-in-li-r" style="width:64%;">{tilenumber_text}</div>';
        foreach ($pages as $page => $value) {
        $config['--template~~'] .= '</li>
            <li class="mo-in-ul-li mo-inline ui-widget-content ui-corner-all ui-helper-clearfix">
                <div class="mo-in-li-l" style="width:34%;">{conf_' . $page . '_description}</div>
                <div class="mo-in-li-r" style="width:64%;">{conf_' . $page . '_textarea}';
        }
        return $config;
    }


    function getInfo() {
        global $ADMIN_CONF;
        $language = $ADMIN_CONF->get("language");

        $info['deDE'] = array(
            // Plugin-Name
            '<b>contentOverview</b> v0.0.2013-09-10',
            // CMS-Version
            "2.0",
            // Kurzbeschreibung
            '{contentOverview|tiles} erstellt das Untermenü der aktuellen Kategorie in Kachelansicht.<br />
            {contentOverview|list} erstellt das Untermenü der aktuellen Kategorie in Listenansicht.<br />
            {contentOverview|links} erstellt eine Liste von Seitenlinks zu den anderen Seiten der aktuellen Kategorie.<br />
            <b>Achtung:</b> Wird eine Kategorie umbenannt, gehen die dafür gesetzten Beschreibungen verloren!',
            // Name des Autors
            'HPdesigner',
            // Download-URL
            'http://www.devmount.de/Develop/moziloCMS/Plugins/contentOverview.html',
            array(
                '{contentOverview|tiles}' => 'Untermenü als Kacheln (nur Titel)',
                '{contentOverview|list}'  => 'Untermenü als Liste (Titel und Beschreibungen)',
                '{contentOverview|links}'  => 'Liste von Links zu anderen Seiten der aktuellen Kategorie',
            )
        );

        if(isset($info[$language])) return $info[$language]; else return $info['deDE'];
    }

}
?>