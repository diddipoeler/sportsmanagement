<?php
/**
 * Native Joomla 5/6 frontend clubs model.
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

final class ClubsModel extends SportsManagementProjectModel
{
    public static int $projectid = 0;
    public static int $divisionid = 0;
    public static int $cfg_which_database = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$projectid = $this->projectId;
        self::$divisionid = $this->divisionId;
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0);
    }

    public function getClubs($ordering = null): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $projectId = $this->projectId;
        $divisionIds = array_values(array_filter(
            array_map('intval', $this->getDivisionTreeIds()),
            static fn (int $id): bool => $id > 0
        ));
        $clubQuery = $db->createQuery();
        $clubDivisionIds = $divisionIds;
        $clubDivisionPlaceholders = $clubDivisionIds !== []
            ? $clubQuery->bindArray($clubDivisionIds, ParameterType::INTEGER)
            : [];

        $exists = $db->createQuery()
            ->select('1')
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->where($db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id'))
            ->where($db->quoteName('pt.project_id') . ' = :clubProjectId');

        if ($clubDivisionPlaceholders !== []) {
            $exists->where($db->quoteName('pt.division_id') . ' IN (' . implode(',', $clubDivisionPlaceholders) . ')');
        }

        $clubQuery
            ->select(['c.*', "CONCAT_WS(':', c.id, c.alias) AS club_slug"])
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->where('EXISTS (' . $exists . ')')
            ->order($this->normaliseOrdering($ordering, 'c.name'))
            ->bind(':clubProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($clubQuery);
        $clubs = $db->loadObjectList() ?: [];

        if (!$clubs) {
            return [];
        }

        $teamQuery = $db->createQuery()
            ->select([
                't.*',
                $db->quoteName('t.picture', 'team_picture'),
                $db->quoteName('pt.picture', 'projectteam_picture'),
                $db->quoteName('pt.division_id'),
                "CONCAT_WS(':', t.id, t.alias) AS team_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->where($db->quoteName('pt.project_id') . ' = :teamProjectId')
            ->order($db->quoteName('t.name') . ' ASC')
            ->bind(':teamProjectId', $projectId, ParameterType::INTEGER);

        if ($divisionIds !== []) {
            $teamDivisionIds = $divisionIds;
            $teamDivisionPlaceholders = $teamQuery->bindArray($teamDivisionIds, ParameterType::INTEGER);
            $teamQuery->where($db->quoteName('pt.division_id') . ' IN (' . implode(',', $teamDivisionPlaceholders) . ')');
        }

        $db->setQuery($teamQuery);
        $teams = $db->loadObjectList() ?: [];
        $teamsByClub = [];

        foreach ($teams as $team) {
            $teamsByClub[(int) $team->club_id][] = $team;
        }

        foreach ($clubs as $club) {
            $club->teams = $teamsByClub[(int) $club->id] ?? [];
        }

        return $clubs;
    }

    private function normaliseOrdering($ordering, string $default): string
    {
        $ordering = trim((string) $ordering);

        if ($ordering === '') {
            return $default . ' ASC';
        }

        if (!preg_match('/^(?:c\.)?([A-Za-z_][A-Za-z0-9_]*)(?:\s+(ASC|DESC))?$/i', $ordering, $match)) {
            return $default . ' ASC';
        }

        $direction = strtoupper((string) ($match[2] ?? 'ASC'));

        return 'c.' . $match[1] . ' ' . $direction;
    }
}
