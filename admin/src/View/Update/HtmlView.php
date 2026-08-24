<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Update;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('update');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/update/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewupdate', __NAMESPACE__ . '\\HtmlView');
}
