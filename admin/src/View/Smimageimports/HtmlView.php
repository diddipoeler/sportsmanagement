<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Smimageimports;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('smimageimports');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/smimageimports/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewsmimageimports', __NAMESPACE__ . '\\HtmlView');
}
