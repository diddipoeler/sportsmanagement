<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Referees;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
LegacyBootstrap::bootForView('referees');
if (!class_exists('sportsmanagementViewReferees')) { \JLoader::import('components.com_sportsmanagement.views.referees.view.html', JPATH_SITE); }
final class HtmlView extends \sportsmanagementViewReferees { public function __construct($config = []) { $config['template_path'] = JPATH_SITE . '/components/com_sportsmanagement/views/referees/tmpl'; parent::__construct($config); } }
