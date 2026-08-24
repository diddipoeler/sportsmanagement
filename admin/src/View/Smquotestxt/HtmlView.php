<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Smquotestxt;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('smquotestxt');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/smquotestxt/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewsmquotestxt', __NAMESPACE__ . '\\HtmlView');
}
