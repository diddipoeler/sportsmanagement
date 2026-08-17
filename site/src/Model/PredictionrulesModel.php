<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
LegacyBootstrap::bootForView('predictionrules');
if (!class_exists('sportsmanagementModelPredictionRules')) { \JLoader::import('components.com_sportsmanagement.models.predictionrules', JPATH_SITE); }
final class PredictionrulesModel extends \sportsmanagementModelPredictionRules {}
