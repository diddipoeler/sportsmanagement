<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementModelrosterpositions')) { \JLoader::import('components.com_sportsmanagement.models.rosterpositions', JPATH_ADMINISTRATOR); }
final class RosterpositionsModel extends \sportsmanagementModelrosterpositions {}
