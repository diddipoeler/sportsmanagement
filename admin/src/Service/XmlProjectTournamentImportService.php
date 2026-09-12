<?php
/**
 * Joomla 5/6 native tournament-tree XML import service.
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
 * Native writer for project XML import steps 33-35.
 */
final class XmlProjectTournamentImportService
{
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $divisionMap
     * @param array<int, int> $projectTeamMap
     * @param array<int, int> $matchMap
     *
     * @return array{maps:array<string,array<int,int>>,messages:array<string,string>}
     */
    public function import(
        array $parsedData,
        int $projectId,
        array $divisionMap,
        array $projectTeamMap,
        array $matchMap,
        string $step
    ): array {
        $maps = [
            '_convertTreetoID' => [],
            '_convertTreetonodeID' => [],
            '_convertTreetomatchID' => [],
        ];
        $messages = [];

        if (version_compare($step, '33', 'ge')) {
            $result = $this->importTrees($parsedData, $projectId, $divisionMap);
            $maps['_convertTreetoID'] = $result['map'];
            $messages['Importing treeto data:'] = $result['message'];
        }

        if (version_compare($step, '34', 'ge')) {
            $result = $this->importNodes(
                $parsedData,
                $maps['_convertTreetoID'],
                $projectTeamMap
            );
            $maps['_convertTreetonodeID'] = $result['map'];
            $messages['Importing treetonode data:'] = $result['message'];
        }

        if (version_compare($step, '35', 'ge')) {
            $result = $this->importTreeMatches(
                $parsedData,
                $maps['_convertTreetonodeID'],
                $matchMap
            );
            $maps['_convertTreetomatchID'] = $result['map'];
            $messages['Importing treetomatch data:'] = $result['message'];
        }

        return ['maps' => $maps, 'messages' => $messages];
    }

    /**
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $divisionMap
     * @return array{map:array<int,int>,message:string}
     */
    private function importTrees(array $parsedData, int $projectId, array $divisionMap): array
    {
        $map = [];
        $message = '';

        foreach (array_values((array) ($parsedData['treeto'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = max(0, (int) ($source->id ?? 0));
            $oldDivisionId = max(0, (int) ($source->division_id ?? 0));
            $name = trim((string) ($source->name ?? ''));
            $row = (object) [
                'project_id' => $projectId,
                'division_id' => $oldDivisionId > 0 ? (int) ($divisionMap[$oldDivisionId] ?? 0) : 0,
                'tree_i' => (int) ($source->tree_i ?? 0),
                'name' => $name !== '' ? $name : (string) $oldId,
                'global_bestof' => (int) ($source->global_bestof ?? 0),
                'global_matchday' => (int) ($source->global_matchday ?? 0),
                'global_known' => (int) ($source->global_known ?? 0),
                'global_fake' => (int) ($source->global_fake ?? 0),
                'leafed' => (int) ($source->leafed ?? 0),
                'mirror' => (int) ($source->mirror ?? 0),
                'hide' => (int) ($source->hide ?? 0),
                'trophypic' => (string) ($source->trophypic ?? ''),
            ];

            $databaseId = $this->insert('#__sportsmanagement_treeto', $row, 'treeto', $oldId);

            if ($oldId > 0) {
                $map[$oldId] = $databaseId;
            }

            $message .= $this->createdMessage('treeto', $oldId, $databaseId);
        }

        return ['map' => $map, 'message' => $message];
    }

    /**
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $treeMap
     * @param array<int, int> $projectTeamMap
     * @return array{map:array<int,int>,message:string}
     */
    private function importNodes(array $parsedData, array $treeMap, array $projectTeamMap): array
    {
        $map = [];
        $message = '';

        foreach (array_values((array) ($parsedData['treetonode'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = max(0, (int) ($source->id ?? 0));
            $oldTreeId = max(0, (int) ($source->treeto_id ?? 0));
            $treeId = (int) ($treeMap[$oldTreeId] ?? 0);

            if ($oldTreeId <= 0 || $treeId <= 0) {
                $message .= $this->skippedMessage('treetonode', $oldId);
                continue;
            }

            $oldTeamId = max(0, (int) ($source->team_id ?? 0));
            $row = (object) [
                'treeto_id' => $treeId,
                'node' => (int) ($source->node ?? 0),
                'row' => (int) ($source->row ?? 0),
                'bestof' => (int) ($source->bestof ?? 1),
                'title' => (string) ($source->title ?? ''),
                'content' => (string) ($source->content ?? ''),
                'team_id' => $oldTeamId > 0 ? (int) ($projectTeamMap[$oldTeamId] ?? 0) : 0,
                'published' => (int) ($source->published ?? 1),
                'is_leaf' => (int) ($source->is_leaf ?? 0),
                'is_lock' => (int) ($source->is_lock ?? 0),
                'is_ready' => (int) ($source->is_ready ?? 0),
                'got_lc' => (int) ($source->got_lc ?? 0),
                'got_rc' => (int) ($source->got_rc ?? 0),
            ];

            $databaseId = $this->insert('#__sportsmanagement_treeto_node', $row, 'treetonode', $oldId);

            if ($oldId > 0) {
                $map[$oldId] = $databaseId;
            }

            $message .= $this->createdMessage('treetonode', $oldId, $databaseId);
        }

        return ['map' => $map, 'message' => $message];
    }

    /**
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $nodeMap
     * @param array<int, int> $matchMap
     * @return array{map:array<int,int>,message:string}
     */
    private function importTreeMatches(array $parsedData, array $nodeMap, array $matchMap): array
    {
        $map = [];
        $message = '';

        foreach (array_values((array) ($parsedData['treetomatch'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = max(0, (int) ($source->id ?? 0));
            $oldNodeId = max(0, (int) ($source->node_id ?? 0));
            $oldMatchId = max(0, (int) ($source->match_id ?? 0));
            $nodeId = (int) ($nodeMap[$oldNodeId] ?? 0);
            $matchId = (int) ($matchMap[$oldMatchId] ?? 0);

            if ($oldNodeId <= 0 || $nodeId <= 0 || $oldMatchId <= 0 || $matchId <= 0) {
                $message .= $this->skippedMessage('treetomatch', $oldId);
                continue;
            }

            $row = (object) [
                'node_id' => $nodeId,
                'match_id' => $matchId,
            ];
            $databaseId = $this->insert('#__sportsmanagement_treeto_match', $row, 'treetomatch', $oldId);

            if ($oldId > 0) {
                $map[$oldId] = $databaseId;
            }

            $message .= $this->createdMessage('treetomatch', $oldId, $databaseId);
        }

        return ['map' => $map, 'message' => $message];
    }

    private function insert(string $table, object $row, string $label, int $oldId): int
    {
        if (!$this->database->insertObject($table, $row)) {
            throw new RuntimeException('Unable to store imported ' . $label . ' ID ' . $oldId . '.', 500);
        }

        return (int) $this->database->insertid();
    }

    private function createdMessage(string $label, int $oldId, int $databaseId): string
    {
        return '<span style="color:green">Created new ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            . ' data: </span><strong>' . htmlspecialchars((string) $oldId, ENT_QUOTES, 'UTF-8')
            . '</strong> &rarr; <strong>' . htmlspecialchars((string) $databaseId, ENT_QUOTES, 'UTF-8')
            . '</strong><br />';
    }

    private function skippedMessage(string $label, int $oldId): string
    {
        return '<span style="color:red">Skipping ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            . ' ID <strong>' . htmlspecialchars((string) $oldId, ENT_QUOTES, 'UTF-8')
            . '</strong>; a required imported relation could not be resolved.</span><br />';
    }
}
