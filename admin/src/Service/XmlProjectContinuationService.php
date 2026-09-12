<?php
/**
 * Joomla 5/6 native project XML import continuation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use RuntimeException;

/**
 * Continues a parsed project import with native writers and finalisation.
 */
final class XmlProjectContinuationService
{
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     * @param array<string, array<int, int>> $maps
     * @param array<string, string> $messages
     *
     * @return array<string, mixed>
     */
    public function import(
        array $post,
        array $parsedData,
        array $maps,
        array $messages,
        string $targetStep,
        int $projectId,
        int $seasonId,
        string $importVersion
    ): array {
        if ($projectId <= 0) {
            throw new RuntimeException('Missing prepared project for XML import continuation.', 400);
        }

        if (!empty($parsedData['teamtool'])) {
            $parsedData['projectteam'] = array_values((array) $parsedData['teamtool']);
        }

        $personMap = $this->preparedOldIdMap($post, $parsedData, 'person', 'dbPersonID_');
        $positionMap = $this->preparedOldIdMap($post, $parsedData, 'position', 'dbPositionID_');
        $statisticMap = $this->preparedOldIdMap($post, $parsedData, 'statistic', 'dbStatisticID_');
        $teamMap = $this->preparedOldIdMap($post, $parsedData, 'team', 'dbTeamID_');

        if (version_compare($targetStep, '21', 'ge')) {
            $memberResult = (new XmlProjectMemberImportService($this->database))->import(
                $parsedData,
                (array) ($maps['_convertProjectTeamID'] ?? []),
                $personMap,
                (array) ($maps['_convertProjectPositionID'] ?? []),
                $targetStep
            );
            $maps = array_replace($maps, $memberResult['maps']);
            $messages = array_replace($messages, $memberResult['messages']);

            if (version_compare($targetStep, '23', 'ge')) {
                $scheduleResult = (new XmlProjectScheduleImportService($this->database))->import(
                    $parsedData,
                    $projectId,
                    (array) ($maps['_convertProjectTeamID'] ?? []),
                    $targetStep
                );
                $maps = array_replace($maps, $scheduleResult['maps']);
                $messages = array_replace($messages, $scheduleResult['messages']);
            }

            if (version_compare($targetStep, '25', 'ge')) {
                $matchResult = (new XmlProjectMatchImportService($this->database))->import(
                    $parsedData,
                    (array) ($maps['_convertRoundID'] ?? []),
                    (array) ($maps['_convertProjectTeamID'] ?? []),
                    $this->preparedOldIdMap($post, $parsedData, 'playground', 'dbPlaygroundID_'),
                    (array) ($maps['_convertDivisionID'] ?? []),
                    $importVersion
                );
                $maps = array_replace($maps, $matchResult['maps']);
                $messages = array_replace($messages, $matchResult['messages']);
            }

            if (version_compare($targetStep, '26', 'ge')) {
                $detailResult = (new XmlProjectMatchDetailImportService($this->database))->import(
                    $parsedData,
                    (array) ($maps['_convertMatchID'] ?? []),
                    (array) ($maps['_convertTeamPlayerID'] ?? []),
                    (array) ($maps['_convertTeamStaffID'] ?? []),
                    (array) ($maps['_convertProjectRefereeID'] ?? []),
                    (array) ($maps['_convertProjectPositionID'] ?? []),
                    (array) ($maps['_convertProjectTeamID'] ?? []),
                    $this->preparedOldIdMap($post, $parsedData, 'event', 'dbEventID_'),
                    $targetStep
                );
                $messages = array_replace($messages, $detailResult['messages']);
            }

            if (version_compare($targetStep, '30', 'ge')) {
                $positionStatisticResult = (new XmlProjectPositionStatisticImportService($this->database))->import(
                    $parsedData,
                    $positionMap,
                    $statisticMap
                );
                $messages = array_replace($messages, $positionStatisticResult['messages']);
            }

            $this->updateFavoriteTeams($projectId, $parsedData, $teamMap);
            $finalizerResult = (new XmlProjectFinalizerService($this->database))->finalize($projectId, $seasonId);
            $messages = array_replace($messages, $finalizerResult['messages']);
        }

        if (version_compare($targetStep, '31', 'ge')) {
            $statisticResult = (new XmlProjectMatchStatisticImportService($this->database))->import(
                $parsedData,
                $seasonId,
                (array) ($maps['_convertMatchID'] ?? []),
                (array) ($maps['_convertProjectTeamID'] ?? []),
                $personMap,
                $statisticMap,
                $targetStep
            );
            $messages = array_replace($messages, $statisticResult['messages']);
        }

        if (version_compare($targetStep, '33', 'ge')) {
            $tournamentResult = (new XmlProjectTournamentImportService($this->database))->import(
                $parsedData,
                $projectId,
                (array) ($maps['_convertDivisionID'] ?? []),
                (array) ($maps['_convertProjectTeamID'] ?? []),
                (array) ($maps['_convertMatchID'] ?? []),
                $targetStep
            );
            $messages = array_replace($messages, $tournamentResult['messages']);
        }

        if (version_compare($targetStep, '21', 'ge')) {
            $this->normalisePicturePaths();
        }

        return $messages;
    }

