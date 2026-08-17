<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Sportstypes;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementViewSportsTypes')) { \JLoader::import('components.com_sportsmanagement.views.sportstypes.view.html', JPATH_ADMINISTRATOR); }
final class HtmlView extends \sportsmanagementViewSportsTypes { public function __construct($config = []) { $config['template_path'] = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/sportstypes/tmpl'; parent::__construct($config); } }
