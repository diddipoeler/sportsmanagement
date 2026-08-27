<?php
/**
 * Roster compatibility adapter for the legacy global Player model name.
 *
 * Roster templates only need participation/time calculations from the old
 * Player model. Keep those calls compatible without loading the 41 KB legacy
 * Player calculation/statistics model.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\PlayerTimeModel;

if (!class_exists(PlayerTimeModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PlayerTimeModel.php';
}

if (!class_exists('sportsmanagementModelPlayer', false)) {
    final class sportsmanagementModelPlayer
    {
        public static function getTimePlayed(
            $playerId,
            $gameRegularTime,
            $matchId = null,
            $cards = null,
            $projectId = 0,
            $addTime = 0
        ) {
            $model = new PlayerTimeModel();

            return $model->getTimePlayed(
                (int) $playerId,
                (int) $gameRegularTime,
                $matchId === null ? null : (int) $matchId,
                is_array($cards) ? $cards : null,
                (int) $projectId,
                (int) $addTime
            );
        }

        public static function getInOutStats(
            $projectId = 0,
            $projectTeamId = 0,
            $teamPlayerId = 0,
            $gameRegularTime = 90,
            $matchId = 0,
            $cfgWhichDatabase = 0,
            $teamId = 0,
            $personId = 0
        ): object {
            $model = new PlayerTimeModel();
            $model->setDatabaseSelector((int) $cfgWhichDatabase);

            return $model->getInOutStats(
                (int) $projectId,
                (int) $projectTeamId,
                (int) $teamPlayerId,
                (int) $gameRegularTime,
                (int) $matchId,
                (int) $cfgWhichDatabase,
                (int) $teamId,
                (int) $personId
            );
        }
    }
}
