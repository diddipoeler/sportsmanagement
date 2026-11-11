<?php
/**
 * Joomla 5/6 SportsManagement matches module PHP implementation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementMatches\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

trait NativeQueryTrait
{
    /**
     * Normalise module selector values to unique positive numeric IDs.
     *
     * Accept Joomla scalar and multiselect values as well as delimited lists.
     * Ignore nested arrays, invalid text, zero and values outside the native
     * integer range before using IDs in bound SQL parameters.
     *
     * @param mixed $value Raw project, club or team selection from Registry.
     * @return array<int,int> Unique validated positive identifiers.
     */
    private function ids($value): array
    {
        $values = is_array($value) ? $value : [$value];
        $ids = [];

        foreach ($values as $candidate) {
            // Joomla multi-select parameters may contain comma- or semicolon-
            // separated values. Nested arrays and malformed IDs are ignored.
            if (!is_scalar($candidate)) {
                continue;
            }

            foreach (preg_split('/[\s,;]+/', (string) $candidate, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
                if (!ctype_digit($part)) {
                    continue;
                }

                $id = filter_var(ltrim($part, '0'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                if ($id !== false) {
                    $ids[$id] = $id;
                }
            }
        }

        return array_values($ids);
    }

    /**
     * Select the configured SportsManagement database for match queries.
     *
     * Delegate the component's database selector to the native resolver while
     * preserving the injected Joomla database as the default connection.
     *
     * @param Registry $params Module settings containing database selection.
     * @param DatabaseInterface $fallbackDatabase Joomla database connection.
     * @return DatabaseInterface Connection used for match and lookup queries.
     */
    private function database(Registry $params, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            (int) $params->get('cfg_which_database', 0)
        );
    }
}
