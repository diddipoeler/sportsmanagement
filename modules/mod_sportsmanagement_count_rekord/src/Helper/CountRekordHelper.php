<?php
/**
 * Joomla 5/6 data helper for mod_sportsmanagement_count_rekord.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementCountRekord\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class CountRekordHelper
{
    /**
     * Build the record-count module data for the configured match target.
     *
     * Checks whether the match counter is enabled, resolves the selected
     * SportsManagement database, counts existing fixtures and compares
     * the result with the configured target. Database failures are logged
     * and represented as an empty collection to keep the site responsive.
     * Malformed database selectors fail closed instead of silently falling
     * back to an unrelated primary connection.
     *
     * @param Registry $params Validated Joomla module parameters.
     * @param object $module Current Joomla module record, retained for compatibility.
     * @param DatabaseInterface $fallbackDatabase Default Joomla connection.
     * @return array Record count, target, difference and translated output.
     */
    public function getData(Registry $params, object $module, DatabaseInterface $fallbackDatabase): array
    {
        $enabled = $params->get('jsm_stat_spielpaarungen', 0);

        if ($enabled !== true && self::nonNegativeInt($enabled) !== 1) {
            return [];
        }

        $selector = self::databaseSelector($params->get('cfg_which_database', 0));

        if ($selector === null) {
            return [];
        }

        try {
            $db = $this->database($selector, $fallbackDatabase);
            $query = $db->createQuery()
                ->select('COUNT(*)')
                ->from($db->quoteName('#__sportsmanagement_match'));
            $db->setQuery($query);
            $count = self::nonNegativeInt($db->loadResult());
        } catch (\Throwable $e) {
            // A missing match table or unavailable external database must not
            // take down the site; details belong in the Joomla error log.
            Log::add('SportsManagement Count Record query failed (' . get_class($e) . ').', Log::ERROR, 'com_sportsmanagement');

            return [];
        }

        $target = self::nonNegativeInt($params->get('jsm_stat_paarungen', 0));
        $difference = max(0, $target - $count);
        $reached = $count >= $target;

        return [(object) [
            'image' => 'modules/mod_sportsmanagement_count_rekord/images/matches.png',
            'anzahl' => $count,
            'anzahlbis' => $target,
            'anzahldiff' => $difference,
            'text' => Text::sprintf(
                $reached ? 'SHOW_MATCHES_TARGET_REACHED' : 'SHOW_MATCHES_DIFF',
                '<strong>' . number_format($reached ? $count : $difference, 0, ',', '.') . '</strong>',
                '<strong>' . number_format($target, 0, ',', '.') . '</strong>'
            ),
        ]];
    }

    /**
     * Convert a numeric module setting to a validated nonnegative integer.
     *
     * Rejects arrays, booleans, partial decimal strings, negative values
     * and integers too large for the runtime. Normalises leading zeros
     * without silently converting malformed settings into valid IDs.
     *
     * @param mixed $raw Raw Joomla module parameter.
     * @return int Parsed nonnegative integer, or zero for invalid input.
     */
    private static function nonNegativeInt(mixed $raw): int
    {
        if (!is_int($raw) && (!is_string($raw) || !preg_match('/^[0-9]+$/D', $raw))) {
            return 0;
        }

        if (is_string($raw)) {
            $digits = ltrim($raw, '0');
            $raw = $digits === '' ? '0' : $digits;
        }

        $number = filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $number === false ? 0 : $number;
    }

    /**
     * Validate the configured database choice before opening a connection.
     *
     * Accepts only database selectors zero and one, including zero-padded
     * legacy strings. Rejects booleans, arrays, out-of-range integers and
     * malformed strings so an invalid selector never resolves database zero.
     *
     * @param mixed $value Raw module database setting.
     * @return int|null Database selector zero or one, or null if invalid.
     */
    private static function databaseSelector(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value === 0 || $value === 1 ? $value : null;
        }

        if (!is_string($value) || strlen($value) > 64
            || preg_match('/^0*([01])$/D', $value, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * Select the database connection used for match record counting.
     *
     * Uses the central SportsManagement resolver with a validated database
     * selector and the provided Joomla connection as the fallback.
     *
     * @param int $selector Validated module database selection.
     * @param DatabaseInterface $fallbackDatabase Default Joomla connection.
     * @return DatabaseInterface Selected SportsManagement connection.
     */
    private function database(int $selector, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            $selector
        );
    }
}
