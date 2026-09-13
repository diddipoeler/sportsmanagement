<?php
/**
 * Joomla 5/6 JoomLeague import cleanup service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\Database\DatabaseInterface;

/** Finish the data-only cleanup after a JoomLeague import. */
final class JoomLeagueCleanupService
{
    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    /** @return array<int,array{label:string,success:bool,count:int,message:string}> */
    public function cleanup(): array
    {
        return [
            $this->updateProjectTimestamps(),
            $this->updateMatchTimestamps(),
            $this->normaliseMatchPlayerPositions(),
        ];
    }

    /** @return array{label:string,success:bool,count:int,message:string} */
    private function updateProjectTimestamps(): array
    {
        try {
            $query = $this->db->createQuery()
                ->select([
                    $this->db->quoteName('id'),
                    $this->db->quoteName('modified'),
                ])
                ->from($this->db->quoteName('#__sportsmanagement_project'))
                ->where($this->db->quoteName('modified_timestamp') . ' = 0');
            $this->db->setQuery($query);
            $rows = $this->db->loadObjectList() ?: [];
            $updated = 0;

            foreach ($rows as $row) {
                $timestamp = $this->toTimestamp((string) ($row->modified ?? ''));

                if ($timestamp === null) {
                    continue;
                }

                $updated += $this->updateById(
                    '#__sportsmanagement_project',
                    (int) ($row->id ?? 0),
                    'modified_timestamp',
                    $timestamp
                );
            }

            return $this->result('Projekt-Zeitstempel', true, $updated, 'aktualisiert');
        } catch (\Throwable $exception) {
            return $this->result('Projekt-Zeitstempel', false, 0, $exception->getMessage());
        }
    }

    /** @return array{label:string,success:bool,count:int,message:string} */
    private function updateMatchTimestamps(): array
    {
        try {
            $query = $this->db->createQuery()
                ->select([
                    $this->db->quoteName('id'),
                    $this->db->quoteName('match_date'),
                ])
                ->from($this->db->quoteName('#__sportsmanagement_match'))
                ->where($this->db->quoteName('match_timestamp') . ' = 0');
            $this->db->setQuery($query);
            $rows = $this->db->loadObjectList() ?: [];
            $updated = 0;

            foreach ($rows as $row) {
                $timestamp = $this->toTimestamp((string) ($row->match_date ?? ''));

                if ($timestamp === null) {
                    continue;
                }

                $updated += $this->updateById(
                    '#__sportsmanagement_match',
                    (int) ($row->id ?? 0),
                    'match_timestamp',
                    $timestamp
                );
            }

            return $this->result('Spiel-Zeitstempel', true, $updated, 'aktualisiert');
        } catch (\Throwable $exception) {
            return $this->result('Spiel-Zeitstempel', false, 0, $exception->getMessage());
        }
    }

    /** @return array{label:string,success:bool,count:int,message:string} */
    private function normaliseMatchPlayerPositions(): array
    {
        try {
            $query = $this->db->createQuery()
                ->select([
                    $this->db->quoteName('pp.id', 'project_position_id'),
                    $this->db->quoteName('pp.position_id'),
                    $this->db->quoteName('pp.project_id'),
                ])
                ->from($this->db->quoteName('#__sportsmanagement_project_position', 'pp'))
                ->where($this->db->quoteName('pp.position_id') . ' > 0');
            $this->db->setQuery($query);
            $positions = $this->db->loadObjectList() ?: [];
            $updated = 0;

            foreach ($positions as $position) {
                $projectPositionId = (int) ($position->project_position_id ?? 0);
                $positionId = (int) ($position->position_id ?? 0);
                $projectId = (int) ($position->project_id ?? 0);

                if ($projectPositionId <= 0 || $positionId <= 0 || $projectId <= 0) {
                    continue;
                }

                $matches = $this->db->createQuery()
                    ->select($this->db->quoteName('m.id'))
                    ->from($this->db->quoteName('#__sportsmanagement_match', 'm'))
                    ->join(
                        'INNER',
                        $this->db->quoteName('#__sportsmanagement_round', 'r')
                        . ' ON ' . $this->db->quoteName('r.id') . ' = ' . $this->db->quoteName('m.round_id')
                    )
                    ->where($this->db->quoteName('r.project_id') . ' = ' . $projectId);

                $update = $this->db->createQuery()
                    ->update($this->db->quoteName('#__sportsmanagement_match_player'))
                    ->set($this->db->quoteName('project_position_id') . ' = ' . $positionId)
                    ->where($this->db->quoteName('project_position_id') . ' = ' . $projectPositionId)
                    ->where($this->db->quoteName('match_id') . ' IN (' . $matches . ')');
                $this->db->setQuery($update);
                $this->db->execute();
                $updated += $this->affectedRows();
            }

            return $this->result('Spieler-Positionen', true, $updated, 'normalisiert');
        } catch (\Throwable $exception) {
            return $this->result('Spieler-Positionen', false, 0, $exception->getMessage());
        }
    }

    private function toTimestamp(string $value): ?int
    {
        $value = trim($value);
        $nullDate = method_exists($this->db, 'getNullDate') ? (string) $this->db->getNullDate() : '0000-00-00 00:00:00';

        if ($value === '' || $value === $nullDate || str_starts_with($value, '0000-00-00')) {
            return null;
        }

        try {
            return (new Date($value, 'UTC'))->toUnix();
        } catch (\Throwable) {
            return null;
        }
    }

    private function updateById(string $table, int $id, string $field, int $value): int
    {
        if ($id <= 0) {
            return 0;
        }

        $query = $this->db->createQuery()
            ->update($this->db->quoteName($table))
            ->set($this->db->quoteName($field) . ' = ' . $value)
            ->where($this->db->quoteName('id') . ' = ' . $id);
        $this->db->setQuery($query);
        $this->db->execute();

        return $this->affectedRows();
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
