<?php
/**
 * Joomla 5/6 native resolver for early project XML import data.
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

/**
 * Resolves independent project rows before the remaining legacy ID graph runs.
 *
 * Project steps 1-5 are written natively. Their real database IDs are written
 * back into the historical form fields so the legacy importer can keep building
 * its conversion maps for the still-dependent project objects.
 */
final class XmlProjectReferenceImportService
{
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     *
     * @return array{post:array<string,mixed>,messages:list<string>}
     */
    public function prepare(array $post, array $parsedData, string $step): array
    {
        $this->validateProject($post, $parsedData);

        $messages = [];
        $sportTypeId = max(0, (int) ($post['sportstype'] ?? 0));

        if (version_compare($step, '1', 'ge')) {
            $sportType = $this->resolveSportsType($post);
            $sportTypeId = (int) $sportType->id;
            $post['sportstype'] = $sportTypeId;
            $post['sportstypeNew'] = '';
            $messages[] = $this->message('sportstype', (string) $sportType->name, (bool) $sportType->created);
        }

        if (version_compare($step, '2', 'ge')) {
            if ($sportTypeId <= 0) {
                throw new RuntimeException('Missing sports type for project XML import.', 400);
            }

            $league = $this->resolveLeague($post, $parsedData, $sportTypeId);
            $post['league'] = (int) $league->id;
            $post['leagueNew'] = '';
            $messages[] = $this->message('league', (string) $league->name, (bool) $league->created);
        }

        if (version_compare($step, '3', 'ge')) {
            $season = $this->resolveSeason($post);
            $post['season'] = (int) $season->id;
            $post['seasonNew'] = '';
            $messages[] = $this->message('season', (string) $season->name, (bool) $season->created);
        }

        if (version_compare($step, '4', 'ge')) {
            (new XmlEventImportService($this->database))->import($post, $parsedData);
            $post = $this->promoteEventIds($post, $parsedData);
        }

        if (version_compare($step, '5', 'ge')) {
            (new XmlStatisticImportService($this->database))->import($post, $parsedData);
            $post = $this->promoteStatisticIds($post, $parsedData);
        }

        return [
            'post' => $post,
            'messages' => $messages,
        ];
    }

    /**
     * Mirror the legacy project-name/object checks before any native insert.
     *
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     */
    private function validateProject(array $post, array $parsedData): void
    {
        if (!array_key_exists('name', $post)) {
            throw new RuntimeException('Missing projectname', 400);
        }

        if (!isset($parsedData['project']) || !is_object($parsedData['project'])) {
            throw new RuntimeException('Project object is missing inside import file.', 400);
        }

        $projectName = substr(stripslashes((string) $post['name']), 0, 100);
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('id'))
            ->from($this->database->quoteName('#__sportsmanagement_project'))
            ->where($this->database->quoteName('name') . ' = :projectName')
            ->bind(':projectName', $projectName, ParameterType::STRING);
        $this->database->setQuery($query, 0, 1);

