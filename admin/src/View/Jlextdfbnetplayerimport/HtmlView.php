<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextdfbnetplayerimport;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('jlextdfbnetplayerimport');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/jlextdfbnetplayerimport/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjlextdfbnetplayerimport', __NAMESPACE__ . '\\HtmlView');
}
