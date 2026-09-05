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

/**
 * Resolve adjacent projects without relying on the legacy static project model.
 */
final class ProjectNavigationHelper
{
    public static function previous(DatabaseInterface $db, object $project): ?object
    {
        return self::find($db, $project, false);
    }

    public static function next(DatabaseInterface $db, object $project): ?object
    {
        return self::find($db, $project, true);
    }

    private static function find(DatabaseInterface $db, object $project, bool $next): ?object
    {
        $leagueId = (int) ($project->league_id ?? 0);
        $name = trim((string) ($project->name ?? ''));

        if ($leagueId <= 0 || $name === '') {
            return null;
        }

        $operator = $next ? '>' : '<';
        $direction = $next ? 'ASC' : 'DESC';
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p') . '.*',
                "CONCAT_WS(':', p.id, p.alias) AS slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.league_id') . ' = ' . $leagueId)
            ->where($db->quoteName('p.name') . ' ' . $operator . ' ' . $db->quote($name))
            ->order($db->quoteName('p.name') . ' ' . $direction);

        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }
}
