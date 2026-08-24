<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Imagelist;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('imagelist');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/imagelist/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewimagelist', __NAMESPACE__ . '\\HtmlView');
}
