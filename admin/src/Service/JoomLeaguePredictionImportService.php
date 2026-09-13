<?php
/**
 * Joomla 5/6 JoomLeague prediction import service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/** Finish prediction-game references after the main JoomLeague import. */
final class JoomLeaguePredictionImportService
{
    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    /** @return array<int,array{label:string,success:bool,count:int,message:string}> */
    public function migrate(): array
    {
        $results = [];

        foreach ([
            '#__sportsmanagement_prediction_admin' => 'Prediction-Administratoren',
            '#__sportsmanagement_prediction_member' => 'Prediction-Mitglieder',
            '#__sportsmanagement_prediction_project' => 'Prediction-Projekte',
            '#__sportsmanagement_prediction_result' => 'Prediction-Ergebnisse',
            '#__sportsmanagement_prediction_result_round' => 'Prediction-Runden',
            '#__sportsmanagement_prediction_template' => 'Prediction-Templates',
        ] as $table => $label) {
            $results[] = $this->remapPredictionGame($table, $label);
        }

        $results[] = $this->remapMemberTips();

        return $results;
    }

    /** @return array<int,array{label:string,success:bool,count:int,message:string}> */
    public function migrateFinalFour(DatabaseInterface $source): array
    {
        try {
            $projectMap = $this->loadImportMap('#__sportsmanagement_project');
            $projectTeamMap = $this->loadImportMap('#__sportsmanagement_project_team');
            $query = $source->createQuery()
                ->select([
                    $source->quoteName('id'),
                    $source->quoteName('champ_tipp'),
                    $source->quoteName('champ_tipp2'),
                    $source->quoteName('champ_tipp3'),
                    $source->quoteName('champ_tipp4'),
                ])
                ->from($source->quoteName('#__joomleague_prediction_member'));
            $source->setQuery($query);
            $members = $source->loadObjectList() ?: [];
            $updated = 0;

            foreach ($members as $member) {
                $sourceId = (int) ($member->id ?? 0);

                if ($sourceId <= 0) {
                    continue;
                }

                $finalFour = $this->mapTipEntries(
                    [
                        (string) ($member->champ_tipp ?? ''),
                        (string) ($member->champ_tipp2 ?? ''),
                        (string) ($member->champ_tipp3 ?? ''),
                        (string) ($member->champ_tipp4 ?? ''),
                    ],
                    $projectMap,
                    $projectTeamMap
                );

                $update = $this->db->createQuery()
                    ->update($this->db->quoteName('#__sportsmanagement_prediction_member'))
                    ->set($this->db->quoteName('final4_tipp') . ' = ' . $this->db->quote($finalFour))
                    ->where($this->db->quoteName('import_id') . ' = ' . $sourceId);
                $this->db->setQuery($update);
                $this->db->execute();
                $updated += $this->affectedRows();
            }

            return [$this->result('Final-Four-Tipps', true, $updated, 'aktualisiert')];
        } catch (\Throwable $exception) {
            return [$this->result('Final-Four-Tipps', false, 0, $exception->getMessage())];
        }
    }

    /** @return array{label:string,success:bool,count:int,message:string} */
    private function remapPredictionGame(string $referenceTable, string $label): array
    {
        try {
            $query = $this->db->createQuery()
                ->select([
                    $this->db->quoteName('id'),
                    $this->db->quoteName('import_id'),
                ])
                ->from($this->db->quoteName('#__sportsmanagement_prediction_game'))
                ->where($this->db->quoteName('import_id') . ' <> 0')
                ->where($this->db->quoteName('id') . ' <> ' . $this->db->quoteName('import_id'));
            $this->db->setQuery($query);
            $mappings = $this->db->loadObjectList() ?: [];
            $updated = 0;

            foreach ($mappings as $mapping) {
                $newId = (int) ($mapping->id ?? 0);
                $oldId = (int) ($mapping->import_id ?? 0);

                if ($newId <= 0 || $oldId <= 0 || $newId === $oldId) {
                    continue;
                }

                $update = $this->db->createQuery()
                    ->update($this->db->quoteName($referenceTable))
                    ->set($this->db->quoteName('prediction_id') . ' = ' . $newId)
                    ->where($this->db->quoteName('prediction_id') . ' = ' . $oldId)
                    ->where($this->db->quoteName('import_id') . ' <> 0');
                $this->db->setQuery($update);
                $this->db->execute();
                $updated += $this->affectedRows();
            }

            return $this->result($label, true, $updated, 'aktualisiert');
        } catch (\Throwable $exception) {
            return $this->result($label, false, 0, $exception->getMessage());
        }
    }

