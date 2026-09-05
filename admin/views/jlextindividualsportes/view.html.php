<?php
/**
 * Joomla 5/6 compatibility bridge for the individual-sports administrator view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Jlextindividualsportes\HtmlView;

if (!class_exists('sportsmanagementViewjlextindividualsportes', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewjlextindividualsportes');
}
