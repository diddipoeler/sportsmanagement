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
    public function remapRoundDivisionAndProjectTeamMatchRelations(): array
    {
        return [
            $this->remap(
                '#__sportsmanagement_round',
                '#__sportsmanagement_match',
                'round_id',
                'Runden in Spielen'
            ),
            $this->remap(
                '#__sportsmanagement_division',
                '#__sportsmanagement_match',
                'division_id',
                'Gruppen in Spielen'
            ),
            $this->remap(
                '#__sportsmanagement_division',
                '#__sportsmanagement_project_team',
                'division_id',
                'Gruppen in Projektmannschaften'
            ),
            $this->remap(
                '#__sportsmanagement_division',
                '#__sportsmanagement_prediction_result_round',
                'division_id',
                'Gruppen in Prediction-Runden'
            ),
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

    /** @return array<int,array{label:string,success:bool,count:int,message:string}> */
    public function remapMatchRelations(): array
    {
        return [
            $this->remap('#__sportsmanagement_match', '#__sportsmanagement_match_player', 'match_id', 'Spiele in Spiel-Spielern'),
            $this->remap('#__sportsmanagement_match', '#__sportsmanagement_match_staff', 'match_id', 'Spiele in Spiel-Staff'),
            $this->remap('#__sportsmanagement_match', '#__sportsmanagement_match_staff_statistic', 'match_id', 'Spiele in Staff-Statistiken'),
            $this->remap('#__sportsmanagement_match', '#__sportsmanagement_match_statistic', 'match_id', 'Spiele in Spielstatistiken'),
            $this->remap('#__sportsmanagement_match', '#__sportsmanagement_match_referee', 'match_id', 'Spiele in Schiedsrichtern'),
            $this->remap('#__sportsmanagement_match', '#__sportsmanagement_match_event', 'match_id', 'Spiele in Spielereignissen'),
            $this->remap('#__sportsmanagement_match', '#__sportsmanagement_prediction_result', 'match_id', 'Spiele in Prediction-Ergebnissen'),
        ];
    }

    /** @return array<int,array{label:string,success:bool,count:int,message:string}> */
    public function remapStatisticRelations(): array
    {
        return [
            $this->remap(
                '#__sportsmanagement_statistic',
                '#__sportsmanagement_match_staff_statistic',
                'statistic_id',
                'Statistiken in Staff-Statistiken'
            ),
            $this->remap(
                '#__sportsmanagement_statistic',
                '#__sportsmanagement_match_statistic',
                'statistic_id',
                'Statistiken in Spielstatistiken'
            ),
            $this->remap(
                '#__sportsmanagement_statistic',
                '#__sportsmanagement_position_statistic',
                'statistic_id',
                'Statistiken in Positionsstatistiken'
            ),
        ];
    }

    /** @return array<int,array{label:string,success:bool,count:int,message:string}> */
    public function ensureProjectPositionRelations(string $modified, int $userId): array
    {
        try {
            $contexts = $this->loadProjectTeamContexts();
            $inserted = 0;

            $this->db->transactionStart();

            try {
                foreach ($contexts as $context) {
                    $projectId = (int) ($context->project_id ?? 0);
                    $seasonId = (int) ($context->season_id ?? 0);
                    $teamId = (int) ($context->team_id ?? 0);

                    if ($projectId <= 0 || $seasonId <= 0 || $teamId <= 0) {
                        continue;
                    }

                    foreach ([1, 2] as $personType) {
                        $inserted += $this->ensureProjectPositionsForRoster(
                            $projectId,
                            $seasonId,
                            $teamId,
                            $personType,
                            $modified,
                            $userId
                        );
                    }
                }

                $this->db->transactionCommit();
            } catch (\Throwable $exception) {
                $this->db->transactionRollback();
                throw $exception;
            }

            return [$this->result('Projektpositionen pro Spieler/Staff', true, $inserted, 'eingefügt')];
        } catch (\Throwable $exception) {
            return [$this->result('Projektpositionen pro Spieler/Staff', false, 0, $exception->getMessage())];
        }
    }

    /** @return array<int,object> */
    private function loadProjectTeamContexts(): array
    {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('p.id', 'project_id'),
                $this->db->quoteName('p.season_id'),
                $this->db->quoteName('st.team_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $this->db->quoteName('pt.project_id') . ' = ' . $this->db->quoteName('p.id')
            )
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $this->db->quoteName('st.id') . ' = ' . $this->db->quoteName('pt.team_id')
            )
            ->where($this->db->quoteName('p.import_id') . ' <> 0')
            ->where($this->db->quoteName('pt.import_id') . ' <> 0')
            ->group([
                $this->db->quoteName('p.id'),
                $this->db->quoteName('p.season_id'),
                $this->db->quoteName('st.team_id'),
            ]);
        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    private function ensureProjectPositionsForRoster(
        int $projectId,
        int $seasonId,
        int $teamId,
        int $personType,
        string $modified,
        int $userId
    ): int {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('stp.person_id'),
                $this->db->quoteName('ppos.id', 'project_position_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_season_team_person_id', 'stp'))
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_person', 'p')
                . ' ON ' . $this->db->quoteName('p.id') . ' = ' . $this->db->quoteName('stp.person_id')
            )
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $this->db->quoteName('ppos.position_id') . ' = ' . $this->db->quoteName('p.position_id')
            )
            ->where($this->db->quoteName('stp.team_id') . ' = ' . $teamId)
            ->where($this->db->quoteName('stp.season_id') . ' = ' . $seasonId)
            ->where($this->db->quoteName('stp.persontype') . ' = ' . $personType)
            ->where($this->db->quoteName('ppos.project_id') . ' = ' . $projectId);
        $this->db->setQuery($query);
        $rows = $this->db->loadObjectList() ?: [];
        $inserted = 0;

        foreach ($rows as $row) {
            $personId = (int) ($row->person_id ?? 0);
            $projectPositionId = (int) ($row->project_position_id ?? 0);

            if ($personId <= 0 || $projectPositionId <= 0) {
                continue;
            }

            $exists = $this->db->createQuery()
                ->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__sportsmanagement_person_project_position'))
                ->where($this->db->quoteName('person_id') . ' = ' . $personId)
                ->where($this->db->quoteName('project_id') . ' = ' . $projectId)
                ->where($this->db->quoteName('project_position_id') . ' = ' . $projectPositionId)
                ->where($this->db->quoteName('persontype') . ' = ' . $personType);
            $this->db->setQuery($exists, 0, 1);

            if ($this->db->loadResult()) {
                continue;
            }

            $insert = $this->db->createQuery()
                ->insert($this->db->quoteName('#__sportsmanagement_person_project_position'))
                ->columns([
                    $this->db->quoteName('person_id'),
                    $this->db->quoteName('project_id'),
                    $this->db->quoteName('project_position_id'),
                    $this->db->quoteName('persontype'),
                    $this->db->quoteName('import_id'),
                    $this->db->quoteName('published'),
                    $this->db->quoteName('modified'),
                    $this->db->quoteName('modified_by'),
                ])
                ->values(implode(', ', [
                    $personId,
                    $projectId,
                    $projectPositionId,
                    $personType,
                    1,
                    1,
                    $this->db->quote($modified),
                    max(0, $userId),
                ]));
            $this->db->setQuery($insert);
            $this->db->execute();
            $inserted += $this->affectedRows();
        }

        return $inserted;
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
