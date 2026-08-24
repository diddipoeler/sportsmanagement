<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Template;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('template');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/template/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewtemplate', __NAMESPACE__ . '\\HtmlView');
}
