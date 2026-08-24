<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Extensions;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('extensions');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/extensions/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewextensions', __NAMESPACE__ . '\\HtmlView');
}
