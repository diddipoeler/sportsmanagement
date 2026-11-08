<?php
/**
 * Joomla 5/6 player persistence service for administrator forms and relations.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\Database\DatabaseInterface;

/** Player-specific form preparation and relation persistence for Joomla 5/6. */
final class PlayerPersistenceService
{
    /**
     * Create the administrator player persistence service.
     * Retains the supplied Joomla database dependency for profile updates and season-person relation operations.
     *
     * @param DatabaseInterface $db Joomla database driver.
     * @return void
     */
    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * Resolve the Joomla administrator application for player form processing.
     * Provides access to the current form input and user state without relying on deprecated global application APIs.
     *
     * @return CMSApplicationInterface Current administrator application.
     */
    private static function administratorApplication(): CMSApplicationInterface
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    /**
     * Normalise player form values before Joomla's model saves the record.
     * Copies approved request fields, converts numeric IDs and dates, derives double-player names and selects a safe default profile image.
     *
     * @param array $data Submitted form values, optionally containing nested request data.
     * @return array Sanitised values ready for player persistence.
     */
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

    /**
     * Finish saving player-specific user state and related records.
     * Stores the new person ID, updates season and club assignments if requested, and invokes the historical extra-field hook when available.
     *
     * @param int $personId Newly persisted player/person identifier.
     * @param array $post Original submitted request data.
     * @return void
     */
    public function afterSave(int $personId, array $post): void
    {
        if ($personId <= 0) {
            return;
        }

        $app = self::administratorApplication();
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

    /**
     * Synchronise a person's season, club and playing-position relations.
     * Normalises selected season IDs, updates or inserts current records and removes deselected assignments within a rollback-safe database transaction.
     *
     * @param int $personId Saved person identifier.
     * @param array $seasonIds Selected season IDs.
     * @param array $clubIds Club IDs indexed by season.
     * @param array $positionIds Position IDs indexed by season.
     * @return void
     */
    private function syncSeasonAssignments(int $personId, array $seasonIds, array $clubIds, array $positionIds): void
    {
        $seasonIds = $this->normaliseIds($seasonIds);
        $now = Factory::getDate()->toSql();
        $userId = (int) self::administratorApplication()->getIdentity()->id;

        $query = $this->db->createQuery()
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

            $delete = $this->db->createQuery()
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

    /**
     * Derive the display name of a doubles team from its two member profiles.
     * Reads both player records and builds a combined surname unless the configured sport explicitly requires the original team naming.
     *
     * @param array $data Player form data updated in place.
     * @return void
     */
    private function applyDoublePersonName(array &$data): void
    {
        $firstId = max(0, (int) ($data['person_id1'] ?? 0));
        $secondId = max(0, (int) ($data['person_id2'] ?? 0));
        if ($firstId === 0 || $secondId === 0) {
            return;
        }

        $sportsTypeId = max(0, (int) ($data['sports_type_id'] ?? 0));
        if ($sportsTypeId > 0) {
            $query = $this->db->createQuery()
                ->select($this->db->quoteName('name'))
                ->from($this->db->quoteName('#__sportsmanagement_sports_type'))
                ->where($this->db->quoteName('id') . ' = ' . $sportsTypeId);
            $sportsTypeName = (string) $this->db->setQuery($query, 0, 1)->loadResult();
            if ($sportsTypeName === 'COM_SPORTSMANAGEMENT_ST_TABLETENNIS') {
                return;
            }
        }

        $query = $this->db->createQuery()
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

    /**
     * Convert supported date formats to a valid database date.
     * Rejects empty, zero and impossible dates, avoiding PHP's silent date rollover and locale-dependent natural-language parsing.
     *
     * @param string $raw Date supplied by the administrator.
     * @return string|null ISO date in Y-m-d format or null.
     */
    private function normaliseDate(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '' || str_starts_with($raw, '0000-00-00')) {
            return null;
        }

        foreach (['!Y-m-d', '!Y-m-d H:i:s', '!Y-m-d H:i', '!d-m-Y', '!d.m.Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $raw);
            // PHP normalizes impossible dates (e.g. 31 February) unless
            // warnings are checked explicitly.
            $errors = \DateTimeImmutable::getLastErrors();

            if ($date instanceof \DateTimeImmutable
                && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $date->format('Y-m-d');
            }
        }

        // Avoid natural-language or ambiguous date strings being silently
        // interpreted differently across servers and PHP versions.
        return null;
    }

    /**
     * Validate and deduplicate IDs from multiselect form values.
     * Accepts positive integers and zero-padded numeric strings while rejecting arrays, booleans, partial numbers and overflowing values.
     *
     * @param array $ids Untrusted form selection values.
     * @return array<int, int> Positive unique IDs.
     */
    private function normaliseIds(array $ids): array
    {
        $normalised = [];

        foreach ($ids as $candidate) {
            if (is_int($candidate)) {
                $id = $candidate;
            } elseif (is_string($candidate) && preg_match('/^[0-9]+$/D', trim($candidate))) {
                $digits = ltrim(trim($candidate), '0');

                if ($digits === '') {
                    continue;
                }

                $id = filter_var($digits, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1],
                ]);

                if ($id === false) {
                    continue;
                }
            } else {
                // Reject partial IDs, booleans, floating-point and arrays.
                continue;
            }

            if ($id > 0) {
                $normalised[$id] = $id;
            }
        }

        return array_values($normalised);
    }
}
