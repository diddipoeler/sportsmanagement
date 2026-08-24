<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Treeto;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('treeto');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/treeto/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewtreeto', __NAMESPACE__ . '\\HtmlView');
}
