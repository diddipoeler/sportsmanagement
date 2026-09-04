<?php
/**
 * Legacy Select2 form behaviour helper for the SportsManagement administrator.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Utility class for legacy Select2 form behaviours.
 */
abstract class JHtmlFormbehavior2
{
    /**
     * @var array<string, array<string, bool>> Loaded selectors.
     */
    protected static $loaded = [];

    /**
     * Load the Select2 JavaScript framework and supporting CSS.
     *
     * @param string      $selector Selector for Select2 elements.
     * @param string      $option   JavaScript options for Select2.
     * @param mixed       $debug    Legacy compatibility argument; retained for callers.
     *
     * @return void
     */
    public static function select2($selector = '.advancedSelect', $option = '', $debug = null)
    {
        if (isset(static::$loaded[__METHOD__][$selector])) {
            return;
        }

        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);
        $wa  = $app->getDocument()->getWebAssetManager();

        $select2Asset = 'com_sportsmanagement.select2';
        $localeAsset  = 'com_sportsmanagement.select2.locale.cs';
        $styleAsset   = 'com_sportsmanagement.select2';

        if (!$wa->assetExists('script', $select2Asset)) {
            $wa->registerScript(
                $select2Asset,
                Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/select2.min.js',
                [],
                [],
                ['jquery']
            );
        }

        if (!$wa->assetExists('script', $localeAsset)) {
            $wa->registerScript(
                $localeAsset,
                Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/select2_locale_cs.js',
                [],
                [],
                [$select2Asset]
            );
        }

        if (!$wa->assetExists('style', $styleAsset)) {
            $wa->registerStyle(
                $styleAsset,
                Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/select2.css'
            );
        }

        $wa->useScript($select2Asset)
            ->useScript($localeAsset)
            ->useStyle($styleAsset)
            ->addInlineScript(
                "jQuery(document).ready(function () {\n"
                . "    jQuery('" . $selector . "').select2({\n"
                . "        " . $option . "\n"
                . "    });\n"
                . "});",
                [],
                [],
                [$localeAsset]
            );

        static::$loaded[__METHOD__][$selector] = true;
    }
}
