<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
LegacyBootstrap::bootForView('referees');
if (!class_exists('sportsmanagementModelReferees')) { \JLoader::import('components.com_sportsmanagement.models.referees', JPATH_SITE); }
final class RefereesModel extends \sportsmanagementModelReferees {}
