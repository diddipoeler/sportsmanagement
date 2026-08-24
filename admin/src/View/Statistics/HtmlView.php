<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Statistics;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('statistics');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/statistics/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewstatistics', __NAMESPACE__ . '\\HtmlView');
}
