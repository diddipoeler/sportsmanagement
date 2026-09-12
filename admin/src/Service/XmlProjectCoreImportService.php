<?php
/**
 * Joomla 5/6 native project core XML import service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use RuntimeException;

/** Native writer for project XML import step 14. */
final class XmlProjectCoreImportService
{
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * Store the project row after all referenced master data has been resolved.
     *
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     */
    public function import(array $post, array $parsedData, int $importProjectId = 0): int
    {
        $source = $parsedData['project'] ?? null;

        if (!is_object($source)) {
            throw new RuntimeException('Project object is missing inside import file.', 400);
        }

        $name = substr(trim(stripslashes((string) ($post['name'] ?? ''))), 0, 99);

        if ($name === '') {
            throw new RuntimeException('Missing projectname', 400);
        }

        $this->assertProjectNameAvailable($name);

        $leagueId = max(0, (int) ($post['league'] ?? 0));
        $seasonId = max(0, (int) ($post['season'] ?? 0));
        $sportTypeId = max(0, (int) ($post['sportstype'] ?? 0));

        $this->requireId('#__sportsmanagement_league', $leagueId, 'league');
        $this->requireId('#__sportsmanagement_season', $seasonId, 'season');
        $this->requireId('#__sportsmanagement_sports_type', $sportTypeId, 'sports type');

        $row = $this->filterSourceFields($source, '#__sportsmanagement_project');
        $row->name = $name;
        $row->alias = substr(OutputFilter::stringURLSafe($name), 0, 99);
        $row->league_id = $leagueId;
        $row->import_project_id = max(0, $importProjectId);
        $row->season_id = $seasonId;
        $row->admin = !empty($post['admin']) ? (int) $post['admin'] : 62;
        $row->editor = !empty($post['editor']) ? (int) $post['editor'] : 62;
        $row->master_template = !empty($post['copyTemplate']) ? (int) $post['copyTemplate'] : 0;
        $row->sub_template_id = 0;
        $row->sports_type_id = $sportTypeId;
        $row->agegroup_id = max(0, (int) ($post['agegroup_id'] ?? 0));
        $row->picture = (string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_project', '');
        $row->fav_team = $this->mapFavoriteTeams($source, $post, $parsedData);

        $timezone = $post['timezone'] ?? null;

        if (is_numeric($timezone)) {
            $zones = \DateTimeZone::listIdentifiers();
            $timezoneIndex = (int) $timezone;

            if (array_key_exists($timezoneIndex, $zones)) {
                $row->timezone = $zones[$timezoneIndex];
            }
        }

        // Keep the historical behavior: a checked publish control forces 1,
        // while an unchecked control leaves the imported project value intact.
        if (!empty($post['publish'])) {
            $row->published = 1;
        }

        $row = $this->filterTableFields($row, '#__sportsmanagement_project');

        if (!$this->database->insertObject('#__sportsmanagement_project', $row)) {
            throw new RuntimeException('Unable to store imported project: ' . $name, 500);
        }

        $projectId = (int) $this->database->insertid();

        if ($projectId <= 0) {
            throw new RuntimeException('Imported project has no database ID.', 500);
        }

        return $projectId;
    }

    /**
     * Native equivalent of the legacy _beforeFinish() favorite-team mapping.
     *
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     */
    private function mapFavoriteTeams(object $project, array $post, array $parsedData): string
    {
        $favoriteIds = array_filter(
            array_map('intval', explode(',', trim((string) ($project->fav_team ?? '')))),
            static fn (int $id): bool => $id > 0
        );

        if ($favoriteIds === []) {
            return '';
        }

        $teamMap = [];

        foreach (array_values((array) ($parsedData['team'] ?? [])) as $key => $team) {
            if (!is_object($team)) {
                continue;
            }

            $oldId = (int) ($team->id ?? 0);
            $databaseId = max(0, (int) ($post['dbTeamID_' . $key] ?? 0));

            if ($oldId > 0 && $databaseId > 0) {
                $teamMap[$oldId] = $databaseId;
            }
        }

        $mapped = [];

        foreach ($favoriteIds as $oldId) {
            if (isset($teamMap[$oldId])) {
                $mapped[] = $teamMap[$oldId];
            }
        }

        return implode(',', $mapped);
    }

    private function assertProjectNameAvailable(string $name): void
    {
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('id'))
            ->from($this->database->quoteName('#__sportsmanagement_project'))
            ->where($this->database->quoteName('name') . ' = :name')
            ->bind(':name', $name, ParameterType::STRING);
        $this->database->setQuery($query, 0, 1);

        if ($this->database->loadResult() !== null) {
            throw new RuntimeException('Projectname already exists', 409);
        }
    }

    private function requireId(string $table, int $id, string $label): void
    {
        if ($id <= 0) {
            throw new RuntimeException('Missing ' . $label . ' for project XML import.', 400);
        }

        $query = $this->database->createQuery()
            ->select($this->database->quoteName('id'))
            ->from($this->database->quoteName($table))
            ->where($this->database->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);

        if ($this->database->loadResult() === null) {
            throw new RuntimeException('Selected ' . $label . ' was not found.', 404);
        }
    }

    private function filterSourceFields(object $source, string $table): object
    {
        $columns = $this->database->getTableColumns($table);
        $row = new \stdClass();

        foreach ($source as $field => $value) {
            $field = (string) $field;

            if ($field === 'id' || !array_key_exists($field, $columns)) {
                continue;
            }

            $row->{$field} = is_scalar($value) ? (string) $value : $value;
        }

        return $row;
    }

    private function filterTableFields(object $source, string $table): object
    {
        $columns = $this->database->getTableColumns($table);
        $row = new \stdClass();

        foreach ($source as $field => $value) {
            if (array_key_exists((string) $field, $columns)) {
                $row->{$field} = $value;
            }
        }

        return $row;
    }
}
