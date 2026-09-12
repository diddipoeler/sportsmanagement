<?php
/**
 * Joomla 5/6 native project schedule XML import service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use RuntimeException;

/** Native writer for project XML import steps 23 and 24. */
final class XmlProjectScheduleImportService
{
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $projectTeamMap
     * @return array{maps:array<string,array<int,int>>,messages:array<string,string>}
     */
    public function import(
        array $parsedData,
        int $projectId,
        array $projectTeamMap,
        string $step
    ): array {
        $maps = ['_convertRoundID' => []];
        $messages = [];

        if (version_compare($step, '23', 'ge')) {
            $messages['Importing team-training data:'] = $this->importTraining(
                $parsedData,
                $projectId,
                $projectTeamMap
            );
        }

        if (version_compare($step, '24', 'ge')) {
            $rounds = $this->importRounds($parsedData, $projectId);
            $maps['_convertRoundID'] = $rounds['map'];
            $messages['Importing round data:'] = $rounds['message'];
        }

        return ['maps' => $maps, 'messages' => $messages];
    }

    /**
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $projectTeamMap
     */
    private function importTraining(array $parsedData, int $projectId, array $projectTeamMap): string
    {
        $message = '';

        foreach (array_values((array) ($parsedData['teamtraining'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldProjectTeamId = (int) ($source->project_team_id ?? 0);
            $projectTeamId = (int) ($projectTeamMap[$oldProjectTeamId] ?? 0);

            if ($projectTeamId <= 0) {
                $message .= $this->skippedMessage('team-training', (int) ($source->id ?? 0));
                continue;
            }

            $teamId = $this->findRawTeamId($projectTeamId);

            if ($teamId <= 0) {
                $message .= $this->skippedMessage('team-training', (int) ($source->id ?? 0));
                continue;
            }

            $dayOfWeek = (int) ($source->dayofweek ?? 0);
            $timeStart = (int) ($source->time_start ?? 0);
            $timeEnd = (int) ($source->time_end ?? 0);

            if ($this->trainingExists($projectId, $teamId, $projectTeamId, $dayOfWeek, $timeStart, $timeEnd)) {
                $message .= $this->existingMessage('team-training', (string) $projectTeamId);
                continue;
            }

            $row = (object) [
                'project_id' => $projectId,
                'team_id' => $teamId,
                'project_team_id' => $projectTeamId,
                'dayofweek' => $dayOfWeek,
                'time_start' => $timeStart,
                'time_end' => $timeEnd,
                'place' => (string) ($source->place ?? ''),
                'notes' => (string) ($source->notes ?? ''),
            ];

            if (!$this->database->insertObject('#__sportsmanagement_team_trainingdata', $row)) {
                throw new RuntimeException('Unable to store imported team training.', 500);
            }

            $message .= $this->createdMessage('team-training', (string) $projectTeamId);
        }

        return $message;
    }

    /**
     * @param array<string, mixed> $parsedData
     * @return array{map:array<int,int>,message:string}
     */
    private function importRounds(array $parsedData, int $projectId): array
    {
        $map = [];
        $message = '';

        foreach (array_values((array) ($parsedData['round'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = (int) ($source->id ?? 0);
            $name = (string) ($source->name ?? '');
            $row = $this->filterSourceFields($source, '#__sportsmanagement_round');
            $row->project_id = $projectId;
            $row->alias = OutputFilter::stringURLSafe($name);

            if (property_exists($source, 'matchcode')) {
                $row->roundcode = (int) $source->matchcode;
            }

            if (!$this->database->insertObject('#__sportsmanagement_round', $row)) {
                throw new RuntimeException('Unable to store imported round: ' . $name, 500);
            }

            $databaseId = (int) $this->database->insertid();

            if ($oldId > 0) {
                $map[$oldId] = $databaseId;
            }

            $message .= $this->createdMessage('round', $name);
        }

        return ['map' => $map, 'message' => $message];
    }

    private function findRawTeamId(int $projectTeamId): int
    {
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('st.team_id'))
            ->from($this->database->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $this->database->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $this->database->quoteName('st.id') . ' = ' . $this->database->quoteName('pt.team_id')
            )
            ->where($this->database->quoteName('pt.id') . ' = :projectTeamId')
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);

        return (int) ($this->database->loadResult() ?? 0);
    }

    private function trainingExists(
        int $projectId,
        int $teamId,
        int $projectTeamId,
        int $dayOfWeek,
        int $timeStart,
        int $timeEnd
    ): bool {
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('id'))
            ->from($this->database->quoteName('#__sportsmanagement_team_trainingdata'))
            ->where($this->database->quoteName('project_id') . ' = :projectId')
            ->where($this->database->quoteName('team_id') . ' = :teamId')
            ->where($this->database->quoteName('project_team_id') . ' = :projectTeamId')
            ->where($this->database->quoteName('dayofweek') . ' = :dayOfWeek')
            ->where($this->database->quoteName('time_start') . ' = :timeStart')
            ->where($this->database->quoteName('time_end') . ' = :timeEnd')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':teamId', $teamId, ParameterType::INTEGER)
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->bind(':dayOfWeek', $dayOfWeek, ParameterType::INTEGER)
            ->bind(':timeStart', $timeStart, ParameterType::INTEGER)
            ->bind(':timeEnd', $timeEnd, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadResult() !== null;
    }

    private function filterSourceFields(object $source, string $table): object
    {
        $columns = $this->database->getTableColumns($table);
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

    private function createdMessage(string $label, string $value): string
    {
        return '<span style="color:green">Created new ' . $label . ' data: </span><strong>'
            . $this->escape($value) . '</strong><br />';
    }

    private function existingMessage(string $label, string $value): string
    {
        return '<span style="color:orange">Using existing ' . $label . ' data: </span><strong>'
            . $this->escape($value) . '</strong><br />';
    }

    private function skippedMessage(string $label, int $oldId): string
    {
        return '<span style="color:red">Skipping ' . $label . ' source ID: </span><strong>'
            . $oldId . '</strong><br />';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
