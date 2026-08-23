<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\Database\DatabaseInterface;

/** Player-specific form preparation and relation persistence for Joomla 5/6. */
final class PlayerPersistenceService
{
    public function __construct(private DatabaseInterface $db)
    {
    }

    public function prepare(array $data): array
    {
        $request = isset($data['request']) && is_array($data['request']) ? $data['request'] : [];

        foreach (['person_art', 'person_id1', 'person_id2', 'sports_type_id', 'position_id', 'agegroup_id'] as $field) {
            if (array_key_exists($field, $request)) {
                $data[$field] = $request[$field];
            }
        }
        unset($data['request']);

        foreach (['person_art', 'person_id1', 'person_id2', 'sports_type_id', 'position_id', 'agegroup_id'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = max(0, (int) $data[$field]);
            }
        }

        foreach (['height', 'weight', 'contact_id'] as $field) {
            if (array_key_exists($field, $data) && trim((string) $data[$field]) === '') {
                $data[$field] = null;
            }
        }

        foreach (['injury_date_start', 'injury_date_end', 'susp_date_start', 'susp_date_end', 'away_date_start', 'away_date_end'] as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $data[$field] = $this->normaliseDate((string) $data[$field]);
        }

        if ((int) ($data['person_art'] ?? 1) === 2) {
            $this->applyDoublePersonName($data);
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $picture = trim((string) ($data['picture'] ?? ''));

        if ($picture === '') {
            $picture = match ((int) ($data['gender'] ?? 0)) {
                1 => (string) $params->get('ph_player_men_small', $params->get('ph_player', '')),
                2 => (string) $params->get('ph_player_woman_small', $params->get('ph_player', '')),
                default => (string) $params->get('ph_player', ''),
            };
        }

        if ($picture !== '') {
            try {
                $picture = MediaHelper::getCleanMediaFieldValue($picture);
            } catch (\Throwable) {
                // Imageselect already returns a component-relative media path.
            }
        }
        $data['picture'] = $picture;

        return $data;
    }

    public function afterSave(int $personId, array $post): void
    {
        if ($personId <= 0) {
            return;
        }

        $app = Factory::getApplication();
        $app->setUserState('com_sportsmanagement.person_id', $personId);
        $app->getInput()->set('person_id', $personId);

        $jform = isset($post['jform']) && is_array($post['jform']) ? $post['jform'] : [];
        if (array_key_exists('season_ids', $jform)) {
            $this->syncSeasonAssignments(
                $personId,
                (array) $jform['season_ids'],
                (array) ($post['season_person_club_id'] ?? []),
                (array) ($post['season_person_position_id'] ?? [])
            );
        }

        if (!class_exists('sportsmanagementHelper', false)) {
            $helper = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';
            if (is_file($helper)) {
                require_once $helper;
            }
        }

        if (class_exists('sportsmanagementHelper', false) && method_exists('sportsmanagementHelper', 'saveExtraFields')) {
            \sportsmanagementHelper::saveExtraFields($post, $personId);
        }
    }

    private function syncSeasonAssignments(int $personId, array $seasonIds, array $clubIds, array $positionIds): void
    {
        $seasonIds = $this->normaliseIds($seasonIds);
        $now = Factory::getDate()->toSql();
        $userId = (int) Factory::getApplication()->getIdentity()->id;

        $query = $this->db->getQuery(true)
            ->select([$this->db->quoteName('id'), $this->db->quoteName('season_id')])
            ->from($this->db->quoteName('#__sportsmanagement_season_person_id'))
            ->where($this->db->quoteName('person_id') . ' = ' . $personId);
        $existingRows = $this->db->setQuery($query)->loadObjectList('season_id') ?: [];

        $this->db->transactionStart();
        try {
            foreach ($seasonIds as $seasonId) {
                $existing = $existingRows[$seasonId] ?? null;
                $row = (object) [
                    'person_id' => $personId,
                    'season_id' => $seasonId,
                    'club_id' => max(0, (int) ($clubIds[$seasonId] ?? 0)),
                    'position_id' => max(0, (int) ($positionIds[$seasonId] ?? 0)),
                    'modified' => $now,
                    'modified_by' => $userId,
                ];

                if ($existing) {
                    $row->id = (int) $existing->id;
                    $this->db->updateObject('#__sportsmanagement_season_person_id', $row, 'id');
                } else {
                    $this->db->insertObject('#__sportsmanagement_season_person_id', $row);
                }
            }

            $delete = $this->db->getQuery(true)
                ->delete($this->db->quoteName('#__sportsmanagement_season_person_id'))
                ->where($this->db->quoteName('person_id') . ' = ' . $personId);

            if ($seasonIds) {
                $delete->where($this->db->quoteName('season_id') . ' NOT IN (' . implode(',', $seasonIds) . ')');
            }
            $this->db->setQuery($delete)->execute();
            $this->db->transactionCommit();
        } catch (\Throwable $e) {
            $this->db->transactionRollback();
            throw $e;
        }
    }

    private function applyDoublePersonName(array &$data): void
    {
        $firstId = max(0, (int) ($data['person_id1'] ?? 0));
        $secondId = max(0, (int) ($data['person_id2'] ?? 0));
        if ($firstId === 0 || $secondId === 0) {
            return;
        }

        $sportsTypeId = max(0, (int) ($data['sports_type_id'] ?? 0));
        if ($sportsTypeId > 0) {
            $query = $this->db->getQuery(true)
                ->select($this->db->quoteName('name'))
                ->from($this->db->quoteName('#__sportsmanagement_sports_type'))
                ->where($this->db->quoteName('id') . ' = ' . $sportsTypeId);
            $sportsTypeName = (string) $this->db->setQuery($query, 0, 1)->loadResult();
            if ($sportsTypeName === 'COM_SPORTSMANAGEMENT_ST_TABLETENNIS') {
                return;
            }
        }

        $query = $this->db->getQuery(true)
            ->select([$this->db->quoteName('id'), $this->db->quoteName('firstname'), $this->db->quoteName('lastname')])
            ->from($this->db->quoteName('#__sportsmanagement_person'))
            ->where($this->db->quoteName('id') . ' IN (' . $firstId . ',' . $secondId . ')');
        $people = $this->db->setQuery($query)->loadObjectList('id') ?: [];
        $names = [];

        foreach ([$firstId, $secondId] as $personId) {
            if (!isset($people[$personId])) {
                return;
            }
            $names[] = trim((string) $people[$personId]->firstname . ' ' . (string) $people[$personId]->lastname);
        }

        $data['firstname'] = '';
        $data['lastname'] = implode(' - ', $names);
    }

    private function normaliseDate(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '' || str_starts_with($raw, '0000-00-00')) {
            return null;
        }

        foreach (['!Y-m-d', '!d-m-Y', '!d.m.Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $raw);
            if ($date instanceof \DateTimeImmutable) {
                return $date->format('Y-m-d');
            }
        }

        $timestamp = strtotime($raw);
        return $timestamp === false ? null : date('Y-m-d', $timestamp);
    }

    private function normaliseIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
    }
}
