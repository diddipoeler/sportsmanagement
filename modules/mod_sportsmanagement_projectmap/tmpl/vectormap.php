<?php
/**
 * Joomla 5/6 compatibility layout for the historical Project Map vector-map view.
 *
 * The active dispatcher registers the native map assets and script options.
 * Keep existing module assignments using the "vectormap" layout compatible by
 * rendering the same passive map container as the default layout.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

require __DIR__ . '/default.php';
