<?php
/** Legacy compatibility bridge for the native site image handler view. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\View\Imagehandler\HtmlView;

if (!class_exists(HtmlView::class)) {
    $viewFile = JPATH_SITE . '/components/com_sportsmanagement/src/View/Imagehandler/HtmlView.php';

    if (is_file($viewFile)) {
        require_once $viewFile;
    }
}

if (class_exists(HtmlView::class) && !class_exists('sportsmanagementViewImagehandler', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewImagehandler');
}
