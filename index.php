<?php

/**
 * moziloCMS Plugin: ContentOverview
 *
 * Returns a different styled list of content pages of current category
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   DEVMOUNT <mail@devmount.de>
 * @license  GPL v3+
 * @version  GIT: v0.1.2014-04-14
 * @link     https://github.com/devmount-mozilo/ContentOverview/wiki/Dokumentation
 *
 * Plugin created by DEVMOUNT
 * www.devmount.de
 *
 */

// only allow moziloCMS environment
if (!defined('IS_CMS')) {
    die();
}

/**
 * ContentOverview Class
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   DEVMOUNT <mail@devmount.de>
 * @license  GPL v3+
 * @link     https://github.com/devmount-mozilo/ContentOverview
 */
class ContentOverview extends Plugin
{
    // language
    private $_admin_lang;
    private $_cms_lang;

    // plugin information
    const PLUGIN_AUTHOR  = 'DEVMOUNT';
    const PLUGIN_TITLE   = 'ContentOverview';
    const PLUGIN_VERSION = 'v0.1.2014-04-14';
    const MOZILO_VERSION = '2.0';
    const PLUGIN_DOCU
        = 'https://github.com/devmount-mozilo/ContentOverview/wiki/Dokumentation';

    private $_plugin_tags = array(
        'tag1' => '{ContentOverview|tiles}',
        'tag2' => '{ContentOverview|list}',
        'tag3' => '{ContentOverview|links}',
    );

    const LOGO_URL = 'http://media.devmount.de/logo_pluginconf.png';

    /**
     * creates plugin content
     *
     * @param string $value Parameter divided by '|'
     *
     * @return string HTML output
     */
    function getContent($value)
    {
        // call given mode
        switch ($value) {
        case 'tiles':
            return $this->catTiles();
            break;
        case 'list':
            return $this->catList();
            break;
        case 'links':
            return $this->catLinks();
            break;
        default:
            return $this->noMode();
            break;
        }

        return $this->noMode();
    }

    /**
     * returns list formatted as tiles
     *
     * @return string html output
     */
    function catTiles()
    {
        global $CatPage;

        // get conf
        $tilenumber = $this->settings->get("tilenumber");
        $col = 15/$tilenumber;

        // get pages of current cat
        $pagearray = $CatPage->get_PageArray(
            CAT_REQUEST,
            array(EXT_PAGE, EXT_HIDDEN)
        );
        // real cat name without '/'
        $cat = substr(CAT_REQUEST, strpos(CAT_REQUEST, '%2F')+3);

        // remove page = category
        for ($i=0; $i < count($pagearray); $i++) {
            if ($cat == $pagearray[$i]) {
                unset($pagearray[$i]);
            }
        }
        $pagearray = array_values($pagearray);

        $content = '';

        // build tile for each content page
        for ($i=0; $i < count($pagearray); $i++) {
            $pagename = $pagearray[$i];
            $catname = CAT_REQUEST;
            // if pagename contains category, extract the name, rebuild the category
            if (strpos($pagearray[$i], '%2F') !== false) {
                // real page name without '/'
                $pagename = substr($pagearray[$i], strpos($pagearray[$i], '%2F')+3);
                $catname = $pagearray[$i];
            }
            // build rows
            if ($i % $tilenumber == 0) {
                $content .= '<div class="row">';
            }
            $content .=
                '<div
                    class="large-' . $col . ' large-uncentered columns box"
                >';
            // build page link with mozilo syntax [seite|...]
            $content .=
                '[seite=<span>'. urldecode($pagename) . '</span>
                    |@='. $catname . ':'. $pagename . '=@]';
            $content .= '</div>';
            if ($i % $tilenumber == $tilenumber-1) {
                $content .= '</div>';
            }
        }
        if (count($pagearray) % $tilenumber != 0) {
            $content .= '</div>';
        }
        // clear floats
        $content .= '<br style="clear:both;" />';

