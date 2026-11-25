<?php
/**
 * Joomla 5/6 data helper for the SportsManagement UEFA ranking module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  (C) 2015-2026
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementUefaWertung\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class UefaWertungHelper
{
    /**
     * Build UEFA coefficient rankings over the latest available five seasons.
     * Validates season and database selectors, resolves the configured
     * SportsManagement connection, and aggregates coefficient seasons.
     * Invalid selectors are rejected before any service lookup, and
     * exception messages containing SQL or credentials are not logged.
     *
     * @param Registry $params UEFA ranking module settings.
     * @param CMSApplicationInterface $app Frontend application for user-visible errors.
     * @param DatabaseInterface $fallbackDatabase Joomla's primary database service.
     * @return array{seasons:array<int,string>,rankings:array<int,array{country:string,points:array<string,float>,total:float}>} Ranked countries and season labels.
     */
    public function getData(
        Registry $params,
        CMSApplicationInterface $app,
        DatabaseInterface $fallbackDatabase
    ): array {
        $seasonId = self::positiveSeasonId($params->get('s', 0));

        if ($seasonId <= 0) {
            return ['seasons' => [], 'rankings' => []];
        }

        $selector = self::databaseSelector($params->get('cfg_which_database', 0));

        if ($selector === null) {
            return ['seasons' => [], 'rankings' => []];
        }

        try {
            // Resolver failures (including unavailable external databases)
            // need the same guarded path as the following queries.
            $db = SportsManagementDatabaseResolver::resolve(
                $fallbackDatabase,
                $selector
            );

            $seasonName = $this->seasonName($db, $seasonId);

            if ($seasonName === '') {
                return ['seasons' => [], 'rankings' => []];
            }

            $seasons = $this->rankingSeasons($db, $seasonName);

            if ($seasons === []) {
                return ['seasons' => [], 'rankings' => []];
            }

            $rankings = $this->rankings($db, $seasons);

            return [
                'seasons' => $seasons,
                'rankings' => $rankings,
            ];
        } catch (\Throwable $error) {
            // Do not expose SQL, table names or connection credentials.
            Log::add(
                __METHOD__ . ': UEFA ranking lookup failed (' . get_class($error) . ').',
                Log::ERROR,
                'com_sportsmanagement'
            );
            $app->enqueueMessage('SportsManagement UEFA rankings could not be loaded.', 'error');

            return ['seasons' => [], 'rankings' => []];
        }
    }

    /**
     * Parse the configured UEFA ranking season identifier without lossy PHP casting.
     * Supports a complete decimal season ID or ID:alias slug while rejecting booleans, partial strings, zero and integer overflow.
     * Rejects oversized selectors and surrounding whitespace or control bytes
     * before parsing, rather than silently normalising malformed input.
     *
     * @param mixed $value Raw Joomla season selector.
     * @return int Positive season record ID, or zero if invalid.
     */
    private static function positiveSeasonId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = (string) $value;

        if (strlen($raw) > 128 || trim($raw) !== $raw) {
            return 0;
        }

        if (!preg_match('/^([0-9]+)(?::[\pL\pN_-]+)?$/Du', $raw, $parts)) {
            return 0;
        }

        $digits = ltrim($parts[1], '0');
        $digits = $digits === '' ? '0' : $digits;
        $max = (string) PHP_INT_MAX;

        if ($digits === '0'
            || strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return 0;
        }

        return (int) $digits;
    }

    /**
     * Select only the primary or configured external SportsManagement database.
     *
     * Legacy zero-padded decimal selectors are accepted, but booleans,
     * nested form values, invalid numeric text and out-of-range settings
     * must not fall back to an unrelated database connection.
     *
     * @param mixed $value Raw Joomla module database selector.
     * @return int|null Database choice zero/one, or null when malformed.
     */
    private static function databaseSelector(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value === 0 || $value === 1 ? $value : null;
        }

        if (!is_string($value) || strlen($value) > 64
            || preg_match('/^0*([01])$/D', $value, $parts) !== 1) {
            return null;
        }

        return (int) $parts[1];
    }

    /**
     * Resolve the human-readable season label for a UEFA ranking query.
     * Looks up a single season by its bound numeric ID. Only printable,
     * bounded text labels are accepted before collecting ranking seasons;
     * malformed database values cannot cause PHP 8 cast warnings.
     * @param DatabaseInterface $db Active SportsManagement database.
     * @param int $seasonId Requested season identifier.
     * @return string Season label, or an empty string when not found.
     */
    private function seasonName(DatabaseInterface $db, int $seasonId): string
    {
        $query = $db->createQuery()
            ->select($db->quoteName('name'))
            ->from($db->quoteName('#__sportsmanagement_season'))
            ->where($db->quoteName('id') . ' = :seasonId')
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return self::printableText($db->loadResult());
    }

    /**
     * Select up to five ranking seasons ending with the requested season label.
     * Retrieves distinct available UEFA season names through a bound query.
     * Skips malformed or oversized rows instead of casting arrays, objects
     * and booleans to strings. Returns up to five printable season labels in
     * natural chronological order.
     *
     * @param DatabaseInterface $db Selected SportsManagement database driver.
     * @param string $seasonName Upper season label boundary.
     * @return array<int,string> Up to five chronological UEFA ranking season labels.
     */
    private function rankingSeasons(DatabaseInterface $db, string $seasonName): array
    {
        $query = $db->createQuery()
            ->select($db->quoteName('season'))
            ->from($db->quoteName('#__sportsmanagement_uefawertung'))
            ->where($db->quoteName('season') . ' <= :seasonName')
            ->bind(':seasonName', $seasonName, ParameterType::STRING)
            ->group($db->quoteName('season'))
            ->order($db->quoteName('season') . ' DESC');
        $db->setQuery($query, 0, 5);
        $rawSeasons = $db->loadColumn();

        if (!is_array($rawSeasons)) {
            return [];
        }

        $seasons = [];

        foreach ($rawSeasons as $rawSeason) {
            $season = self::printableText($rawSeason);

            if ($season !== '' && !in_array($season, $seasons, true)) {
                $seasons[] = $season;
            }

            if (count($seasons) === 5) {
                break;
            }
        }

        sort($seasons, SORT_NATURAL);

        return $seasons;
    }

    /**
     * Extract short, printable season and country labels from database rows.
     *
     * Accepts string and integer legacy season names, but not booleans or
     * nested values. Oversized values and control bytes are discarded before
     * they can be used as array keys, SQL season selectors or template text.
     *
     * @param mixed $value Imported name or season lookup result.
     * @return string Trimmed printable text, or an empty string if malformed.
     */
    private static function printableText(mixed $value): string
    {
        if (!is_string($value) && !is_int($value)) {
            return '';
        }

        $text = (string) $value;

        if (strlen($text) > 128
            || preg_match('/[\x00-\x1F\x7F]/', $text) === 1) {
            return '';
        }

        return trim($text);
    }

    /**
     * Aggregate UEFA points by country across all selected ranking seasons.
     * Loads per-country season scores and discards corrupt rows, nested or
     * oversized labels, nonnumeric points and nonfinite numeric results.
     * Countries missing a valid season are excluded rather than treating
     * malformed points as zero. Nonfinite totals are discarded and numeric
     * country keys are converted back to strings for the layout.
     * Complete totals are sorted descending with
     * alphabetical ties.
     *
     * @param DatabaseInterface $db Selected SportsManagement database driver.
     * @param array<int,string> $seasons Chronologically selected UEFA coefficient seasons.
     * @return array<int,array{country:string,points:array<string,float>,total:float}> Countries ranked by combined points.
     */
    private function rankings(DatabaseInterface $db, array $seasons): array
    {
        $query = $db->createQuery()
            ->select([
                $db->quoteName('country'),
                $db->quoteName('season'),
                $db->quoteName('points'),
            ])
            ->from($db->quoteName('#__sportsmanagement_uefawertung'))
            ->whereIn($db->quoteName('season'), $seasons, ParameterType::STRING)
            ->order([
                $db->quoteName('country') . ' ASC',
                $db->quoteName('season') . ' ASC',
            ]);
        $db->setQuery($query);

        $rawRows = $db->loadObjectList();

        if (!is_array($rawRows)) {
            return [];
        }

        $countries = [];

        foreach ($rawRows as $row) {
            if (!is_object($row)) {
                continue;
            }

            $country = self::printableText($row->country ?? null);
            $season = self::printableText($row->season ?? null);
            $rawPoints = $row->points ?? null;

            if ($country === '' || !in_array($season, $seasons, true)
                || (!is_string($rawPoints) && !is_int($rawPoints) && !is_float($rawPoints))
                || !is_numeric($rawPoints)) {
                continue;
            }

            $points = (float) $rawPoints;

            if (!is_finite($points)) {
                continue;
            }

            $countries[$country][$season] = $points;
        }

        $rankings = [];

        foreach ($countries as $country => $points) {
            if (count(array_intersect_key(array_flip($seasons), $points)) !== count($seasons)) {
                continue;
            }

            $orderedPoints = [];
            $total = 0.0;

            foreach ($seasons as $season) {
                $value = (float) ($points[$season] ?? 0);
                $orderedPoints[$season] = $value;
                $total += $value;
            }

            if (!is_finite($total)) {
                continue;
            }

            $rankings[] = [
                'country' => (string) $country,
                'points' => $orderedPoints,
                'total' => $total,
            ];
        }

        usort(
            $rankings,
            /**
             * Sort countries by descending total points and then by name.
             * Uses a case-insensitive alphabetical comparison for tied totals.
             * @param array $left First ranking row.
             * @param array $right Second ranking row.
             * @return int Comparison result for usort().
             */
            static function (array $left, array $right): int {
                $total = $right['total'] <=> $left['total'];

                return $total !== 0 ? $total : strcasecmp($left['country'], $right['country']);
            }
        );

        return $rankings;
    }
}
