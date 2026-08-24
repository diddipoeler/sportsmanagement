<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Treetos;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('treetos');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/treetos/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewtreetos', __NAMESPACE__ . '\\HtmlView');
}
