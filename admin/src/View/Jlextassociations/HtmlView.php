<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextassociations;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('jlextassociations');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/jlextassociations/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjlextassociations', __NAMESPACE__ . '\\HtmlView');
}
