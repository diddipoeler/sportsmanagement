<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Extrafields;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementViewextrafields')) { \JLoader::import('components.com_sportsmanagement.views.extrafields.view.html', JPATH_ADMINISTRATOR); }
final class HtmlView extends \sportsmanagementViewextrafields { public function __construct($config = []) { $config['template_path'] = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/extrafields/tmpl'; parent::__construct($config); } }
