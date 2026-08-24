<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Smextxmleditor;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('smextxmleditor');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/smextxmleditor/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewsmextxmleditor', __NAMESPACE__ . '\\HtmlView');
}
