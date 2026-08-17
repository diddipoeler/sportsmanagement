<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Seasons;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementViewSeasons')) { \JLoader::import('components.com_sportsmanagement.views.seasons.view.html', JPATH_ADMINISTRATOR); }
final class HtmlView extends \sportsmanagementViewSeasons { public function __construct($config = []) { $config['template_path'] = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/seasons/tmpl'; parent::__construct($config); } }
