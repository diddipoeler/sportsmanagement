<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextcountry;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('jlextcountry');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/jlextcountry/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjlextcountry', __NAMESPACE__ . '\\HtmlView');
}
