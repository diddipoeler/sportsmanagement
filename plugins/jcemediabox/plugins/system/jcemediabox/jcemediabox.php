<?php

/**
 * @package JCE MediaBox
 * @copyright Copyright (C) 2006-2013 Ryan Demmer. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see licence.txt
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *
 * Light Theme inspired by Slimbox by Christophe Beyls
 * @ http://www.digitalia.be
 *
 * Shadow Theme inspired by ShadowBox
 * @ http://mjijackson.com/shadowbox/
 *
 * Squeeze theme inspired by Squeezebox by Harald Kirschner
 * @ http://digitarald.de/project/squeezebox/
 *
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * JCE MediaBox Plugin
 *
 * @package         JCE MediaBox
 * @subpackage    System
 */
class plgSystemJCEMediabox extends JPlugin
{

    private static $version = '@@version@@';

    protected function getPath()
    {
        return JPATH_PLUGINS . '/system/jcemediabox';
    }

    protected function getURL()
    {
        return JURI::base(true) . '/plugins/system/jcemediabox';
    }

    protected function getEtag($file)
    {
        return md5($file . preg_replace('/[^\d]+/', '', self::$version));
    }

    /**
     * Create JSON parameter object
     * @param String $name Object name
     * @param Array $params Parameter array
     * @param Boolean $end If end parameters
     * @return JSON Object String
     */
    protected function renderParams($name, $params, $end)
    {
        $html = '';
        if ($name) {
            $html .= $name . ":{";
        }
        $i = 0;
        foreach ($params as $k => $v) {
            // not objects or arrays or functions or numbers
            if (!preg_match('/(\[[^\]*]\]|\{[^\}]*\}|function\([^\}]*\})/', $v)) {
                if (!is_numeric($v)) {
                    $v = '"' . $v . '"';
                }
            }
            if ($i < count($params) - 1) {
                $v .= ',';
            }
            if (preg_match('/\s+/', $k)) {
                $html .= "'" . $k . "':" . $v;
            } else {
                $html .= $k . ":" . $v;
            }

            $i++;
        }
        if ($name) {
            $html .= "}";
        }
        if (!$end) {
            $html .= ",";
        }
        return $html;
    }

    /**
     * Load theme css files
     * @param object $vars Parameter variables
     * @return Boolean true
     */
    protected function getThemeCSS($vars)
    {
        jimport('joomla.environment.browser');
        jimport('joomla.filesystem.file');

        $document = JFactory::getDocument();
        $theme = $vars['theme'] == 'custom' ? $vars['themecustom'] : $vars['theme'];

        // Load template css file
        if (JFile::exists(JPATH_ROOT . '/' . $vars['themepath'] . '/' . $theme . '/css/style.css')) {
            $document->addStyleSheet(JURI::base(true) . '/' . $vars['themepath'] . '/' . $theme . '/css/style.css?' . $this->getEtag($theme . '/style.css'));
        } else {
            $document->addStyleSheet(JURI::base(true) . '/' . $vars['themepath'] . '/standard/css/style.css?' . $this->getEtag('standard/style.css'));
        }
        return true;
    }

    /**
     * Create a list of translated labels for popup window
     * @return Key : Value labels string
     */
    protected function getLabels()
    {
        JPlugin::loadLanguage('plg_system_jcemediabox', JPATH_ADMINISTRATOR);

        $words = array('close', 'next', 'previous', 'cancel', 'numbers');
        $i = 0;
        $v = '';
        foreach ($words as $word) {
            $v .= "'" . $word . "':'" . htmlspecialchars(JText::_('JCEMEDIABOX_' . strtoupper($word))) . "'";
            if ($i < count($words) - 1) {
                $v .= ',';
            }
            $i++;
        }

        return $v;
    }

    /**
     * Load Addons
     * @return Boolean true
     */
    protected function getAddons()
    {
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        $path = $this->getPath() . '/addons';
        $filter = array('default-src.js');

        // not dev mode, skip default.js
        if (strpos(self::$version, '@@') === false) {
            $filter[] = 'default.js';
        }

        $files = JFolder::files($path, '.js$', false, false, $filter);

        $scripts = array();

        if (is_array($files) && count($files)) {
            foreach ($files as $file) {
                $scripts[] = 'addons/' . $file;
            }
        }

        return $scripts;
    }

