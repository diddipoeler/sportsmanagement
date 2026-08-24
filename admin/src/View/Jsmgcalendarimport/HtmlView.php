<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jsmgcalendarimport;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('jsmgcalendarimport');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/jsmgcalendarimport/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjsmgcalendarimport', __NAMESPACE__ . '\\HtmlView');
}
