<?php
/**
 * Joomla 5/6 native project-match XML import service.
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
 * Native writer for project XML import step 25.
 *
 * The returned conversion map keeps the historical source match IDs as keys so
 * match players, staff, referees, events, statistics and tournament nodes can
 * continue to resolve the newly inserted rows.
 */
final class XmlProjectMatchImportService
{
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $roundMap
     * @param array<int, int> $projectTeamMap
     * @param array<int, int> $playgroundMap
     * @param array<int, int> $divisionMap
     *
     * @return array{maps:array<string,array<int,int>>,messages:array<string,string>}
     */
    public function import(
        array $parsedData,
        array $roundMap,
        array $projectTeamMap,
        array $playgroundMap,
        array $divisionMap,
        string $importVersion
    ): array {
        $matchMap = [];
        $message = '';

        foreach (array_values((array) ($parsedData['match'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = max(0, (int) ($source->id ?? 0));
            $oldRoundId = max(0, (int) ($source->round_id ?? 0));
            $oldHomeId = $this->sourceProjectTeamId($source, 'projectteam1_id', 'matchpart1');
            $oldAwayId = $this->sourceProjectTeamId($source, 'projectteam2_id', 'matchpart2');

            $roundId = $oldRoundId > 0 ? (int) ($roundMap[$oldRoundId] ?? 0) : 0;
            $homeId = $oldHomeId > 0 ? (int) ($projectTeamMap[$oldHomeId] ?? 0) : 0;
            $awayId = $oldAwayId > 0 ? (int) ($projectTeamMap[$oldAwayId] ?? 0) : 0;

            if (($oldRoundId > 0 && $roundId <= 0)
                || ($oldHomeId > 0 && $homeId <= 0)
                || ($oldAwayId > 0 && $awayId <= 0)
            ) {
                $message .= $this->skippedMessage($oldId, $oldRoundId, $oldHomeId, $oldAwayId);
                continue;
            }

            $row = $this->filterSourceFields($source);
            $row->round_id = $roundId;
            $row->projectteam1_id = $homeId;
            $row->projectteam2_id = $awayId;

            $oldPlaygroundId = max(0, (int) ($source->playground_id ?? 0));
            $row->playground_id = $oldPlaygroundId > 0
                ? (($playgroundMap[$oldPlaygroundId] ?? 0) > 0 ? (int) $playgroundMap[$oldPlaygroundId] : null)
                : null;

            $oldDivisionId = max(0, (int) ($source->division_id ?? 0));
            $row->division_id = $oldDivisionId > 0 ? (int) ($divisionMap[$oldDivisionId] ?? 0) : 0;

            $sourceDate = trim((string) ($source->match_date ?? ''));
            $row->match_date = $this->normaliseMatchDate($sourceDate);
            $row->match_timestamp = $this->timestamp($sourceDate);
            $row->import_match_id = $oldId;

            $this->applyLegacyMatchPartFields($row, $source);

            if (!$this->database->insertObject('#__sportsmanagement_match', $row)) {
                throw new RuntimeException('Unable to store imported match ID ' . $oldId . '.', 500);
            }

            $databaseId = (int) $this->database->insertid();

            if ($oldId > 0) {
                $matchMap[$oldId] = $databaseId;
            }

            $message .= $this->createdMessage(
                $oldId,
                $databaseId,
                $roundId,
                $homeId,
                $awayId,
                $row->match_date,
                $importVersion
            );
        }

        return [
            'maps' => ['_convertMatchID' => $matchMap],
            'messages' => ['Importing match data:' => $message],
        ];
    }

    private function sourceProjectTeamId(object $source, string $currentField, string $legacyField): int
    {
        if (property_exists($source, $legacyField)) {
            return max(0, (int) $source->{$legacyField});
        }

        return max(0, (int) ($source->{$currentField} ?? 0));
    }

    private function applyLegacyMatchPartFields(object $row, object $source): void
    {
        $fields = [
            'matchpart1_result' => 'team1_result',
            'matchpart2_result' => 'team2_result',
            'matchpart1_bonus' => 'team1_bonus',
            'matchpart2_bonus' => 'team2_bonus',
            'matchpart1_legs' => 'team1_legs',
            'matchpart2_legs' => 'team2_legs',
            'matchpart1_result_split' => 'team1_result_split',
            'matchpart2_result_split' => 'team2_result_split',
            'matchpart1_result_ot' => 'team1_result_ot',
            'matchpart2_result_ot' => 'team2_result_ot',
            'matchpart1_result_decision' => 'team1_result_decision',
            'matchpart2_result_decision' => 'team2_result_decision',
        ];

        foreach ($fields as $legacyField => $targetField) {
            if (property_exists($source, $legacyField)) {
                $row->{$targetField} = $source->{$legacyField};
            }
        }
    }

    private function normaliseMatchDate(string $value): string
    {
        if ($value === '') {
            return '0000-00-00 00:00:00';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}$/', $value) === 1) {
            return $value . ':00';
        }

        return $value;
    }

    private function timestamp(string $value): int
    {
        if ($value === '' || str_starts_with($value, '0000-00-00')) {
            return 0;
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? 0 : $timestamp;
    }

    private function filterSourceFields(object $source): object
    {
        $columns = $this->database->getTableColumns('#__sportsmanagement_match');
        $row = new \stdClass();

        foreach ($source as $field => $value) {
            if ($field === 'id') {
                continue;
            }

            if (array_key_exists((string) $field, $columns)) {
                $row->{$field} = $value;
            }
        }

        return $row;
    }

    private function createdMessage(
        int $oldId,
        int $databaseId,
        int $roundId,
        int $homeId,
        int $awayId,
        string $matchDate,
        string $importVersion
    ): string {
        return '<span style="color:green">Created new match data: </span><strong>'
            . htmlspecialchars((string) $oldId, ENT_QUOTES, 'UTF-8')
            . '</strong> &rarr; <strong>'
            . htmlspecialchars((string) $databaseId, ENT_QUOTES, 'UTF-8')
            . '</strong> / Round <strong>'
            . htmlspecialchars((string) $roundId, ENT_QUOTES, 'UTF-8')
            . '</strong> / Teams <strong>'
            . htmlspecialchars((string) $homeId, ENT_QUOTES, 'UTF-8')
            . '</strong> - <strong>'
            . htmlspecialchars((string) $awayId, ENT_QUOTES, 'UTF-8')
            . '</strong> / Date <strong>'
            . htmlspecialchars($matchDate, ENT_QUOTES, 'UTF-8')
            . '</strong> / Source <strong>'
            . htmlspecialchars(strtoupper($importVersion), ENT_QUOTES, 'UTF-8')
            . '</strong><br />';
    }

    private function skippedMessage(int $oldId, int $roundId, int $homeId, int $awayId): string
    {
        return '<span style="color:red">Skipping match ID <strong>'
            . htmlspecialchars((string) $oldId, ENT_QUOTES, 'UTF-8')
            . '</strong>; unresolved source IDs — Round: <strong>'
            . htmlspecialchars((string) $roundId, ENT_QUOTES, 'UTF-8')
            . '</strong>, Home: <strong>'
            . htmlspecialchars((string) $homeId, ENT_QUOTES, 'UTF-8')
            . '</strong>, Away: <strong>'
            . htmlspecialchars((string) $awayId, ENT_QUOTES, 'UTF-8')
            . '</strong>.</span><br />';
    }
}
