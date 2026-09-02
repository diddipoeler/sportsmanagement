<?php
/**
 * Joomla 5/6 compatibility layout for the historical calendar view.
 *
 * The previous layout embedded an obsolete jQuery FullCalendar runtime and
 * exposed the Google API key in inline JavaScript. Keep existing module
 * assignments using the "calendar" layout working by rendering the same
 * server-side, escaped event data as the native default layout.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

require __DIR__ . '/default.php';
