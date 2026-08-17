<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Predictionrules;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
LegacyBootstrap::bootForView('predictionrules');
if (!class_exists('sportsmanagementViewPredictionRules')) { \JLoader::import('components.com_sportsmanagement.views.predictionrules.view.html', JPATH_SITE); }
final class HtmlView extends \sportsmanagementViewPredictionRules { public function __construct($config = []) { $config['template_path'] = JPATH_SITE . '/components/com_sportsmanagement/views/predictionrules/tmpl'; parent::__construct($config); } }