    /**
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $teamMap
     */
    private function updateFavoriteTeams(int $projectId, array $parsedData, array $teamMap): void
    {
        if (!isset($parsedData['project']) || !is_object($parsedData['project'])) {
            return;
        }

        $source = trim((string) ($parsedData['project']->fav_team ?? ''));
        $newIds = [];

        if ($source !== '') {
            foreach (explode(',', $source) as $oldId) {
                $oldId = max(0, (int) trim($oldId));

                if ($oldId > 0 && isset($teamMap[$oldId]) && $teamMap[$oldId] > 0) {
                    $newIds[] = (int) $teamMap[$oldId];
                }
            }
        }

        $row = (object) [
            'id' => $projectId,
            'fav_team' => implode(',', array_values(array_unique($newIds))),
        ];

        if (!$this->database->updateObject('#__sportsmanagement_project', $row, 'id')) {
            throw new RuntimeException('Unable to update imported project favorite teams.', 500);
        }
    }

    /**
     * Preserve the historic post-import replacement of JoomLeague image paths
     * without loading the legacy database-tool model.
     */
    private function normalisePicturePaths(): void
    {
        foreach ([
            ['#__sportsmanagement_person', 'picture'],
            ['#__sportsmanagement_playground', 'picture'],
            ['#__sportsmanagement_team', 'picture'],
            ['#__sportsmanagement_club', 'logo_big'],
            ['#__sportsmanagement_club', 'logo_middle'],
            ['#__sportsmanagement_club', 'logo_small'],
            ['#__sportsmanagement_associations', 'picture'],
            ['#__sportsmanagement_associations', 'assocflag'],
            ['#__sportsmanagement_eventtype', 'icon'],
            ['#__sportsmanagement_project_team', 'picture'],
            ['#__sportsmanagement_project_team', 'trikot_home'],
            ['#__sportsmanagement_project_team', 'trikot_away'],
            ['#__sportsmanagement_season_team_person_id', 'picture'],
            ['#__sportsmanagement_season_person_id', 'picture'],
        ] as [$table, $field]) {
            $columns = $this->database->getTableColumns($table);

            if (!array_key_exists($field, $columns)) {
                continue;
            }

            $quotedField = $this->database->quoteName($field);
            $query = $this->database->createQuery()
                ->update($this->database->quoteName($table))
                ->set(
                    $quotedField . ' = REPLACE('
                    . $quotedField . ', '
                    . $this->database->quote('com_joomleague') . ', '
                    . $this->database->quote('com_sportsmanagement') . ')'
                )
                ->where($quotedField . ' LIKE ' . $this->database->quote('%com_joomleague%'));
            $this->database->setQuery($query);
            $this->database->execute();
        }
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     * @return array<int, int>
     */
    private function preparedOldIdMap(
        array $post,
        array $parsedData,
        string $collection,
        string $prefix
    ): array {
        $map = [];

        foreach (array_values((array) ($parsedData[$collection] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = (int) ($source->id ?? 0);
            $databaseId = max(0, (int) ($post[$prefix . $key] ?? 0));

            if ($oldId > 0 && $databaseId > 0) {
                $map[$oldId] = $databaseId;
            }
        }

        return $map;
    }
}
