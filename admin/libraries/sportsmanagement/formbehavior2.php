<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Utility class for form related behaviors
 *
 * @package    Joomla.Libraries
 * @subpackage HTML
 * @since      3.0
 */
abstract class JHtmlFormbehavior2
{
    /**
     * @var    array  Array containing information for loaded files
     * @since  3.0
     */
    protected static $loaded = array();

    /**
     * Method to load the Select2 JavaScript framework and supporting CSS into the document head
     *
     * @param string $selector Class for Chosen elements. [optional]
     * @param string $option   options for Select2 elements. [optional]
     * @param mixed  $debug    Is debugging mode on? [optional]
     *
     * @return void
     *
     * @since 3.0
     */
    public static function select2($selector = '.advancedSelect', $option = '', $debug = null)
    {
        if (isset(static::$loaded[__METHOD__][$selector])) {
            return;
        }

        // Include jQuery
        HTMLHelper::_('jquery.framework');

          // If no debugging value is set, use the configuration setting
        if ($debug === null) {
            $config = Factory::getConfig();
            $debug  = (boolean) $config->get('debug');
        }

        HTMLHelper::_('script', 'jui/select2.min.js', false, true, false, false, $debug);
        HTMLHelper::_('script', 'jui/select2_locale_cs.js', false, true, false, false, $debug);
        HTMLHelper::_('stylesheet', 'jui/select2.css', false, true);
        Factory::getDocument()->addScriptDeclaration(
            "
				jQuery(document).ready(function (){
					jQuery('" . $selector . "').select2({
						" .$option. "
					});
				});
			"
        );

        static::$loaded[__METHOD__][$selector] = true;

        return;
    }
}
