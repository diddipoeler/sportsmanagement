<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Nextmatch view.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Nextmatch\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Nextmatch/HtmlView.php';
}

if (!class_exists(HtmlView::class)) {
    throw new \RuntimeException('SportsManagement native frontend Nextmatch view could not be loaded.', 500);
}

if (!class_exists('sportsmanagementViewNextMatch', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewNextMatch');
}
