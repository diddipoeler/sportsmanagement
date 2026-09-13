<?php
/**
 * Joomla 5/6 JoomLeague final import relation service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/** Repair the final match-related references after JoomLeague staging. */
final class JoomLeagueFinalImportService
{
    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    /** @return array<int,array{label:string,success:bool,count:int,message:string}> */
    public function remapProjectTeamMatchRelations(): array
    {
        return [
            $this->remap(
                '#__sportsmanagement_project_team',
                '#__sportsmanagement_match',
                'projectteam1_id',
                'Heimmannschaften in Spielen'
            ),
            $this->remap(
                '#__sportsmanagement_project_team',
                '#__sportsmanagement_match',
                'projectteam2_id',
                'Gastmannschaften in Spielen'
            ),
            $this->remap(
                '#__sportsmanagement_project_team',
                '#__sportsmanagement_match_event',
                'projectteam_id',
                'Projektmannschaften in Spielereignissen'
            ),
        ];
    }

    /** @return array{label:string,success:bool,count:int,message:string} */
    private function remap(string $entityTable, string $referenceTable, string $referenceField, string $label): array
    {
        try {
            $query = $this->db->createQuery()
                ->select([
                    $this->db->quoteName('id'),
                    $this->db->quoteName('import_id'),
                ])
                ->from($this->db->quoteName($entityTable))
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
                    ->set($this->db->quoteName($referenceField) . ' = ' . $newId)
                    ->where($this->db->quoteName($referenceField) . ' = ' . $oldId)
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
