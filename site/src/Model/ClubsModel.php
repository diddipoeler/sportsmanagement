<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
LegacyBootstrap::bootForView('clubs');
if (!class_exists('sportsmanagementModelClubs')) { \JLoader::import('components.com_sportsmanagement.models.clubs', JPATH_SITE); }
final class ClubsModel extends \sportsmanagementModelClubs {}
