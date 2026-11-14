<?php
/**
 * Joomla 5/6 data helper for the SportsManagement TrainingsData module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) 2015 diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementTrainingsData\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class TrainingsDataHelper
{
    /**
     * Load the configured team's training schedule for a Joomla module.
     * Validates the team ID or ID:alias slug and the database selector,
     * uses bound SQL parameters, orders sessions by weekday/start time
     * and logs database failures by exception class only, never by
     * writing sensitive SQL or driver credentials to a shared log.
     * Filters non-object driver rows and normalises nested legacy values
     * before templates read training locations, notes and numeric fields.
     * @param Registry $params TrainingsData module settings.
     * @param DatabaseInterface $fallbackDatabase Joomla fallback database.
     * @return array<int,object> Training sessions or an empty list on failure.
     */
    public function getData(Registry $params, DatabaseInterface $fallbackDatabase): array
    {
        $teamId = self::positiveId($params->get('teams', 0));

        if ($teamId <= 0) {
            return [];
        }

        $selector = self::databaseSelector($params->get('cfg_which_database', 0));

        if ($selector === null) {
            return [];
        }

        try {
            $db = $this->database($selector, $fallbackDatabase);
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('dayofweek'),
                    $db->quoteName('time_start'),
                    $db->quoteName('time_end'),
                    $db->quoteName('place'),
                    $db->quoteName('notes'),
                ])
                ->from($db->quoteName('#__sportsmanagement_team_trainingdata'))
                ->where($db->quoteName('team_id') . ' = :teamId')
                ->bind(':teamId', $teamId, ParameterType::INTEGER)
                ->order([
                    $db->quoteName('dayofweek') . ' ASC',
                    $db->quoteName('time_start') . ' ASC',
                ]);
            $db->setQuery($query);

            return self::normaliseTrainingRows($db->loadObjectList());
        } catch (\Throwable $e) {
            // Log remote/database failures server-side without exposing SQL
            // or connection information to frontend site visitors.
            Log::add(
                __METHOD__ . ': training data query failed (' . get_class($e) . ').',
                Log::ERROR,
                'jsmerror'
            );

            return [];
        }
    }

    /**
     * Convert training query results into safe, object-only template rows.
     *
     * Some older drivers can return false or mixed arrays instead of a list
     * of training objects. Discard non-object entries and replace nested
     * place/notes fields and malformed weekday/second counts with safe
     * defaults to prevent PHP 8 array-to-string conversion warnings in the
     * Joomla module layout. Preserve scalar values for valid legacy rows.
     *
     * @param mixed $raw Database result returned by loadObjectList().
     * @return array<int,object> Validated object rows for the module template.
     */
    private static function normaliseTrainingRows(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $rows = [];

        foreach ($raw as $record) {
            if (!is_object($record)) {
                continue;
            }

            foreach (['place', 'notes'] as $name) {
                $value = $record->{$name} ?? '';

                if (!is_scalar($value)) {
                    $record->{$name} = '';
                }
            }

            foreach (['dayofweek', 'time_start', 'time_end'] as $name) {
                $value = $record->{$name} ?? null;

                if (!is_int($value) && (!is_string($value)
                    || strlen($value) > 16 || preg_match('/^[0-9]+$/D', $value) !== 1)) {
                    $record->{$name} = 0;
                    continue;
                }

                $digits = is_string($value) ? ltrim($value, '0') : $value;
                $digits = $digits === '' ? '0' : $digits;

                if (filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
                    $record->{$name} = 0;
                }
            }

            $rows[] = $record;
        }

        return $rows;
    }

    /**
     * Parse a positive Joomla team identifier or ID:alias slug for training queries.
     * Accepts complete nonnegative decimal IDs with optional URL-safe alias suffixes and rejects unknown types, partial IDs or values beyond PHP's integer range.
     *
     * @param mixed $value Raw team selector from module settings.
     * @return int Valid positive team ID, or zero for invalid input.
     */
    private static function positiveId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = trim((string) $value);

        if (!preg_match('/^([0-9]+)(?::[\pL\pN_-]+)?$/Du', $raw, $matches)) {
            return 0;
        }

        $decimal = ltrim($matches[1], '0');
        $decimal = $decimal === '' ? '0' : $decimal;
        $max = (string) PHP_INT_MAX;

        if ($decimal === '0'
            || strlen($decimal) > strlen($max)
            || (strlen($decimal) === strlen($max) && strcmp($decimal, $max) > 0)) {
            return 0;
        }

        return (int) $decimal;
    }

    /**
     * Accept only the two configured SportsManagement database choices.
     * Allows integer zero/one and decimal strings with leading zeroes,
     * rejecting malformed or partially numeric values instead of coercing
     * them into another database selection.
     * @param mixed $raw Raw cfg_which_database module setting.
     * @return int|null Allowed database selector or null when invalid.
     */
    private static function databaseSelector(mixed $raw): ?int
    {
        if (is_int($raw)) {
            return $raw === 0 || $raw === 1 ? $raw : null;
        }

        if (!is_string($raw) || strlen($raw) > 64
            || !preg_match('/^0*([01])$/D', $raw, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * Resolve the already-validated database selected by the module.
     * Keeps the connection selection in the component's Joomla 5/6
     * database resolver rather than using deprecated Joomla factories.
     * @param int $selector Validated primary/external database setting.
     * @param DatabaseInterface $fallbackDatabase Joomla database service.
     * @return DatabaseInterface Effective connection for training queries.
     */
    private function database(int $selector, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve($fallbackDatabase, $selector);
    }
}
