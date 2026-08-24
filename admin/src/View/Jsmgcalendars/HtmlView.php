<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jsmgcalendars;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('jsmgcalendars');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/jsmgcalendars/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjsmgcalendars', __NAMESPACE__ . '\\HtmlView');
}
