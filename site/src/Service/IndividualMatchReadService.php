<?php
/**
 * Read-only access to individual-match rows for frontend roster statistics.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

final class IndividualMatchReadService
{
    /**
     * Create a read-only frontend service for individual match results.
     * Receives the Joomla database service required for scoped player-to-match lookups.
     *
     * @param DatabaseInterface $db Joomla database driver.
     * @return void
     */
    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * Fetch the individual games played by a season-team person for a project team.
     * Restricts the query to the owning project's rounds, selected project team,
     * person, match type and HOME/AWAY side. Rejects invalid record IDs and
     * unsupported match-type values and unknown HOME/AWAY selectors before
     * querying the Joomla database; unsupported sides never silently map HOME.
     * Database construction and execution failures are logged without
     * exposing SQL details in the frontend, then return an empty result so
     * that a broken optional statistics lookup does not abort the roster.
     *
     * @param int $projectId Owning project identifier.
     * @param int $projectTeamId Project-team relation identifier.
     * @param int $seasonTeamPersonId Season-team-person identifier.
     * @param string $matchType Individual game type, SINGLE by default.
     * @param string $homeAway HOME uses the first participant; AWAY uses the second.
     * @return array<int,object> Individual-match rows for the requested participant.
     */
    public function getPlayerMatches(
        int $projectId,
        int $projectTeamId,
        int $seasonTeamPersonId,
        string $matchType = 'SINGLE',
        string $homeAway = 'HOME'
    ): array {
        if ($projectId <= 0 || $projectTeamId <= 0 || $seasonTeamPersonId <= 0
            || $matchType === '' || strlen($matchType) > 32
            || preg_match('/^[A-Za-z0-9_]+$/D', $matchType) !== 1) {
            return [];
        }

        $homeAway = strtoupper($homeAway);

        if ($homeAway !== 'HOME' && $homeAway !== 'AWAY') {
            return [];
        }

        $side = $homeAway === 'AWAY' ? 2 : 1;
        $db = $this->db;
        try {
            $query = $db->createQuery()
            ->select($db->quoteName('ms') . '.*')
            ->from($db->quoteName('#__sportsmanagement_match_single', 'ms'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('ms.round_id'))
            ->where($db->quoteName('r.project_id') . ' = :projectId')
            ->where($db->quoteName('ms.projectteam' . $side . '_id') . ' = :projectTeamId')
            ->where($db->quoteName('ms.teamplayer' . $side . '_id') . ' = :seasonTeamPersonId')
            ->where($db->quoteName('ms.match_type') . ' = :matchType')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->bind(':seasonTeamPersonId', $seasonTeamPersonId, ParameterType::INTEGER)
            ->bind(':matchType', $matchType, ParameterType::STRING);
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $exception) {
            // Database diagnostics belong in server logs, not a public roster.
            Log::add(
                __METHOD__ . ': individual match lookup failed (' . get_class($exception) . ').',
                Log::ERROR,
                'com_sportsmanagement'
            );

            return [];
        }
    }
}
