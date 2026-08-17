<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementModelextrafields')) { \JLoader::import('components.com_sportsmanagement.models.extrafields', JPATH_ADMINISTRATOR); }
final class ExtrafieldsModel extends \sportsmanagementModelextrafields {}