        return $content;
    }

    /**
     * returns list formatted as list
     *
     * @return string html output
     */
    function catList()
    {
        global $CatPage;

        // get conf
        $catdescriptions = explode('\r\n', $this->settings->get("catdescriptions"));

        // get pages of current cat
        $pagearray = $CatPage->get_PageArray(CAT_REQUEST, array(EXT_PAGE));
        $cat = substr(CAT_REQUEST, strripos(CAT_REQUEST, '%2F')+3);

        $content = '';

        // build listbox for each page
        foreach ($pagearray as $page) {
            $pagename = $page;
            $catname = CAT_REQUEST;
            // if pagename contains category, extract the name, rebuild the category
            if (strpos($page, '%2F') !== false) {
                // real page name without '/'
                $pagename = substr($page, strpos($page, '%2F')+3);
                $catname = $page;
            }
            // only take pages unlike category
            if ($cat != $page) {
                $content .= '<div class="row collapse listboxrow">';
                $content .=     '<div class="large-5 columns listbox">';
                $content .=     '[seite=<span>'. urldecode($pagename) . '</span>
                                    |@='. $catname . ':'. $pagename . '=@]';
                $content .=     '</div>';
                $content .=     '<div class="large-10 columns listboxdescription">';
                $description = $this->settings->get('conf_' . $page);
                if (isset($description)) {
                    $content .= $description;
                }
                $content .=     '</div>';
                $content .= '</div>';
            }
        }

        return $content;
    }

    /**
     * returns list formatted as tiles
     *
     * @return string html output
     */
    function catLinks()
    {
        global $CatPage;

        // get conf
        $catdescriptions = explode('\r\n', $this->settings->get("catdescriptions"));

        // get pages of current cat
        $pagearray = $CatPage->get_PageArray(CAT_REQUEST, array(EXT_PAGE));
        $cat = substr(CAT_REQUEST, strripos(CAT_REQUEST, '%2F')+3);

        $content = '';

        // build list item for each page
        foreach ($pagearray as $page) {
            $pagename = $page;
            $catname = CAT_REQUEST;
            // if pagename contains category, extract the name, rebuild the category
            if (strpos($page, '%2F') !== false) {
                // real page name without '/'
                $pagename = substr($page, strpos($page, '%2F')+3);
                $catname = $page;
            }
            // only take pages unlike category
            if ($cat != $page) {
                if (PAGE_REQUEST != $page) {
                    $content .=
                        '[liste|[seite=<span>'. urldecode($pagename) . '</span>
                            |@='. $catname . ':'. $pagename . '=@]]';
                } else {
                    $content .= '[liste|' . urldecode($pagename) . ']';
                }
            }
        }

        return $content;
    }

    /**
     * returns warning of wrong or no mode
     *
     * @return string html output
     */
    function noMode()
    {
        $content = 'Bitte einen gültigen Modus angeben.';
        return $content;
    }

    /**
     * sets backend configuration elements and template
     *
     * @return Array configuration
     */
    function getConfig()
    {
        global $CatPage;
        $pages = array();
        $catarr = $CatPage->get_CatArray();
        foreach ($catarr as $cat) {
            $pagearr = $CatPage->get_PageArray($cat);
            foreach ($pagearr as $page) {
                $pages[$page] = $cat;
            }
        }

        $config = array();

        // Number of tiles in one line
        $config['tilenumber']  = array(
            'type' => 'text',
            'description' => 'Anzahl an Kacheln in einer Reihe',
            'maxlength' => '100',
            'size' => '4',
            'regex' => "/^[1-9]{1,2}$/",
            'regex_error' => 'Bitte eine Zahl zwischen 1 und 9 eingeben'
        );

        // Categories / Page Description
        foreach ($pages as $page => $cat) {
            $config['conf_' . $page] = array(
                'type' => 'textarea',
                'description' => '<b>' . urldecode($page) . '</b>',
                'cols' => '',
                'rows' => '1'
            );
        }

        // read admin.css
        $admin_css = '';
        $lines = file('../plugins/' . self::PLUGIN_TITLE. '/admin.css');
        foreach ($lines as $line_num => $line) {
            $admin_css .= trim($line);
        }

        // add template CSS
        $template = '<style>' . $admin_css . '</style>';

        // Template
        $template .= '
            <div class="contentoverview-admin-header">
            <span>'
                . $this->_admin_lang->getLanguageValue(
                    'admin_header',
                    self::PLUGIN_TITLE
                )
            . '</span>
            <a href="' . self::PLUGIN_DOCU . '" target="_blank">
            <img style="float:right;" src="' . self::LOGO_URL . '" />
            </a>
            </div>
        </li>
        <li class="mo-in-ul-li ui-widget-content contentoverview-admin-li">
            <div class="contentoverview-admin-subheader">'
            . $this->_admin_lang->getLanguageValue('admin_tiles')
            . '</div>
            <div style="margin-bottom:5px;">
                    {tilenumber_text}
                {tilenumber_description}
            </div>
        </li>
        <li class="mo-in-ul-li ui-widget-content contentoverview-admin-li">
            <div class="contentoverview-admin-subheader">'
            . $this->_admin_lang->getLanguageValue('admin_description')
            . '</div>
            <div style="margin-bottom:5px;">
        ';
        foreach ($pages as $page => $cat) {
            $template .=
                '{conf_' . $page . '_description}
                {conf_' . $page . '_textarea}';
        }

        $config['--template~~'] = $template;

        return $config;
    }

    /**
     * sets backend plugin information
     *
     * @return Array information
     */
    function getInfo()
    {
        global $ADMIN_CONF;

        $this->_admin_lang = new Language(
            $this->PLUGIN_SELF_DIR
            . 'lang/admin_language_'
            . $ADMIN_CONF->get('language')
            . '.txt'
        );

        // build plugin tags
        $tags = array();
        foreach ($this->_plugin_tags as $key => $tag) {
            $tags[$tag] = $this->_admin_lang->getLanguageValue('tag_' . $key);
        }

        $info = array(
            '<b>' . self::PLUGIN_TITLE . '</b> ' . self::PLUGIN_VERSION,
            self::MOZILO_VERSION,
            $this->_admin_lang->getLanguageValue(
                'description',
                htmlspecialchars($this->_plugin_tags['tag1'], ENT_COMPAT, 'UTF-8'),
                htmlspecialchars($this->_plugin_tags['tag2'], ENT_COMPAT, 'UTF-8'),
                htmlspecialchars($this->_plugin_tags['tag3'], ENT_COMPAT, 'UTF-8')
            ),
            self::PLUGIN_AUTHOR,
            array(
                self::PLUGIN_DOCU,
                self::PLUGIN_TITLE . ' '
                . $this->_admin_lang->getLanguageValue('on_devmount')
            ),
            $tags
        );

        return $info;
    }

}
?>
