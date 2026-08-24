<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Cpanel;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('cpanel');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/cpanel/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewcpanel', __NAMESPACE__ . '\\HtmlView');
}
