<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Teams;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementViewTeams')) { \JLoader::import('components.com_sportsmanagement.views.teams.view.html', JPATH_ADMINISTRATOR); }
final class HtmlView extends \sportsmanagementViewTeams { public function __construct($config = []) { $config['template_path'] = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/teams/tmpl'; parent::__construct($config); } }
