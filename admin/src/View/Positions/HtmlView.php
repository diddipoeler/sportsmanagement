<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Positions;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementViewPositions')) { \JLoader::import('components.com_sportsmanagement.views.positions.view.html', JPATH_ADMINISTRATOR); }
final class HtmlView extends \sportsmanagementViewPositions { public function __construct($config = []) { $config['template_path'] = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/positions/tmpl'; parent::__construct($config); } }
