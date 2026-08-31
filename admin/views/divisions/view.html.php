<?php
/**
 * Joomla 5/6 compatibility bridge for the legacy administrator divisions view.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Divisions\HtmlView;

if (!class_exists('sportsmanagementViewDivisions', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewDivisions');
}
