<?php
/**
 * Joomla 5/6 helper for adjacent SportsManagement project navigation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Resolve adjacent projects without relying on the legacy static project model.
 */
final class ProjectNavigationHelper
{
    /**
     * Find the preceding project in the current project's league.
     *
     * Uses the supplied Joomla database connection and the shared
     * navigation query to search for the nearest lower project name.
     *
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param object $project Current project containing league_id and name.
     * @return object|null Previous project record, or null if none exists.
     */
    public static function previous(DatabaseInterface $db, object $project): ?object
    {
        return self::find($db, $project, false);
    }

    /**
     * Find the following project within the current project's league.
     *
     * Uses the same parameterised query as previous(), ordered by
     * ascending name to select the nearest following project.
     *
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param object $project Current project containing league_id and name.
     * @return object|null Next project record, or null if none exists.
     */
    public static function next(DatabaseInterface $db, object $project): ?object
    {
        return self::find($db, $project, true);
    }

    /**
     * Query the nearest project in a requested navigation direction.
     *
     * Rejects empty project names and invalid league IDs, binds league
     * and name parameters using Joomla DatabaseInterface, and limits
     * the result to one neighbouring project in the same league. Where the
     * current project has an ID, equal-name neighbours are ordered by that
     * ID to avoid skipping projects with identical display names.
     * Malformed league selectors cannot be silently truncated by PHP casts.
     * Builds ID:alias slugs in PHP rather than MySQL-only CONCAT_WS, keeping
     * the query portable to PostgreSQL database drivers.
     *
     * @param DatabaseInterface $db Joomla database connection.
     * @param object $project Current project providing league and name.
     * @param bool $next True to search forward; false for backward.
     * @return object|null Adjacent project or null if no match exists.
     */
    private static function find(DatabaseInterface $db, object $project, bool $next): ?object
    {
        $rawLeagueId = $project->league_id ?? null;
        $leagueId = self::positiveId($rawLeagueId);
        $name = is_string($project->name ?? null) ? trim($project->name) : '';
        $projectId = self::positiveId($project->id ?? null);

        if ($leagueId <= 0 || $name === '') {
            return null;
        }

        $operator = $next ? '>' : '<';
        $direction = $next ? 'ASC' : 'DESC';
        $query = $db->createQuery()
            ->select($db->quoteName('p') . '.*')
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.league_id') . ' = :navigationLeagueId')
            ->bind(':navigationLeagueId', $leagueId, ParameterType::INTEGER)
            ->bind(':navigationProjectName', $name, ParameterType::STRING);

        if ($projectId > 0) {
            $query->where('(' . $db->quoteName('p.name') . ' ' . $operator . ' :navigationProjectName'
                . ' OR (' . $db->quoteName('p.name') . ' = :navigationEqualName'
                . ' AND ' . $db->quoteName('p.id') . ' ' . $operator . ' :navigationProjectId))')
                ->bind(':navigationEqualName', $name, ParameterType::STRING)
                ->bind(':navigationProjectId', $projectId, ParameterType::INTEGER);
        } else {
            // Legacy callers without a project ID retain name-only navigation.
            $query->where($db->quoteName('p.name') . ' ' . $operator . ' :navigationProjectName');
        }

        $query->order($db->quoteName('p.name') . ' ' . $direction)
            ->order($db->quoteName('p.id') . ' ' . $direction);

        $db->setQuery($query, 0, 1);

        $neighbour = $db->loadObject();

        if (!is_object($neighbour)) {
            return null;
        }

        $neighbourId = self::positiveId($neighbour->id ?? null);
        if ($neighbourId <= 0) {
            return null;
        }

        $alias = is_string($neighbour->alias ?? null) ? $neighbour->alias : '';
        $neighbour->slug = $neighbourId . ':' . $alias;

        return $neighbour;
    }
    /**
     * Parse a positive project or league ID without accepting partial numbers.
     *
     * @param mixed $value Legacy database identifier.
     * @return int Positive integer or zero when malformed.
     */
    private static function positiveId(mixed $value): int
    {
        if (!is_int($value) && (!is_string($value)
            || strlen($value) > 64 || preg_match('/^[0-9]+$/D', $value) !== 1)) {
            return 0;
        }

        $digits = is_string($value) ? ltrim($value, '0') : $value;
        if ($digits === '') {
            return 0;
        }

        $parsed = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $parsed === false ? 0 : $parsed;
    }
}
