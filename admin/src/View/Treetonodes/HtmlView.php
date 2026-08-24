<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Treetonodes;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('treetonodes');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/treetonodes/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewtreetonodes', __NAMESPACE__ . '\\HtmlView');
}
