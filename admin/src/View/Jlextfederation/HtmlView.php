<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextfederation;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('jlextfederation');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/jlextfederation/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjlextfederation', __NAMESPACE__ . '\\HtmlView');
}
