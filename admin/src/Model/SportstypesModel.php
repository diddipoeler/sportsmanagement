<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementModelSportsTypes')) { \JLoader::import('components.com_sportsmanagement.models.sportstypes', JPATH_ADMINISTRATOR); }
final class SportstypesModel extends \sportsmanagementModelSportsTypes {}
