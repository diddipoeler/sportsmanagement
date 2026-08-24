<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Smextxmleditors;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('smextxmleditors');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/smextxmleditors/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewsmextxmleditors', __NAMESPACE__ . '\\HtmlView');
}
