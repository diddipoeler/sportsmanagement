<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Eventtypes;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementViewEventtypes')) { \JLoader::import('components.com_sportsmanagement.views.eventtypes.view.html', JPATH_ADMINISTRATOR); }
final class HtmlView extends \sportsmanagementViewEventtypes { public function __construct($config = []) { $config['template_path'] = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/eventtypes/tmpl'; parent::__construct($config); } }