        if ($this->database->loadResult() !== null) {
            throw new RuntimeException('Projectname already exists', 409);
        }
    }

    /** @param array<string, mixed> $post */
    private function resolveSportsType(array $post): object
    {
        $id = max(0, (int) ($post['sportstype'] ?? 0));

        if ($id > 0) {
            return $this->requireById('#__sportsmanagement_sports_type', $id, 'sports type');
        }

        $name = substr(trim((string) ($post['sportstypeNew'] ?? '')), 0, 25);

        if ($name === '') {
            throw new RuntimeException('Missing sports type for project XML import.', 400);
        }

        $existing = $this->findByName('#__sportsmanagement_sports_type', $name);

        if ($existing !== null) {
            $existing->created = false;

            return $existing;
        }

        $row = (object) ['name' => $name];

        if (!$this->database->insertObject('#__sportsmanagement_sports_type', $row)) {
            throw new RuntimeException('Unable to store project sports type: ' . $name, 500);
        }

        $row->id = (int) $this->database->insertid();
        $row->created = true;

        return $row;
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     */
    private function resolveLeague(array $post, array $parsedData, int $sportTypeId): object
    {
        $id = max(0, (int) ($post['league'] ?? 0));

        if ($id > 0) {
            return $this->requireById('#__sportsmanagement_league', $id, 'league');
        }

        $name = substr(trim((string) ($post['leagueNew'] ?? '')), 0, 75);

        if ($name === '') {
            throw new RuntimeException('Missing league for project XML import.', 400);
        }

        $leagueName = substr($name, 0, 74);
        $existing = $this->findByName('#__sportsmanagement_league', $leagueName);

        if ($existing !== null) {
            $existing->created = false;

            return $existing;
        }

        $slug = OutputFilter::stringURLSafe($name);
        $source = isset($parsedData['league']) && is_object($parsedData['league'])
            ? $parsedData['league']
            : null;
        $row = (object) [
            'name' => $leagueName,
            'alias' => substr($slug, 0, 74),
            'short_name' => substr($slug, 0, 14),
            'middle_name' => substr($slug, 0, 24),
            'country' => trim((string) ($source->country ?? '')),
            'sports_type_id' => $sportTypeId,
            'agegroup_id' => max(0, (int) ($post['agegroup_id'] ?? 0)),
            'picture' => (string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_big', ''),
        ];
        $row = $this->filterTableFields($row, '#__sportsmanagement_league');

        if (!$this->database->insertObject('#__sportsmanagement_league', $row)) {
            throw new RuntimeException('Unable to store project league: ' . $leagueName, 500);
        }

        $row->id = (int) $this->database->insertid();
        $row->name = $leagueName;
        $row->created = true;

        return $row;
    }

    /** @param array<string, mixed> $post */
    private function resolveSeason(array $post): object
    {
        $id = max(0, (int) ($post['season'] ?? 0));

        if ($id > 0) {
            return $this->requireById('#__sportsmanagement_season', $id, 'season');
        }

        $name = substr(trim((string) ($post['seasonNew'] ?? '')), 0, 75);

        if ($name === '') {
            throw new RuntimeException('Missing season for project XML import.', 400);
        }

        $existing = $this->findByName('#__sportsmanagement_season', $name);

        if ($existing !== null) {
            $existing->created = false;

            return $existing;
        }

        $row = (object) [
            'name' => $name,
            'alias' => OutputFilter::stringURLSafe($name),
        ];
        $row = $this->filterTableFields($row, '#__sportsmanagement_season');

        if (!$this->database->insertObject('#__sportsmanagement_season', $row)) {
            throw new RuntimeException('Unable to store project season: ' . $name, 500);
        }

        $row->id = (int) $this->database->insertid();
        $row->name = $name;
        $row->created = true;

        return $row;
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     *
     * @return array<string, mixed>
     */
    private function promoteEventIds(array $post, array $parsedData): array
    {
        foreach (array_values((array) ($parsedData['event'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $field = 'dbEventID_' . $key;
            $databaseId = max(0, (int) ($post[$field] ?? 0));

            if ($databaseId > 0) {
                $this->requireById('#__sportsmanagement_eventtype', $databaseId, 'event type');
                continue;
            }

            if (!array_key_exists('eventID_' . $key, $post)) {
                continue;
            }

            $name = trim((string) ($post['eventName_' . $key] ?? ($source->name ?? '')));

            if ($name === '') {
                throw new RuntimeException('Missing event name for project XML import.', 400);
            }

            $event = $this->findByName('#__sportsmanagement_eventtype', $name);

            if ($event === null) {
                throw new RuntimeException('Prepared project event type was not found: ' . $name, 500);
            }

            $post[$field] = (int) $event->id;
            unset($post['eventID_' . $key]);
        }

        return $post;
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     *
     * @return array<string, mixed>
     */
    private function promoteStatisticIds(array $post, array $parsedData): array
    {
        foreach (array_values((array) ($parsedData['statistic'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $field = 'dbStatisticID_' . $key;
            $databaseId = max(0, (int) ($post[$field] ?? 0));

            if ($databaseId > 0) {
                $this->requireById('#__sportsmanagement_statistic', $databaseId, 'statistic');
                continue;
            }

            if (!array_key_exists('statisticID_' . $key, $post)) {
                continue;
            }

            $name = trim((string) ($post['statisticName_' . $key] ?? ($source->name ?? '')));
            $class = trim((string) ($source->class ?? ''));

            if ($name === '') {
                throw new RuntimeException('Missing statistic name for project XML import.', 400);
            }

            $statistic = $this->findStatisticByNameAndClass($name, $class);

            if ($statistic === null) {
                throw new RuntimeException('Prepared project statistic was not found: ' . $name, 500);
            }

            $post[$field] = (int) $statistic->id;
            unset($post['statisticID_' . $key]);
        }

        return $post;
    }

    private function requireById(string $table, int $id, string $label): object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName($table))
            ->where($this->database->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);
        $row = $this->database->loadObject();

        if (!$row) {
            throw new RuntimeException('Selected ' . $label . ' was not found.', 404);
        }

        $row->created = false;

        return $row;
    }

    private function findByName(string $table, string $name): ?object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName($table))
            ->where($this->database->quoteName('name') . ' = :name')
            ->bind(':name', $name, ParameterType::STRING);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    private function findStatisticByNameAndClass(string $name, string $class): ?object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_statistic'))
            ->where($this->database->quoteName('name') . ' = :name')
            ->where($this->database->quoteName('class') . ' = :class')
            ->bind(':name', $name, ParameterType::STRING)
            ->bind(':class', $class, ParameterType::STRING);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
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

    private function message(string $type, string $name, bool $created): string
    {
        $action = $created ? 'Created new ' : 'Using existing ';
        $color = $created ? 'green' : 'orange';

        return '<span style="color:' . $color . '">' . $action . $type . ' data: </span><strong>'
            . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</strong><br />';
    }
}
