<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Imagehandler;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('imagehandler');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/imagehandler/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewimagehandler', __NAMESPACE__ . '\\HtmlView');
}
