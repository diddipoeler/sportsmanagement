<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Playgrounds;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementViewPlaygrounds')) { \JLoader::import('components.com_sportsmanagement.views.playgrounds.view.html', JPATH_ADMINISTRATOR); }
final class HtmlView extends \sportsmanagementViewPlaygrounds { public function __construct($config = []) { $config['template_path'] = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/playgrounds/tmpl'; parent::__construct($config); } }
