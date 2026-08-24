<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Projectteam;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('projectteam');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/projectteam/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewprojectteam', __NAMESPACE__ . '\\HtmlView');
}
