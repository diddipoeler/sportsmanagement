<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 tournament bracket model.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\TournamentbracketModel;

if (!class_exists(TournamentbracketModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/TournamentbracketModel.php';
}

if (!class_exists('sportsmanagementModeltournamentbracket', false)) {
    class_alias(TournamentbracketModel::class, 'sportsmanagementModeltournamentbracket');
}
