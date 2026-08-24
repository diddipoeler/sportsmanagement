<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Sportsmanagement;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('sportsmanagement');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/sportsmanagement/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewsportsmanagement', __NAMESPACE__ . '\\HtmlView');
}
