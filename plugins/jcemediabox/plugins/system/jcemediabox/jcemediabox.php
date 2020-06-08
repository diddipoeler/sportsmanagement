<?php

/**
 * @package JCE MediaBox
 * @copyright Copyright (C) 2006-2017 Ryan Demmer. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL 3, see LICENCE
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
use Joomla\CMS\Factory;
jimport('joomla.plugin.plugin');

/**
 * JCE MediaBox Plugin
 *
 * @package         JCE MediaBox
 * @subpackage    System
 */
class plgSystemJCEMediabox extends JPlugin
{
    /**
     * Create a list of translated labels for popup window
     * @return Key : Value labels string
     */
    protected function getLabels()
    {
        JPlugin::loadLanguage('plg_system_jcemediabox', JPATH_ADMINISTRATOR);

        $words = array('close', 'next', 'previous', 'cancel', 'numbers', 'numbers_count', 'download');

        $v = array();

        foreach ($words as $word) {
            $v[$word] = htmlspecialchars(JText::_('PLG_SYSTEM_JCEMEDIABOX_LABEL_' . strtoupper($word)));
        }

        return $v;
    }

    private function getAssetPath($relative)
    {
        $path = __DIR__ . '/' . $relative;
        $hash = md5_file($path);
        
        return JURI::base(true) . '/plugins/system/jcemediabox/' . $relative . '?' . $hash;
    }

    /**
     * OnAfterRoute function
     * @return Boolean true
     */
    public function onAfterDispatch()
    {
        $app = Factory::getApplication();

        // only in "site"
        if ($app->getClientId() !== 0) {
            return;
        }

        $document = Factory::getDocument();
        $docType = $document->getType();

        // only in html pages
        if ($docType != 'html') {
            return;
        }

        $db = Factory::getDBO();

        // Causes issue in Safari??
        $pop = $app->input->getInt('pop');
        $print = $app->input->getInt('print');
        $task = $app->input->getCmd('task');
        $tmpl = $app->input->getWord('tmpl');

        // don't load mediabox on certain pages
        if ($pop || $print || $tmpl == 'component' || $task == 'new' || $task == 'edit') {
            return;
        }

        $params = $this->params;

        $components = $params->get('components');

        if (!empty($components)) {
            if (is_string($components)) {
                $components = explode(',', $components);
            }

            $option = $app->input->get('option', '');

            foreach ($components as $component) {
                if ($option === 'com_' . $component || $option === $component) {
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
            if ($menu && !in_array($menu->id, (array) $menuitems)) {
                return;
            }
        }

        // get excluded menu items from parameter
        $menuitems_exclude = (array) $params->get('menu_exclude');

        // is there a menu exclusion?
        if (!empty($menuitems_exclude) && !empty($menuitems_exclude[0])) {
            if ($menu && in_array($menu->id, (array) $menuitems_exclude)) {
                return;
            }
        }

        $theme = $params->get('theme', 'standard');

        if ($params->get('dynamic_themes', 0)) {
            $theme = $app->input->getWord('theme', $theme);
        }

        $config = array(
            'base' => JURI::base(true) . '/',
            'theme' => $theme,
            'mediafallback' => (int) $params->get('mediafallback', 0),
            'mediaselector' => $params->get('mediaselector', 'audio,video'),
            'width' => $params->get('width', ''),
            'height' => $params->get('height', ''),
            'lightbox' => (int) $params->get('lightbox', 0),
            'shadowbox' => (int) $params->get('shadowbox', 0),
            'icons' => (int) $params->get('icons', 1),
            'overlay' => (int) $params->get('overlay', 1),
            'overlay_opacity' => (float) $params->get('overlayopacity'),
            'overlay_color' => $params->get('overlaycolor', ''),
            'transition_speed' => (int) $params->get('transition_speed', $params->get('scalespeed', 300)),
            'close' => (int) $params->get('close', 2),
            'scrolling' => (string) $params->get('scrolling', 'fixed'),
            'labels' => $this->getLabels(),
        );

        if ($this->params->get('jquery', 1)) {
            // Include jQuery
            JHtml::_('jquery.framework');
        }

        $document->addScript($this->getAssetPath('js/jcemediabox.min.js'));
        $document->addStyleSheet($this->getAssetPath('css/jcemediabox.min.css'));

        $document->addScriptDeclaration('jQuery(document).ready(function(){WFMediaBox.init(' . json_encode($config) . ');});');

        return true;
    }
}