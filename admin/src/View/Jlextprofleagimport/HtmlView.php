<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextprofleagimport;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('jlextprofleagimport');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/jlextprofleagimport/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjlextprofleagimport', __NAMESPACE__ . '\\HtmlView');
}
