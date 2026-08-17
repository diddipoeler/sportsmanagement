<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementModelSeasons')) { \JLoader::import('components.com_sportsmanagement.models.seasons', JPATH_ADMINISTRATOR); }
final class SeasonsModel extends \sportsmanagementModelSeasons {}
