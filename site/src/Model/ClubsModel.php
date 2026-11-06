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

    /**
     * Initialise the clubs listing using current project and division.
     * Stores legacy static context values for existing templates.
     * @param array $config Joomla model configuration.
     * @param MVCFactoryInterface|null $factory Optional MVC factory.
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$projectid = $this->projectId;
        self::$divisionid = $this->divisionId;
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0);
    }

    /**
     * Read club records belonging to the selected project and divisions.
     * Accepts a configured ordering expression and applies the project
     * and division restrictions through Joomla database queries.
     * @param string|null $ordering Optional club list sort expression.
     * @return array<int,object> Project club records.
     */
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

        $exists = $db->createQuery()
            ->select('1')
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->where($db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id'))
            ->where($db->quoteName('pt.project_id') . ' = :clubProjectId');

        if ($divisionIds !== []) {
            $exists->whereIn($db->quoteName('pt.division_id'), $divisionIds, ParameterType::INTEGER);
        }

        $clubQuery
            ->select([
                $db->quoteName('c') . '.*',
                "CONCAT_WS(':', " . $db->quoteName('c.id') . ', ' . $db->quoteName('c.alias') . ') AS ' . $db->quoteName('club_slug'),
            ])
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
                $db->quoteName('t') . '.*',
                $db->quoteName('t.picture', 'team_picture'),
                $db->quoteName('pt.picture', 'projectteam_picture'),
                $db->quoteName('pt.division_id'),
                "CONCAT_WS(':', " . $db->quoteName('t.id') . ', ' . $db->quoteName('t.alias') . ') AS ' . $db->quoteName('team_slug'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->where($db->quoteName('pt.project_id') . ' = :teamProjectId')
            ->order($db->quoteName('t.name') . ' ASC')
            ->bind(':teamProjectId', $projectId, ParameterType::INTEGER);

        if ($divisionIds !== []) {
            $teamQuery->whereIn($db->quoteName('pt.division_id'), $divisionIds, ParameterType::INTEGER);
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

    /**
     * Normalise a club list ordering expression into column and direction.
     * Falls back to the default ordering on malformed input.
     * @param mixed $ordering Requested sort expression.
     * @param string $default Default club database column.
     * @return string Safe ordering expression.
     */
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
