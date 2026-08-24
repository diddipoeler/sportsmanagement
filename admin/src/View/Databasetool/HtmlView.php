<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Databasetool;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('databasetool');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/databasetool/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewdatabasetool', __NAMESPACE__ . '\\HtmlView');
}
