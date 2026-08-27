<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Throwable;

/**
 * Native Joomla 5/6 reader for project data used by the combined
 * results/ranking view. Ranking and results calculations stay in their
 * legacy models until those models are migrated separately.
 */
final class ResultsrankingDataModel extends SportsManagementProjectModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
    }

    public function getRoundCode(int $roundId): string
    {
        if ($roundId <= 0 || $this->projectId <= 0) {
            return '';
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('roundcode'))
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = ' . $roundId)
            ->where($db->quoteName('project_id') . ' = ' . $this->projectId);

        try {
            $db->setQuery($query, 0, 1);
            return (string) ($db->loadResult() ?? '');
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return '';
        }
    }

    public function getRoundSlug(int $roundId): string
    {
        if ($roundId <= 0 || $this->projectId <= 0) {
            return '';
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select("CONCAT_WS(':', id, alias)")
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = ' . $roundId)
            ->where($db->quoteName('project_id') . ' = ' . $this->projectId);

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

    /**
     * Preserve the legacy return contract: divisions are keyed by id and are
     * only returned for DIVISIONS_LEAGUE projects.
     */
    public function getDivisions(int $divisionLevel = 0): array
    {
        $project = $this->getProject();
        if (!$project || ($project->project_type ?? '') !== 'DIVISIONS_LEAGUE') {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('project_id') . ' = ' . $this->projectId)
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('ordering') . ' ASC');

        if ($divisionLevel === 1) {
            $query->where('(' . $db->quoteName('parent_id') . ' = 0 OR ' . $db->quoteName('parent_id') . ' IS NULL)');
        } elseif ($divisionLevel === 2) {
            $query->where($db->quoteName('parent_id') . ' > 0');
        }

        try {
            $db->setQuery($query);
            return $db->loadObjectList('id') ?: [];
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

    /** Preserve the legacy ranking color configuration structure. */
    public function parseColors(string $configColors = ''): array
    {
        $colors = [[
            'from' => '',
            'to' => '',
            'color' => '',
            'description' => '',
        ]];

        if (trim($configColors) === '') {
            return $colors;
        }

        foreach (explode(';', $configColors) as $index => $entry) {
            $parts = explode(',', $entry);
            if (count($parts) !== 4) {
                break;
            }

            $colors[$index] = [
                'from' => $parts[0],
                'to' => $parts[1],
                'color' => $parts[2],
                'description' => $parts[3],
            ];
        }

        return $colors;
    }

    private function reportDatabaseError(Throwable $e): void
    {
        Factory::getApplication()->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
            'error'
        );
    }
}
