<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\About;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
LegacyBootstrap::bootForView('about');
if (!class_exists('sportsmanagementViewAbout')) { \JLoader::import('components.com_sportsmanagement.views.about.view.html', JPATH_SITE); }
final class HtmlView extends \sportsmanagementViewAbout { public function __construct($config = []) { $config['template_path'] = JPATH_SITE . '/components/com_sportsmanagement/views/about/tmpl'; parent::__construct($config); } }
