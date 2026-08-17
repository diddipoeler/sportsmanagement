<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Teams;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
LegacyBootstrap::bootForView('teams');
if (!class_exists('sportsmanagementViewTeams')) { \JLoader::import('components.com_sportsmanagement.views.teams.view.html', JPATH_SITE); }
final class HtmlView extends \sportsmanagementViewTeams { public function __construct($config = []) { $config['template_path'] = JPATH_SITE . '/components/com_sportsmanagement/views/teams/tmpl'; parent::__construct($config); } }
