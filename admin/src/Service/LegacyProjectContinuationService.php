<?php
/**
 * Joomla 5/6 bridge for the remaining legacy project parser.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;
use RuntimeException;

/**
 * Continue a normal project import with native writers/finalisation.
 *
 * Only the historical public getData() parser is still used here. No legacy
 * project writer or finalizer is invoked.
 */
final class LegacyProjectContinuationService
{
    /**
     * @param array<string, mixed> $post
     * @param array<string, array<int, int>> $maps
     * @param array<string, string> $messages
     *
     * @return array<string, mixed>
     */
    public function continue(
        object $legacy,
        array $post,
        array $maps,
        array $messages,
        string $targetStep
    ): array {
        $parsedData = $legacy->getData($post);

        if (!is_array($parsedData)) {
            throw new RuntimeException('Unable to load XML data for the project import continuation.', 500);
        }

        if (!empty($parsedData['teamtool'])) {
            $parsedData['projectteam'] = array_values((array) $parsedData['teamtool']);
        }

        $database = $legacy->getDbo();

        if (!$database instanceof DatabaseInterface) {
            throw new RuntimeException('XML continuation database is unavailable.', 500);
        }

        $projectId = max(0, (int) ($legacy->_project_id ?? 0));
        $seasonId = max(0, (int) ($post['season'] ?? ($post['filter_season'] ?? 0)));
        $personMap = $this->preparedOldIdMap($post, $parsedData, 'person', 'dbPersonID_');
        $positionMap = $this->preparedOldIdMap($post, $parsedData, 'position', 'dbPositionID_');
        $statisticMap = $this->preparedOldIdMap($post, $parsedData, 'statistic', 'dbStatisticID_');
        $teamMap = $this->preparedOldIdMap($post, $parsedData, 'team', 'dbTeamID_');

        if (version_compare($targetStep, '21', 'ge')) {
            $memberResult = (new XmlProjectMemberImportService($database))->import(
                $parsedData,
                (array) ($maps['_convertProjectTeamID'] ?? []),
                $personMap,
                (array) ($maps['_convertProjectPositionID'] ?? []),
                $targetStep
            );
            $maps = array_replace($maps, $memberResult['maps']);
            $messages = array_replace($messages, $memberResult['messages']);

            if (version_compare($targetStep, '23', 'ge')) {
                $scheduleResult = (new XmlProjectScheduleImportService($database))->import(
                    $parsedData,
                    $projectId,
                    (array) ($maps['_convertProjectTeamID'] ?? []),
                    $targetStep
                );
                $maps = array_replace($maps, $scheduleResult['maps']);
                $messages = array_replace($messages, $scheduleResult['messages']);
            }

            if (version_compare($targetStep, '25', 'ge')) {
                $matchResult = (new XmlProjectMatchImportService($database))->import(
                    $parsedData,
                    (array) ($maps['_convertRoundID'] ?? []),
                    (array) ($maps['_convertProjectTeamID'] ?? []),
                    $this->preparedOldIdMap($post, $parsedData, 'playground', 'dbPlaygroundID_'),
                    (array) ($maps['_convertDivisionID'] ?? []),
                    (string) ($legacy->import_version ?? '')
                );
                $maps = array_replace($maps, $matchResult['maps']);
                $messages = array_replace($messages, $matchResult['messages']);
            }

            if (version_compare($targetStep, '26', 'ge')) {
                $detailResult = (new XmlProjectMatchDetailImportService($database))->import(
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
                $positionStatisticResult = (new XmlProjectPositionStatisticImportService($database))->import(
                    $parsedData,
                    $positionMap,
                    $statisticMap
                );
                $messages = array_replace($messages, $positionStatisticResult['messages']);
            }

            $this->updateFavoriteTeams($database, $projectId, $parsedData, $teamMap);
            $finalizerResult = (new XmlProjectFinalizerService($database))->finalize($projectId, $seasonId);
            $messages = array_replace($messages, $finalizerResult['messages']);
        }

        if (version_compare($targetStep, '31', 'ge')) {
            $statisticResult = (new XmlProjectMatchStatisticImportService($database))->import(
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
            $tournamentResult = (new XmlProjectTournamentImportService($database))->import(
                $parsedData,
                $projectId,
                (array) ($maps['_convertDivisionID'] ?? []),
                (array) ($maps['_convertProjectTeamID'] ?? []),
                (array) ($maps['_convertMatchID'] ?? []),
                $targetStep
            );
            $maps = array_replace($maps, $tournamentResult['maps']);
            $messages = array_replace($messages, $tournamentResult['messages']);
        }

        if (version_compare($targetStep, '21', 'ge')) {
            $model = BaseDatabaseModel::getInstance('databasetool', 'sportsmanagementModel');

            if ($model && method_exists($model, 'setNewPicturePath')) {
                $model->setNewPicturePath();
            }
        }

        $this->deleteImportFile();

        return $messages;
    }

    /**
     * Restore the historical _beforeFinish() conversion of project fav_team.
     *
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $teamMap
     */
    private function updateFavoriteTeams(
        DatabaseInterface $database,
        int $projectId,
        array $parsedData,
        array $teamMap
    ): void {
        if ($projectId <= 0 || !isset($parsedData['project']) || !is_object($parsedData['project'])) {
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

        if (!$database->updateObject('#__sportsmanagement_project', $row, 'id')) {
            throw new RuntimeException('Unable to update imported project favorite teams.', 500);
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

    private function deleteImportFile(): void
    {
        $path = JPATH_SITE . '/tmp/sportsmanagement_import.jlg';

        if (is_file($path)) {
            @unlink($path);
        }
    }
}