    /** @return array{label:string,success:bool,count:int,message:string} */
    private function remapMemberTips(): array
    {
        try {
            $projectMap = $this->loadImportMap('#__sportsmanagement_project');
            $projectTeamMap = $this->loadImportMap('#__sportsmanagement_project_team');
            $query = $this->db->createQuery()
                ->select([
                    $this->db->quoteName('id'),
                    $this->db->quoteName('fav_team'),
                    $this->db->quoteName('champ_tipp'),
                ])
                ->from($this->db->quoteName('#__sportsmanagement_prediction_member'))
                ->where($this->db->quoteName('import_id') . ' <> 0');
            $this->db->setQuery($query);
            $members = $this->db->loadObjectList() ?: [];
            $updated = 0;

            foreach ($members as $member) {
                $memberId = (int) ($member->id ?? 0);

                if ($memberId <= 0) {
                    continue;
                }

                $favTeam = $this->mapTipList((string) ($member->fav_team ?? ''), $projectMap, $projectTeamMap);
                $champTipp = $this->mapTipList((string) ($member->champ_tipp ?? ''), $projectMap, $projectTeamMap);

                $update = $this->db->createQuery()
                    ->update($this->db->quoteName('#__sportsmanagement_prediction_member'))
                    ->set($this->db->quoteName('fav_team') . ' = ' . $this->db->quote($favTeam))
                    ->set($this->db->quoteName('champ_tipp') . ' = ' . $this->db->quote($champTipp))
                    ->where($this->db->quoteName('id') . ' = ' . $memberId);
                $this->db->setQuery($update);
                $this->db->execute();
                $updated += $this->affectedRows();
            }

            return $this->result('Favoriten- und Meistertipps', true, $updated, 'aktualisiert');
        } catch (\Throwable $exception) {
            return $this->result('Favoriten- und Meistertipps', false, 0, $exception->getMessage());
        }
    }

    /** @return array<int,int> */
    private function loadImportMap(string $table): array
    {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('id'),
                $this->db->quoteName('import_id'),
            ])
            ->from($this->db->quoteName($table))
            ->where($this->db->quoteName('import_id') . ' <> 0');
        $this->db->setQuery($query);
        $rows = $this->db->loadObjectList() ?: [];
        $map = [0 => 0];

        foreach ($rows as $row) {
            $oldId = (int) ($row->import_id ?? 0);
            $newId = (int) ($row->id ?? 0);

            if ($oldId > 0 && $newId > 0) {
                $map[$oldId] = $newId;
            }
        }

        return $map;
    }

    /** @param array<int,int> $projectMap @param array<int,int> $projectTeamMap */
    private function mapTipList(string $value, array $projectMap, array $projectTeamMap): string
    {
        $mapped = [];

        foreach (explode(';', trim($value)) as $entry) {
            $entry = trim($entry);

            if ($entry === '') {
                continue;
            }

            [$oldProjectId, $oldProjectTeamId] = array_pad(array_map('intval', explode(',', $entry, 2)), 2, 0);

            if ($oldProjectId <= 0 || $oldProjectTeamId <= 0) {
                continue;
            }

            $projectId = $projectMap[$oldProjectId] ?? 0;
            $projectTeamId = $projectTeamMap[$oldProjectTeamId] ?? 0;

            if ($projectId <= 0 || $projectTeamId <= 0) {
                continue;
            }

            $mapped[$projectId] = $projectTeamId;
        }

        $pairs = [];

        foreach ($mapped as $projectId => $projectTeamId) {
            $pairs[] = $projectId . ',' . $projectTeamId;
        }

        return implode(';', $pairs);
    }

    /** @param array<int,string> $values @param array<int,int> $projectMap @param array<int,int> $projectTeamMap */
    private function mapTipEntries(array $values, array $projectMap, array $projectTeamMap): string
    {
        $pairs = [];

        foreach ($values as $value) {
            foreach (explode(';', trim($value)) as $entry) {
                $entry = trim($entry);

                if ($entry === '') {
                    continue;
                }

                [$oldProjectId, $oldProjectTeamId] = array_pad(array_map('intval', explode(',', $entry, 2)), 2, 0);
                $projectId = $projectMap[$oldProjectId] ?? 0;
                $projectTeamId = $projectTeamMap[$oldProjectTeamId] ?? 0;

                if ($projectId > 0 && $projectTeamId > 0) {
                    $pairs[] = $projectId . ',' . $projectTeamId;
                }
            }
        }

        return implode(';', $pairs);
    }

    private function affectedRows(): int
    {
        return method_exists($this->db, 'getAffectedRows') ? (int) $this->db->getAffectedRows() : 0;
    }

    /** @return array{label:string,success:bool,count:int,message:string} */
    private function result(string $label, bool $success, int $count, string $message): array
    {
        return [
            'label' => $label,
            'success' => $success,
            'count' => $count,
            'message' => $message,
        ];
    }
}
