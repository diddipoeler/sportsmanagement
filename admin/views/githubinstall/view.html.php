<?php
/** Legacy compatibility bridge for the native Joomla 5/6 GitHub install view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Githubinstall\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Githubinstall/HtmlView.php';
}

if (!class_exists('sportsmanagementViewgithubinstall', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewgithubinstall');
}
