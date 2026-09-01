<?php
/** Legacy compatibility bridge for the native Joomla 5/6 singular XML import entry view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Jlxmlimport\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Jlxmlimport/HtmlView.php';
}

if (!class_exists('sportsmanagementViewJLXMLImport', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewJLXMLImport');
}
