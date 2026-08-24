<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Treetonode;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('treetonode');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/treetonode/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewtreetonode', __NAMESPACE__ . '\\HtmlView');
}
