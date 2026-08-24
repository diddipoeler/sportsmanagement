<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextfederations;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('jlextfederations');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/jlextfederations/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjlextfederations', __NAMESPACE__ . '\\HtmlView');
}
