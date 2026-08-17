<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementModelEventtypes')) { \JLoader::import('components.com_sportsmanagement.models.eventtypes', JPATH_ADMINISTRATOR); }
final class EventtypesModel extends \sportsmanagementModelEventtypes {}
