<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Databasetools;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('databasetools');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/databasetools/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewdatabasetools', __NAMESPACE__ . '\\HtmlView');
}
