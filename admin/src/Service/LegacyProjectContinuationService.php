<?php
/**
 * Joomla 5/6 bridge for the remaining legacy project finalisation.
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
 * Prime the historical finalizer with native conversion state.
 *
 * All project graph writers through step 35 are native. The legacy object is
 * retained only for its public parser/finalizer while those last lifecycle
 * responsibilities are migrated separately.
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
        // Reuse only the public legacy parser for the collections still consumed
        // by the continuation. Unlike importData(), getData() performs no writes,
        // finalisation or import-file deletion.
        $parsedData = $legacy->getData($post);

        if (!is_array($parsedData)) {
            throw new RuntimeException('Unable to load XML data for the project import continuation.', 500);
        }

        // Match the native parser's compatibility handling for JoomLeague 0.93
        // exports where TeamTool represented what later became ProjectTeam.
        if (!empty($parsedData['teamtool'])) {
            $parsedData['projectteam'] = array_values((array) $parsedData['teamtool']);
        }

        $database = $legacy->getDbo();

        if (!$database instanceof DatabaseInterface) {
            throw new RuntimeException('XML continuation database is unavailable.', 500);
        }

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
                    max(0, (int) ($legacy->_project_id ?? 0)),
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
        }

        $this->primeLegacyState(
            $legacy,
            $post,
            $parsedData,
            $maps,
            $messages,
            (string) ($legacy->import_version ?? '')
        );

        if (version_compare($targetStep, '21', 'ge')) {
            $this->updateFavoriteTeams(
                $database,
                max(0, (int) ($legacy->_project_id ?? 0)),
                $parsedData,
                $teamMap
            );

            if (!method_exists($legacy, 'setNewDataStructur')) {
                throw new RuntimeException('Legacy XML finalizer setNewDataStructur is unavailable.', 500);
            }

            // This public legacy lifecycle method converts the native temporary
            // team-player/staff rows into final seasonal memberships. Run it
            // before statistics so they can target season_team_person_id.
            $legacy->setNewDataStructur();
        }

        if (version_compare($targetStep, '31', 'ge')) {
            $statisticResult = (new XmlProjectMatchStatisticImportService($database))->import(
                $parsedData,
                max(0, (int) ($legacy->_season_id ?? 0)),
                (array) ($maps['_convertMatchID'] ?? []),
                (array) ($maps['_convertProjectTeamID'] ?? []),
                $personMap,
                $statisticMap,
                $targetStep
            );
            $legacy->_success_text = array_replace(
                is_array($legacy->_success_text ?? null) ? $legacy->_success_text : [],
                $statisticResult['messages']
            );
        }

        if (version_compare($targetStep, '33', 'ge')) {
            $tournamentResult = (new XmlProjectTournamentImportService($database))->import(
                $parsedData,
                max(0, (int) ($legacy->_project_id ?? 0)),
                (array) ($maps['_convertDivisionID'] ?? []),
                (array) ($maps['_convertProjectTeamID'] ?? []),
                (array) ($maps['_convertMatchID'] ?? []),
                $targetStep
            );

            foreach ($tournamentResult['maps'] as $property => $map) {
                $maps[$property] = $map;
                $legacy->{$property} = $map;
            }

            $legacy->_success_text = array_replace(
                is_array($legacy->_success_text ?? null) ? $legacy->_success_text : [],
                $tournamentResult['messages']
            );
        }

        if (version_compare($targetStep, '21', 'ge')) {
            $model = BaseDatabaseModel::getInstance('databasetool', 'sportsmanagementModel');

            if ($model && method_exists($model, 'setNewPicturePath')) {
                $model->setNewPicturePath();
            }
        }

        $this->deleteImportFile();

        return is_array($legacy->_success_text) ? $legacy->_success_text : [];
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
     * @param array<string, array<int, int>> $maps
     * @param array<string, string> $messages
     */
    private function primeLegacyState(
        object $legacy,
        array $post,
        array $parsedData,
        array $maps,
        array $messages,
        string $importVersion
    ): void {
        $legacy->_datas = $parsedData;
        $legacy->_success_text = $messages;
        $legacy->_importType = (string) ($post['importType'] ?? '');
        $legacy->import_version = $importVersion;
        $legacy->_season_id = max(0, (int) ($post['season'] ?? ($post['filter_season'] ?? 0)));
        $legacy->_agegroup_id = max(0, (int) ($post['agegroup_id'] ?? 0));
        $legacy->_template_id = max(0, (int) ($post['copyTemplate'] ?? 0));
        $legacy->master_template = $legacy->_template_id;
        $legacy->timezone = $post['timezone'] ?? 0;
        $legacy->_sportsmanagement_admin = !empty($post['admin']) ? (int) $post['admin'] : 62;
        $legacy->_sportsmanagement_editor = !empty($post['editor']) ? (int) $post['editor'] : 62;
        $legacy->_publish = !empty($post['publish']) ? (int) $post['publish'] : 0;

        // These arrays are retained for the public historical finalizer while
        // that last lifecycle operation is still being migrated.
        $legacy->_dbteamsid = $this->preparedIdsByKey($post, $parsedData, 'team', 'dbTeamID_');
        $legacy->_dbpersonsid = $this->preparedIdsByKey($post, $parsedData, 'person', 'dbPersonID_');
        $legacy->_dbplaygroundsid = $this->preparedIdsByKey($post, $parsedData, 'playground', 'dbPlaygroundID_');
        $legacy->_dbeventsid = $this->preparedIdsByKey($post, $parsedData, 'event', 'dbEventID_');
        $legacy->_dbpositionsid = $this->preparedIdsByKey($post, $parsedData, 'position', 'dbPositionID_');
        $legacy->_dbparentpositionsid = $this->preparedIdsByKey(
            $post,
            $parsedData,
            'parentposition',
            'dbParentPositionID_'
        );
        $legacy->_dbstatisticsid = $this->preparedIdsByKey($post, $parsedData, 'statistic', 'dbStatisticID_');

        $legacy->_newteams = [];
        $legacy->_newpersonsid = [];
        $legacy->_newplaygroundid = [];
        $legacy->_neweventsid = [];
        $legacy->_newpositionsid = [];
        $legacy->_newparentpositionsid = [];
        $legacy->_newstatisticsid = [];

        $legacy->_convertEventID = $this->preparedOldIdMap($post, $parsedData, 'event', 'dbEventID_');
        $legacy->_convertStatisticID = $this->preparedOldIdMap(
            $post,
            $parsedData,
            'statistic',
            'dbStatisticID_'
        );
        $legacy->_convertParentPositionID = $this->preparedOldIdMap(
            $post,
            $parsedData,
            'parentposition',
            'dbParentPositionID_'
        );
        $legacy->_convertPositionID = $this->preparedOldIdMap(
            $post,
            $parsedData,
            'position',
            'dbPositionID_'
        );
        $legacy->_convertPlaygroundID = $this->preparedOldIdMap(
            $post,
            $parsedData,
            'playground',
            'dbPlaygroundID_'
        );
        $legacy->_convertTeamID = $this->preparedOldIdMap($post, $parsedData, 'team', 'dbTeamID_');
        $legacy->_convertPersonID = $this->preparedOldIdMap($post, $parsedData, 'person', 'dbPersonID_');
        $legacy->_convertClubID = [];

        foreach ($maps as $property => $map) {
            $legacy->{$property} = $map;
        }

        foreach ([
            '_convertTeamPlayerID',
            '_convertTeamStaffID',
            '_convertRoundID',
            '_convertMatchID',
            '_convertTreetoID',
            '_convertTreetonodeID',
            '_convertTreetomatchID',
        ] as $property) {
            if (!isset($legacy->{$property}) || !is_array($legacy->{$property})) {
                $legacy->{$property} = [];
            }
        }
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     * @return array<int, int>
     */
    private function preparedIdsByKey(
        array $post,
        array $parsedData,
        string $collection,
        string $prefix
    ): array {
        $ids = [];

        foreach (array_values((array) ($parsedData[$collection] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $databaseId = max(0, (int) ($post[$prefix . $key] ?? 0));

            if ($databaseId > 0) {
                $ids[$key] = $databaseId;
            }
        }

        return $ids;
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
