<?php
/**
 * Joomla 5/6 bridge for the remaining legacy project XML graph.
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
use ReflectionMethod;
use RuntimeException;

/**
 * Prime the historical importer with native conversion state and continue only
 * with the still-unmigrated project graph (steps 23-35).
 */
final class LegacyProjectContinuationService
{
    /** @var array<int, string> */
    private const LEGACY_STEPS = [
        23 => '_importTeamTraining',
        24 => '_importRounds',
        25 => '_importMatches',
        26 => '_importMatchPlayer',
        27 => '_importMatchStaff',
        28 => '_importMatchReferee',
        29 => '_importMatchEvent',
        30 => '_importPositionStatistic',
        31 => '_importMatchStaffStatistic',
        32 => '_importMatchStatistic',
        33 => '_importTreetos',
        34 => '_importTreetonode',
        35 => '_importTreetomatch',
    ];

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
            throw new RuntimeException('Unable to load XML data for the legacy project continuation.', 500);
        }

        // Match the native parser's compatibility handling for JoomLeague 0.93
        // exports where TeamTool represented what later became ProjectTeam.
        if (!empty($parsedData['teamtool'])) {
            $parsedData['projectteam'] = array_values((array) $parsedData['teamtool']);
        }

        if (version_compare($targetStep, '21', 'ge')) {
            $database = $legacy->getDbo();

            if (!$database instanceof DatabaseInterface) {
                throw new RuntimeException('Legacy XML continuation database is unavailable.', 500);
            }

            $memberResult = (new XmlProjectMemberImportService($database))->import(
                $parsedData,
                (array) ($maps['_convertProjectTeamID'] ?? []),
                $this->preparedOldIdMap($post, $parsedData, 'person', 'dbPersonID_'),
                (array) ($maps['_convertProjectPositionID'] ?? []),
                $targetStep
            );
            $maps = array_replace($maps, $memberResult['maps']);
            $messages = array_replace($messages, $memberResult['messages']);
        }

        $this->primeLegacyState(
            $legacy,
            $post,
            $parsedData,
            $maps,
            $messages,
            (string) ($legacy->import_version ?? '')
        );

        foreach (self::LEGACY_STEPS as $step => $methodName) {
            if (!version_compare($targetStep, (string) $step, 'ge')) {
                continue;
            }

            if ($this->invokeLegacyMethod($legacy, $methodName) === false) {
                return is_array($legacy->_success_text) ? $legacy->_success_text : [];
            }
        }

        if (version_compare($targetStep, '21', 'ge')) {
            if (!method_exists($legacy, 'setNewDataStructur')) {
                throw new RuntimeException('Legacy XML finalizer setNewDataStructur is unavailable.', 500);
            }

            $legacy->setNewDataStructur();

            $model = BaseDatabaseModel::getInstance('databasetool', 'sportsmanagementModel');

            if ($model && method_exists($model, 'setNewPicturePath')) {
                $model->setNewPicturePath();
            }
        }

        // Match the successful legacy import lifecycle after all consumers have
        // finished using the in-memory parsed data.
        $this->invokeLegacyMethod($legacy, '_deleteImportFile');

        return is_array($legacy->_success_text) ? $legacy->_success_text : [];
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

        // These arrays are only used by later legacy guards to decide whether
        // the already-native team/person mappings are available.
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

        // The continuation itself creates these maps in dependency order.
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

    private function invokeLegacyMethod(object $legacy, string $methodName): mixed
    {
        try {
            $method = new ReflectionMethod($legacy, $methodName);
            $method->setAccessible(true);

            return $method->invoke($legacy);
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Unable to continue legacy XML project step ' . $methodName . ': ' . $e->getMessage(),
                500,
                $e
            );
        }
    }
}
