<?php
/**
 * Native Joomla 5/6 migration helper for historical SportsManagement image paths.
 *
 * @version    5.6.0
 * @author     diddipoeler, SportsManagement contributors
 * @copyright  Copyright (C) 2013-2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/**
 * Apply the legacy image-path replacements without executing SQL at file load.
 *
 * The migration plan is intentionally fixed. No request-supplied table names,
 * columns or replacement strings enter SQL; the original operation order is
 * preserved for installations with historical media paths.
 */
final class ImagePathMigrationHelper
{
    /** @var list<array{string,string,string,string}> */
    private const REPLACEMENTS = [
        ['#__sportsmanagement_person', 'picture', 'placeholders', 'persons'],
        ['#__sportsmanagement_person', 'picture', 'media', 'images'],
        ['#__sportsmanagement_club', 'logo_big', 'placeholders', 'clubs/large'],
        ['#__sportsmanagement_club', 'logo_middle', 'placeholders', 'clubs/medium'],
        ['#__sportsmanagement_club', 'logo_small', 'placeholders', 'clubs/small'],
        ['#__sportsmanagement_club', 'logo_big', 'media', 'images'],
        ['#__sportsmanagement_club', 'logo_middle', 'media', 'images'],
        ['#__sportsmanagement_club', 'logo_small', 'media', 'images'],
        ['#__sportsmanagement_club', 'logo_big', 'com_sportsmanagement/clubs/large', 'com_sportsmanagement/database/clubs/large'],
        ['#__sportsmanagement_club', 'logo_middle', 'com_sportsmanagement/clubs/medium', 'com_sportsmanagement/database/clubs/medium'],
        ['#__sportsmanagement_club', 'logo_small', 'com_sportsmanagement/clubs/small', 'com_sportsmanagement/database/clubs/small'],
        ['#__sportsmanagement_club', 'trikot_home', 'placeholders', 'clubs/trikot'],
        ['#__sportsmanagement_club', 'trikot_away', 'placeholders', 'clubs/trikot'],
        ['#__sportsmanagement_club', 'trikot_home', 'media', 'images'],
        ['#__sportsmanagement_club', 'trikot_away', 'media', 'images'],
    ];

    /**
     * Execute the 15 historical media-path replacements against one database.
     *
     * Quotes every identifier and replacement literal through Joomla's current
     * database API, writes only fields from the private migration allowlist,
     * and counts affected rows for the administrator's confirmation message.
     * An explicit false execution result or database exception aborts the
     * remaining updates instead of announcing an incomplete migration as done.
     *
     * @param DatabaseInterface $database Approved SportsManagement connection.
     * @return array{operations:int,updatedRows:int} Executed steps and affected rows.
     * @throws \RuntimeException When an SQL statement reports failure.
     */
    public static function migrate(DatabaseInterface $database): array
    {
        $operations = 0;
        $updatedRows = 0;

        foreach (self::REPLACEMENTS as [$table, $column, $old, $new]) {
            $quotedColumn = $database->quoteName($column);
            $query = $database->createQuery()
                ->update($database->quoteName($table))
                ->set($quotedColumn . ' = REPLACE('
                    . $quotedColumn . ', ' . $database->quote($old)
                    . ', ' . $database->quote($new) . ')')
                ->where($quotedColumn . ' LIKE ' . $database->quote('%' . $old . '%'));

            $database->setQuery($query);

            if ($database->execute() === false) {
                throw new \RuntimeException('SportsManagement image-path migration failed.');
            }

            ++$operations;
            $updatedRows += max(0, (int) $database->getAffectedRows());
        }

        return ['operations' => $operations, 'updatedRows' => $updatedRows];
    }
}
