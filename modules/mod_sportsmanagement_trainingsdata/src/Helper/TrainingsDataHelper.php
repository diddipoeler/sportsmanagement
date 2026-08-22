<?php
namespace Diddipoeler\Module\SportsManagementTrainingsData\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class TrainingsDataHelper
{
    public function getData(Registry $params): array
    {
        $teamId = (int) $params->get('teams', 0);

        if ($teamId <= 0) {
            return [];
        }

        $db = $this->database($params);
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('dayofweek'),
                $db->quoteName('time_start'),
                $db->quoteName('time_end'),
                $db->quoteName('place'),
                $db->quoteName('notes'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team_trainingdata'))
            ->where($db->quoteName('team_id') . ' = ' . $teamId)
            ->order([
                $db->quoteName('dayofweek') . ' ASC',
                $db->quoteName('time_start') . ' ASC',
            ]);
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    private function database(Registry $params): DatabaseInterface
    {
        $helperFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';

        if (!class_exists('sportsmanagementHelper', false) && is_file($helperFile)) {
            require_once $helperFile;
        }

        if (class_exists('sportsmanagementHelper', false)) {
            try {
                $db = \sportsmanagementHelper::getDBConnection(true, (int) $params->get('cfg_which_database', 0));

                if ($db instanceof DatabaseInterface) {
                    return $db;
                }
            } catch (\Throwable) {
            }
        }

        return Factory::getContainer()->get(DatabaseInterface::class);
    }
}
