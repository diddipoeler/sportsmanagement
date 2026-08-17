<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Clubs;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
LegacyBootstrap::bootForView('clubs');
if (!class_exists('sportsmanagementViewClubs')) { \JLoader::import('components.com_sportsmanagement.views.clubs.view.html', JPATH_SITE); }
final class HtmlView extends \sportsmanagementViewClubs { public function __construct($config = []) { $config['template_path'] = JPATH_SITE . '/components/com_sportsmanagement/views/clubs/tmpl'; parent::__construct($config); } }
