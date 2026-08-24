<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Updates;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('updates');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/updates/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewupdates', __NAMESPACE__ . '\\HtmlView');
}
