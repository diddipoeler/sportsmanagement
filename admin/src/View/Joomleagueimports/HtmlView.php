<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Joomleagueimports;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('joomleagueimports');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/joomleagueimports/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjoomleagueimports', __NAMESPACE__ . '\\HtmlView');
}
