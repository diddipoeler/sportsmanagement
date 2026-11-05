<?php
/**
 * Native Joomla 5/6 ranking map model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;
use Throwable;

/** Load only the club coordinates required by the native ranking map. */
final class RankingMapModel extends SportsManagementProjectModel
{
    /**
     * Initialise ranking map data access via the native project model.
     *
     * Forwards Joomla model configuration and the optional MVC factory to
     * the base project model without recreating legacy database services.
     *
     * @param array $config Joomla MVC model configuration.
     * @param MVCFactoryInterface|null $factory Optional MVC factory.
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
    }

    /**
     * Select the project whose club locations should be plotted.
     *
     * Normalises negative project IDs to zero, which makes getTeams()
     * return an empty list rather than querying an invalid project.
     *
     * @param int $projectId Project record identifier.
     * @return void
     */
    public function setProjectId(int $projectId): void
    {
        $this->projectId = max(0, $projectId);
    }

    /**
     * Read ranking-map teams and their associated club coordinates.
     *
     * Uses Joomla's parameterised query to join project teams, teams and
     * clubs, selecting only scoring participants with location values.
     * Rejects failed queries and filters coordinates outside valid
     * latitude/longitude bounds or at the empty (0,0) location.
     *
     * @return array<int, object> Team and club location records.
     */
    public function getTeams(): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pt.id', 'projectteamid'),
                $db->quoteName('t.id', 'team_id'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('c.id', 'club_id'),
                $db->quoteName('c.latitude'),
                $db->quoteName('c.longitude'),
                $db->quoteName('c.logo_small'),
                $db->quoteName('c.logo_big'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->where($db->quoteName('pt.project_id') . ' = :rankingMapProjectId')
            ->bind(':rankingMapProjectId', $projectId, ParameterType::INTEGER)
            ->where($db->quoteName('pt.is_in_score') . ' = 1')
            ->where($db->quoteName('c.latitude') . ' IS NOT NULL')
            ->where($db->quoteName('c.longitude') . ' IS NOT NULL')
            ->order($db->quoteName('t.name') . ' ASC');

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (Throwable) {
            return [];
        }

        return array_values(array_filter($rows, static function (object $row): bool {
            $latitude = (float) ($row->latitude ?? 0);
            $longitude = (float) ($row->longitude ?? 0);

            return $latitude >= -90.0 && $latitude <= 90.0
                && $longitude >= -180.0 && $longitude <= 180.0
                && ($latitude !== 0.0 || $longitude !== 0.0);
        }));
    }
}
