<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Rosterpositions;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementViewrosterpositions')) { \JLoader::import('components.com_sportsmanagement.views.rosterpositions.view.html', JPATH_ADMINISTRATOR); }
final class HtmlView extends \sportsmanagementViewrosterpositions { public function __construct($config = []) { $config['template_path'] = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/rosterpositions/tmpl'; parent::__construct($config); } }
