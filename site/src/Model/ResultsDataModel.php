<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Throwable;

/**
 * Native Joomla 5/6 reader for the regular results view.
 *
 * Match list/edit/ACL behaviour remains in the legacy Results model for now.
 * Project, round, team, event and referee-position reads live here so they use
 * the selected SportsManagement database through SportsManagementModel.
 */
final class ResultsDataModel extends SportsManagementProjectModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
    }

    public function getRoundCode(int $roundId): string
    {
        if ($roundId <= 0) {
            return '';
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('roundcode'))
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = ' . $roundId);

        if ($this->projectId > 0) {
            $query->where($db->quoteName('project_id') . ' = ' . $this->projectId);
        }

        try {
            $db->setQuery($query, 0, 1);
            return (string) ($db->loadResult() ?? '');
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return '';
        }
    }

    public function getRoundOptions(string $ordering = 'ASC'): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $direction = strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';
        $db = $this->getDatabase();
        $matchdayName = Text::_('COM_SPORTSMANAGEMENT_MATCHDAY_NAME');
        $query = $db->getQuery(true)
            ->select([
                "CONCAT_WS(':', id, alias) AS slug",
                $db->quoteName('id', 'value'),
                "CASE LENGTH(name) WHEN 0 THEN CONCAT(" . $db->quote($matchdayName) . ", ' ', id) ELSE CONCAT(name, ' (', round_date_first, ')') END AS text",
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $this->projectId)
            ->order($db->quoteName('roundcode') . ' ' . $direction);

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /** Preserve the legacy id-keyed event list used by results templates. */
    public function getProjectEvents(int $positionId = 0): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ])
            ->from($db->quoteName('#__sportsmanagement_eventtype', 'et'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_position_eventtype', 'pet')
                . ' ON ' . $db->quoteName('pet.eventtype_id') . ' = ' . $db->quoteName('et.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pet.position_id')
            )
            ->where($db->quoteName('ppos.project_id') . ' = ' . $this->projectId)
            ->group([
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ]);

        if ($positionId > 0) {
            $query->where($db->quoteName('ppos.position_id') . ' = ' . $positionId);
        }

        try {
            $db->setQuery($query);
            return $db->loadObjectList('id') ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    public function getProjectTeamsIndexed(int $divisionId = 0): array
    {
        $teams = [];

        foreach ($this->getProjectTeams($divisionId) as $team) {
            $projectTeamId = (int) ($team->projectteamid ?? 0);
            if ($projectTeamId > 0) {
                $teams[$projectTeamId] = $team;
            }
        }

        return $teams;
    }

    /**
     * Return project position options in the same shape as the legacy
     * sportsmanagementModelMatch::getProjectPositionsOptions() helper.
     */
    public function getProjectPositionsOptions(
        int $positionId = 0,
        int $personType = 1,
        int $projectId = 0
    ): array {
        $projectId = $projectId > 0 ? $projectId : $this->projectId;

        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('ppos.id', 'value'),
                $db->quoteName('pos.name', 'text'),
                $db->quoteName('pos.id', 'posid'),
                $db->quoteName('pos.id', 'pposid'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pos.id')
            )
            ->where($db->quoteName('ppos.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('pos.persontype') . ' = ' . $personType)
            ->order($db->quoteName('pos.ordering') . ' ASC');

        if ($positionId > 0) {
            $query->where($db->quoteName('ppos.position_id') . ' = ' . $positionId);
        }

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    private function reportDatabaseError(Throwable $e): void
    {
        Factory::getApplication()->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
            'error'
        );
    }
}
