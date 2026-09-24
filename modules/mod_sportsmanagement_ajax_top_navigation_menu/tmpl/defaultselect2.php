<?php
/**
 * Legacy Select2 layout compatibility wrapper for the SportsManagement AJAX top navigation module.
 *
 * Joomla 5/6 uses the native module renderer and no longer loads the historical
 * jQuery Select2/Alertify CDN implementation from this layout.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_ajax_top_navigation_menu
 * @file       defaultselect2.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

require __DIR__ . '/native.php';
