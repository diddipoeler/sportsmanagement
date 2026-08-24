<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Smquotetxt;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('smquotetxt');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/smquotetxt/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewsmquotetxt', __NAMESPACE__ . '\\HtmlView');
}
