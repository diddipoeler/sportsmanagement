<?php
/**
 * Joomla 5/6 JoomLeague post-import relation service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/** Repair SportsManagement references after JoomLeague staging rows were inserted. */
final class JoomLeaguePostImportService
{
    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    /** @return array<int,array{label:string,success:bool,count:int,message:string}> */
    public function applySportsType(int $sportsTypeId): array
    {
        if ($sportsTypeId <= 0) {
            return [$this->result('Sportart', false, 0, 'Keine Sportart ausgewählt')];
        }

        $results = [];

        foreach (['team', 'project'] as $table) {
            try {
                $query = $this->db->createQuery()
                    ->update($this->db->quoteName('#__sportsmanagement_' . $table))
                    ->set($this->db->quoteName('sports_type_id') . ' = ' . $sportsTypeId)
                    ->where($this->db->quoteName('sports_type_id') . ' <> ' . $sportsTypeId);
                $this->db->setQuery($query);
                $this->db->execute();
                $results[] = $this->result($table, true, $this->affectedRows(), 'aktualisiert');
            } catch (\Throwable $exception) {
                $results[] = $this->result($table, false, 0, $exception->getMessage());
            }
        }

        return $results;
    }

    /** @return array<int,array{label:string,success:bool,count:int,message:string}> */
    public function remapClubRelations(): array
    {
        return [
            $this->remap('#__sportsmanagement_club', '#__sportsmanagement_team', 'club_id', 'Vereine in Mannschaften'),
            $this->remap('#__sportsmanagement_club', '#__sportsmanagement_playground', 'club_id', 'Vereine in Spielorten'),
            $this->remap('#__sportsmanagement_playground', '#__sportsmanagement_club', 'standard_playground', 'Spielorte in Vereinen'),
            $this->remap('#__sportsmanagement_associations', '#__sportsmanagement_club', 'associations', 'Verbände in Vereinen'),
        ];
    }

    /** @return array<int,array{label:string,success:bool,count:int,message:string}> */
    public function remapSeasonRelations(): array
    {
        return [
            $this->remap('#__sportsmanagement_season', '#__sportsmanagement_project', 'season_id', 'Saisons in Projekten'),
        ];
    }

    /** @return array<int,array{label:string,success:bool,count:int,message:string}> */
    public function remapLeagueRelations(): array
    {
        return [
            $this->remap('#__sportsmanagement_league', '#__sportsmanagement_project', 'league_id', 'Ligen in Projekten'),
        ];
    }

    /** @return array<int,array{label:string,success:bool,count:int,message:string}> */
    public function remapProjectRelations(): array
    {
        $results = [];

        foreach ([
            '#__sportsmanagement_round' => 'Runden',
            '#__sportsmanagement_division' => 'Gruppen',
            '#__sportsmanagement_project_position' => 'Projektpositionen',
            '#__sportsmanagement_project_referee' => 'Projektschiedsrichter',
            '#__sportsmanagement_project_team' => 'Projektmannschaften',
            '#__sportsmanagement_template_config' => 'Template-Konfigurationen',
            '#__sportsmanagement_prediction_project' => 'Prediction-Projekte',
            '#__sportsmanagement_prediction_result' => 'Prediction-Ergebnisse',
            '#__sportsmanagement_prediction_result_round' => 'Prediction-Runden',
        ] as $referenceTable => $label) {
            $results[] = $this->remap(
                '#__sportsmanagement_project',
                $referenceTable,
                'project_id',
                'Projekte in ' . $label
            );
        }

        $results[] = $this->remap(
            '#__sportsmanagement_project',
            '#__sportsmanagement_project',
            'master_template',
            'Master-Templates'
        );

        return $results;
    }

    /** @return array<int,array{label:string,success:bool,count:int,message:string}> */
    public function remapPositionAndEventTypeRelations(string $modified, int $userId): array
    {
        return [
            $this->remapWithAudit(
                '#__sportsmanagement_position',
                '#__sportsmanagement_person',
                'position_id',
                'Positionen in Personen',
                $modified,
                $userId
            ),
            $this->remapWithAudit(
                '#__sportsmanagement_position',
                '#__sportsmanagement_project_position',
                'position_id',
                'Positionen in Projektpositionen',
                $modified,
                $userId
            ),
            $this->remapWithAudit(
                '#__sportsmanagement_position',
                '#__sportsmanagement_position_eventtype',
                'position_id',
                'Positionen in Ereignistypen',
                $modified,
                $userId
            ),
            $this->remapWithAudit(
                '#__sportsmanagement_position',
                '#__sportsmanagement_position_statistic',
                'position_id',
                'Positionen in Statistiken',
                $modified,
                $userId
            ),
            $this->remapWithAudit(
                '#__sportsmanagement_eventtype',
                '#__sportsmanagement_position_eventtype',
                'eventtype_id',
                'Ereignistypen in Positionen',
                $modified,
                $userId
            ),
            $this->remapWithAudit(
                '#__sportsmanagement_eventtype',
                '#__sportsmanagement_match_event',
                'event_type_id',
                'Ereignistypen in Spielereignissen',
                $modified,
                $userId
            ),
        ];
    }

    /** @return array<int,array{label:string,success:bool,count:int,message:string}> */
    public function remapPersonRelations(): array
    {
        return [
            $this->remap(
                '#__sportsmanagement_person',
                '#__sportsmanagement_team_player',
                'person_id',
                'Personen in Team-Spielern'
            ),
            $this->remap(
                '#__sportsmanagement_person',
                '#__sportsmanagement_team_staff',
                'person_id',
                'Personen in Team-Staff'
            ),
            $this->remap(
                '#__sportsmanagement_person',
                '#__sportsmanagement_project_referee',
                'person_id',
                'Personen in Projektschiedsrichtern'
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

    /** @return array{label:string,success:bool,count:int,message:string} */
    private function remapWithAudit(
        string $entityTable,
        string $referenceTable,
        string $referenceField,
        string $label,
        string $modified,
        int $userId
    ): array {
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
                    ->set($this->db->quoteName('modified') . ' = ' . $this->db->quote($modified))
                    ->set($this->db->quoteName('modified_by') . ' = ' . max(0, $userId))
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
