<?php
/**
 * Native Joomla 5/6 reader for project data used by the combined results/ranking view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;
use Throwable;

/**
 * Native Joomla 5/6 reader for project data used by the combined
 * results/ranking view. Ranking and results calculations stay in their
 * legacy models until those models are migrated separately.
 */
final class ResultsrankingDataModel extends SportsManagementProjectModel
{
    /**
     * Initialise the Joomla results and ranking reader with project context.
     * @param array $config Joomla MVC model configuration.
     * @param MVCFactoryInterface|null $factory Optional Joomla model factory.
     * @return void
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
    }

    /**
     * Retrieve the round code for a round in the selected project.
     * @param int $roundId Requested round identifier.
     * @return string Round code or an empty string when unavailable.
     */
    public function getRoundCode(int $roundId): string
    {
        if ($roundId <= 0 || $this->projectId <= 0) {
            return '';
        }

        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('roundcode'))
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = :roundCodeId')
            ->where($db->quoteName('project_id') . ' = :roundCodeProjectId')
            ->bind(':roundCodeId', $roundId, ParameterType::INTEGER)
            ->bind(':roundCodeProjectId', $projectId, ParameterType::INTEGER);

        try {
            $db->setQuery($query, 0, 1);
            return (string) ($db->loadResult() ?? '');
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return '';
        }
    }

    /**
     * Build the Joomla route slug of a round belonging to the project.
     * @param int $roundId Requested round identifier.
     * @return string Round ID and alias slug, or an empty string.
     */
    public function getRoundSlug(int $roundId): string
    {
        if ($roundId <= 0 || $this->projectId <= 0) {
            return '';
        }

        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select("CONCAT_WS(':', " . $db->quoteName('id') . ', ' . $db->quoteName('alias') . ')')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = :roundSlugId')
            ->where($db->quoteName('project_id') . ' = :roundSlugProjectId')
            ->bind(':roundSlugId', $roundId, ParameterType::INTEGER)
            ->bind(':roundSlugProjectId', $projectId, ParameterType::INTEGER);

        try {
            $db->setQuery($query, 0, 1);
            return (string) ($db->loadResult() ?? '');
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return '';
        }
    }

    /**
     * List project rounds with translated labels for a selection field.
     * @param string $ordering Round-code ordering; ASC or DESC.
     * @return array<int,object> Select-option records.
     */
    public function getRoundOptions(string $ordering = 'ASC'): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $projectId = $this->projectId;
        $direction = strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';
        $db = $this->getDatabase();
        $matchdayName = Text::_('COM_SPORTSMANAGEMENT_MATCHDAY_NAME');
        $query = $db->createQuery()
            ->select([
                "CONCAT_WS(':', " . $db->quoteName('id') . ', ' . $db->quoteName('alias') . ') AS ' . $db->quoteName('slug'),
                $db->quoteName('id', 'value'),
                'CASE LENGTH(' . $db->quoteName('name') . ') WHEN 0 THEN CONCAT(' . $db->quote($matchdayName) . ", ' ', " . $db->quoteName('id') . ') ELSE CONCAT(' . $db->quoteName('name') . ", ' (', " . $db->quoteName('round_date_first') . ", ')') END AS " . $db->quoteName('text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = :roundOptionsProjectId')
            ->order($db->quoteName('roundcode') . ' ' . $direction)
            ->bind(':roundOptionsProjectId', $projectId, ParameterType::INTEGER);

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

        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('d') . '.*')
            ->from($db->quoteName('#__sportsmanagement_division', 'd'))
            ->where($db->quoteName('d.project_id') . ' = :divisionProjectId')
            ->where($db->quoteName('d.published') . ' = 1')
            ->order($db->quoteName('d.ordering') . ' ASC')
            ->bind(':divisionProjectId', $projectId, ParameterType::INTEGER);

        if ($divisionLevel === 1) {
            $query->where('(' . $db->quoteName('d.parent_id') . ' = 0 OR ' . $db->quoteName('d.parent_id') . ' IS NULL)');
        } elseif ($divisionLevel === 2) {
            $query->where($db->quoteName('d.parent_id') . ' > 0');
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

        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
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
            ->where($db->quoteName('ppos.project_id') . ' = :eventProjectId')
            ->group([
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ])
            ->bind(':eventProjectId', $projectId, ParameterType::INTEGER);

        if ($positionId > 0) {
            $query->where($db->quoteName('ppos.position_id') . ' = :eventPositionId')
                ->bind(':eventPositionId', $positionId, ParameterType::INTEGER);
        }

        try {
            $db->setQuery($query);
            return $db->loadObjectList('id') ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /**
     * Index the selected project's team records by project-team ID.
     * @param int $divisionId Optional division filter.
     * @return array<int,object> Project-team rows keyed by project-team ID.
     */
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

    /**
     * Log results/ranking database exceptions without disclosing SQL to visitors.
     * @param Throwable $e Original database query exception.
     * @return void
     */
    private function reportDatabaseError(Throwable $e): void
    {
        \Joomla\CMS\Log\Log::add(
            'SportsManagement results/ranking query failed: ' . $e->getMessage(),
            \Joomla\CMS\Log\Log::ERROR,
            'com_sportsmanagement'
        );
        $this->siteApplication()->enqueueMessage(
            Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
            'error'
        );
    }
}
