<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Leagues;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementViewLeagues')) { \JLoader::import('components.com_sportsmanagement.views.leagues.view.html', JPATH_ADMINISTRATOR); }
final class HtmlView extends \sportsmanagementViewLeagues { public function __construct($config = []) { $config['template_path'] = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/leagues/tmpl'; parent::__construct($config); } }
