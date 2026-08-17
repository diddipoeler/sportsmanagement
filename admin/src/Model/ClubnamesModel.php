<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementModelclubnames')) { \JLoader::import('components.com_sportsmanagement.models.clubnames', JPATH_ADMINISTRATOR); }
final class ClubnamesModel extends \sportsmanagementModelclubnames {}
