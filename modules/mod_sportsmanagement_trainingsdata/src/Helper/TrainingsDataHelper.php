<?php
namespace Diddipoeler\Module\SportsManagementTrainingsData\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
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
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);

        return SportsManagementDatabaseResolver::resolve(
            $joomlaDatabase,
            (int) $params->get('cfg_which_database', 0)
        );
    }
}
