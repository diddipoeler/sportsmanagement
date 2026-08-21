<?php
/** Legacy compatibility bridge for the native frontend Editperson view. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\View\Editperson\HtmlView;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;

if (!class_exists(SportsManagementHtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
}

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Editperson/HtmlView.php';
}

if (!class_exists('sportsmanagementViewEditPerson', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewEditPerson');
}
