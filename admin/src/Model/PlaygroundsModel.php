<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementModelPlaygrounds')) { \JLoader::import('components.com_sportsmanagement.models.playgrounds', JPATH_ADMINISTRATOR); }
final class PlaygroundsModel extends \sportsmanagementModelPlaygrounds {}
