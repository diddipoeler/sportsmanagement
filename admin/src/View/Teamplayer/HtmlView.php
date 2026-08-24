<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Teamplayer;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('teamplayer');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/teamplayer/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewteamplayer', __NAMESPACE__ . '\\HtmlView');
}
