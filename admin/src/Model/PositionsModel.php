<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementModelPositions')) { \JLoader::import('components.com_sportsmanagement.models.positions', JPATH_ADMINISTRATOR); }
final class PositionsModel extends \sportsmanagementModelPositions {}
