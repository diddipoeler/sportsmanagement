<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Installhelper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('installhelper');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/installhelper/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewinstallhelper', __NAMESPACE__ . '\\HtmlView');
}