    /**
     * OnAfterRoute function
     * @return Boolean true
     */
    public function onAfterDispatch()
    {
        $app = JFactory::getApplication();

        if ($app->isAdmin()) {
            return;
        }

        $document = JFactory::getDocument();
        $docType = $document->getType();

        // only in html pages
        if ($docType != 'html') {
            return;
        }

        $dev = true;

        $db = JFactory::getDBO();

        // Causes issue in Safari??
        $pop = JRequest::getInt('pop');
        $print = JRequest::getInt('print');
        $task = JRequest::getVar('task');
        $tmpl = JRequest::getWord('tmpl');

        // don't load mediabox on certain pages
        if ($pop || $print || $tmpl == 'component' || $task == 'new' || $task == 'edit') {
            return;
        }

        $params = $this->params;

        $components = $params->get('components', '');
        if ($components) {
            $excluded = explode(',', $components);
            $option = JRequest::getVar('option', '');
            foreach ($excluded as $exclude) {
                if ($option == 'com_' . $exclude || $option == $exclude) {
                    return;
                }
            }
        }

        // get active menu
        $menus = $app->getMenu();
        $menu = $menus->getActive();

        // get menu items from parameter
        $menuitems = (array) $params->get('menu');

        // is there a menu assignment?
        if (!empty($menuitems) && !empty($menuitems[0])) {
            if ($menu && !in_array($menu->id, $menuitems)) {
                return;
            }
        }

        // get excluded menu items from parameter
        $menuitems_exclude = (array) $params->get('menu_exclude');

        // is there a menu exclusion?
        if (!empty($menuitems_exclude) && !empty($menuitems_exclude[0])) {            
            if ($menu && in_array($menu->id, $menuitems_exclude)) {
                return;
            }
        }

        $theme = $params->get('theme', 'standard');

        if ($params->get('dynamic_themes', 0)) {
            $theme = JRequest::getWord('theme', $params->get('theme', 'standard'));
        }

        $popup = array(
            'width' => $params->get('width', ''),
            'height' => $params->get('height', ''),
            'legacy' => $params->get('legacy', 0),
            'lightbox' => $params->get('lightbox', 0),
            'shadowbox' => $params->get('shadowbox', 0),
            //'convert'            =>    $params->get('convert', 0),
            'resize' => $params->get('resize', 0),
            'icons' => $params->get('icons', 1),
            'overlay' => $params->get('overlay', 1),
            'overlayopacity' => $params->get('overlayopacity', 0.8),
            'overlaycolor' => $params->get('overlaycolor', '#000000'),
            'fadespeed' => $params->get('fadespeed', 500),
            'scalespeed' => $params->get('scalespeed', 500),
            'hideobjects' => $params->get('hideobjects', 1),
            'scrolling' => $params->get('scrolling', 'fixed'),
            //'protect'            =>    $params->get('protect', 1),
            'close' => $params->get('close', 2),
            'labels' => '{' . $this->getLabels() . '}',
            'cookie_expiry' => $params->get('cookie_expiry', ''),
            'google_viewer' => $params->get('google_viewer', 0),
        );

        $tooltip = array(
            'className' => $params->get('tipclass', 'tooltip'),
            'opacity' => $params->get('tipopacity', 0.8),
            'speed' => $params->get('tipspeed', 200),
            'position' => $params->get('tipposition', 'br'),
            'offsets' => "{x: " . $params->get('tipoffsets_x', 16) . ", y: " . $params->get('tipoffsets_y', 16) . "}",
        );

        $standard = array(
            'base' => JURI::base(true) . '/',
            'imgpath' => $params->get('imgpath', 'plugins/system/jcemediabox/img'),
            'theme' => $theme,
            'themecustom' => $params->get('themecustom', ''),
            'themepath' => $params->get('themepath', 'plugins/system/jcemediabox/themes'),
            'mediafallback' => $params->get('mediafallback', 0),
            'mediaselector' => $params->get('mediaselector', 'audio,video'),
        );

        jimport('joomla.environment.browser');
        jimport('joomla.filesystem.file');

        $scripts = $this->getScripts();

        $url = $this->getURL();

        foreach ($scripts as $script) {
            $document->addScript($url . '/' . $script . '?' . $this->getEtag(basename($script)));
        }

        $document->addStyleSheet($url . '/css/jcemediabox.css?' . $this->getEtag('jcemediabox.css'));

        $this->getThemeCss($standard);

        $html = "JCEMediaBox.init({";
        $html .= $this->renderParams('popup', $popup, false);
        $html .= $this->renderParams('tooltip', $tooltip, false);
        $html .= $this->renderParams('', $standard, true);
        $html .= "});";

        $document->addScriptDeclaration($html);
        return true;
    }

    protected function getScripts()
    {
        return array_merge(array('js/jcemediabox.js'), $this->getAddons());
    }

}
