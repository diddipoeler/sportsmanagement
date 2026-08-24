<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Treetomatchs;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('treetomatchs');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/treetomatchs/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewtreetomatchs', __NAMESPACE__ . '\\HtmlView');
}
