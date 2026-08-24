<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Joomleagueimport;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('joomleagueimport');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/joomleagueimport/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjoomleagueimport', __NAMESPACE__ . '\\HtmlView');
}
