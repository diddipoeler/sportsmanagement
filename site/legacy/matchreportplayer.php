<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PlayerModel;

if (!class_exists(PlayerModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PlayerModel.php';
}

if (!class_exists('sportsmanagementModelPlayer', false)) {
    final class sportsmanagementModelPlayer
    {
        public static function getTeamPlayer($projectId = 0, $personId = 0, $teamPlayerId = 0, $cfgWhichDatabase = null): array
        {
            $model = new PlayerModel();

            if ($cfgWhichDatabase !== null) {
                $model->setDatabaseSelector((int) $cfgWhichDatabase);
            }

            return $model->getTeamPlayer(
                (int) $projectId,
                (int) $personId,
                (int) $teamPlayerId
            );
        }
    }
}
