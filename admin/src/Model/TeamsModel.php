<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementModelTeams')) { \JLoader::import('components.com_sportsmanagement.models.teams', JPATH_ADMINISTRATOR); }
final class TeamsModel extends \sportsmanagementModelTeams {}
