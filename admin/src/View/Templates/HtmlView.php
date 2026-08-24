<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Templates;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('templates');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/templates/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewtemplates', __NAMESPACE__ . '\\HtmlView');
}
