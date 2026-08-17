<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
LegacyBootstrap::bootForView('teams');
if (!class_exists('sportsmanagementModelTeams')) { \JLoader::import('components.com_sportsmanagement.models.teams', JPATH_SITE); }
final class TeamsModel extends \sportsmanagementModelTeams {}
