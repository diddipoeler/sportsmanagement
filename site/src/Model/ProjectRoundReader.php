<?php
/**
 * Joomla 5/6 project round reader.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

final class ProjectRoundReader
{
    /**
     * Create a project-scoped, read-only round selector.
     *
     * Negative project IDs are reset to zero, so they cannot broaden
     * fixture queries or select another SportsManagement project.
     *
     * @param DatabaseInterface $database Component database connection.
     * @param int $projectId Project identifier.
     * @return void
     */
    public function __construct(
        private DatabaseInterface $database,
        private int $projectId
    ) {
        $this->projectId = max(0, $this->projectId);
    }

    /**
     * Read rounds belonging to this project in numeric round-code order.
     *
     * Restricts sorting to ASC/DESC and generates Joomla ID:alias slugs in
     * PHP instead of requiring MySQL's CONCAT_WS expression.
     *
     * @param string $ordering Requested ASC/DESC round direction.
     * @param bool $slug Whether to convert round IDs to ID:alias values.
     * @return array<int,object> Project round rows.
     */
    public function getRounds(string $ordering = 'ASC', bool $slug = true): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $direction = strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';
        $db = $this->database;
        $projectId = $this->projectId;
        $query = $db->createQuery();

        $query->select([
                $db->quoteName('r.id'),
                $db->quoteName('r.alias'),
                $db->quoteName('r.round_date_first'),
                $db->quoteName('r.round_date_last'),
                'CASE LENGTH(' . $db->quoteName('r.name') . ') WHEN 0 THEN ' . $db->quoteName('r.roundcode') . ' ELSE ' . $db->quoteName('r.name') . ' END AS ' . $db->quoteName('name'),
                $db->quoteName('r.roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = :roundListProjectId')
            ->bind(':roundListProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('r.roundcode') . ' ' . $direction);

        $db->setQuery($query);
        $rounds = $db->loadObjectList() ?: [];

        if ($slug) {
            foreach ($rounds as $round) {
                if (is_object($round)) {
                    $round->id = (int) ($round->id ?? 0) . ':' . (string) ($round->alias ?? '');
                }
            }
        }

        return $rounds;
    }

    /**
     * Return the effective round ID without modifying project records.
     *
     * The legacy $persist argument remains accepted for compatible callers
     * but no longer authorises a database write from the public site.
     *
     * @param object|null $project Optional project details.
     * @param bool $persist Deprecated persistence option, ignored for safety.
     * @return int Effective round ID, or zero if no round exists.
     */
    public function getCurrentRoundId(?object $project = null, bool $persist = false): int
    {
        $round = $this->getCurrentRound($project, $persist);
        return $round ? (int) $round->id : 0;
    }

    /**
     * Compute the current round from the project's scheduling rules.
     *
     * Verifies the supplied project's ownership and uses bound UTC cutoff
     * timestamps for automatic selection. The old $persist flag remains in
     * the signature for legacy callers but is intentionally ignored:
     * a public Joomla site GET is always read-only.
     *
     * @param object|null $project Optional project details.
     * @param bool $persist Deprecated legacy flag; ignored for site reads.
     * @return object|null Effective round record, or null if unavailable.
     */
    public function getCurrentRound(?object $project = null, bool $persist = false): ?object
    {
        if ($this->projectId <= 0) {
            return null;
        }

        $project ??= $this->loadProject();
        if (!$project) {
            return null;
        }

        $projectId = (int) ($project->id ?? 0);
        if ($projectId <= 0 || $projectId !== $this->projectId) {
            return null;
        }

        $currentRoundId = max(0, (int) ($project->current_round ?? 0));
        $autoMode = max(0, (int) ($project->current_round_auto ?? 0));
        $autoTime = (int) ($project->auto_time ?? 0);
        if ($autoTime <= 0) {
            $autoTime = 7200;
        }

        $round = $this->findAutomaticRound($autoMode, $autoTime, $currentRoundId);
        if (!$round && $currentRoundId > 0) {
            $round = $this->loadRound($currentRoundId);
        }
        if (!$round) {
            $round = $this->loadFallbackRound($autoMode);
        }

        // Never persist a derived round during a public GET. Saving the
        // administrator-managed current_round is an explicit backend action.
        return $round ?: null;
    }

    /**
     * Find an automatic project round with portable, bound UTC date cutoffs.
     *
     * Automatic modes use the start/end dates or fixture dates; the
     * configured offset is interpreted in minutes, matching historical
     * INTERVAL MINUTE queries. The current date is evaluated at UTC midnight.
     * Avoids raw database-specific interval arithmetic and exposes no
     * parameters through direct SQL interpolation.
     *
     * @param int $autoMode Automatic mode selector (0-4).
     * @param int $autoTime UTC cutoff offset in minutes.
     * @param int $currentRoundId Previously selected round.
     * @return object|null Effective project-owned round.
     */
    private function findAutomaticRound(int $autoMode, int $autoTime, int $currentRoundId): ?object
    {
        $db = $this->database;
        $projectId = $this->projectId;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('r.id'),
                $db->quoteName('r.roundcode'),
                $db->quoteName('r.alias'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = :autoRoundProjectId')
            ->bind(':autoRoundProjectId', $projectId, ParameterType::INTEGER);

        $today = new \DateTimeImmutable('today', new \DateTimeZone('UTC'));
        $minutes = $autoTime > 0 && $autoTime <= 5256000 ? $autoTime : 7200;
        $cutoffAfter = $today->modify('+' . $minutes . ' minutes')->format('Y-m-d H:i:s');
        $cutoffBefore = $today->modify('-' . $minutes . ' minutes')->format('Y-m-d H:i:s');

        switch ($autoMode) {
            case 0:
                if ($currentRoundId <= 0) {
                    return null;
                }
                $query->where($db->quoteName('r.id') . ' = :currentRoundId')
                    ->bind(':currentRoundId', $currentRoundId, ParameterType::INTEGER);
                break;

            case 1:
                $query->where($db->quoteName('r.round_date_first') . ' < :roundCutoffAfter')
                    ->bind(':roundCutoffAfter', $cutoffAfter, ParameterType::STRING)
                    ->order($db->quoteName('r.round_date_first') . ' DESC');
                break;

            case 2:
                $query->where($db->quoteName('r.round_date_last') . ' < :roundCutoffAfter')
                    ->bind(':roundCutoffAfter', $cutoffAfter, ParameterType::STRING)
                    ->order($db->quoteName('r.round_date_first') . ' DESC');
                break;

            case 3:
                $query->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
                    ->where($db->quoteName('m.match_date') . ' < :roundCutoffAfter')
                    ->bind(':roundCutoffAfter', $cutoffAfter, ParameterType::STRING)
                    ->order($db->quoteName('m.match_date') . ' DESC');
                break;

            case 4:
                $query->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
                    ->where($db->quoteName('m.match_date') . ' < :roundCutoffBefore')
                    ->bind(':roundCutoffBefore', $cutoffBefore, ParameterType::STRING)
                    ->order($db->quoteName('m.match_date') . ' ASC');
                break;

            default:
                return null;
        }

        $db->setQuery($query, 0, 1);
        $round = $db->loadObject();
        return $this->withRoundSlug($round ?: null);
    }

    /**
     * Load a single round belonging to this project by a bound identifier.
     *
     * A round owned by another project cannot be returned, even when its ID
     * is passed through a stale or forged frontend request.
     *
     * @param int $roundId Round identifier.
     * @return object|null Project-owned round with a PHP-generated slug.
     */
    private function loadRound(int $roundId): ?object
    {
        if ($roundId <= 0) {
            return null;
        }

        $db = $this->database;
        $projectId = $this->projectId;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('r.id'),
                $db->quoteName('r.roundcode'),
                $db->quoteName('r.alias'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = :roundProjectId')
            ->where($db->quoteName('r.id') . ' = :roundId')
            ->bind(':roundProjectId', $projectId, ParameterType::INTEGER)
            ->bind(':roundId', $roundId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $round = $db->loadObject();
        return $this->withRoundSlug($round ?: null);
    }

    /**
     * Select a deterministic fallback from the selected project's rounds.
     *
     * Orders by round code, using ASC or DESC according to the legacy
     * scheduling mode without inserting vendor-specific slug expressions.
     *
     * @param int $autoMode Automatic selection mode.
     * @return object|null Fallback round with an ID:alias slug.
     */
    private function loadFallbackRound(int $autoMode): ?object
    {
        $db = $this->database;
        $projectId = $this->projectId;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('r.id'),
                $db->quoteName('r.roundcode'),
                $db->quoteName('r.alias'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = :fallbackProjectId')
            ->bind(':fallbackProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('r.roundcode') . (in_array($autoMode, [0, 2], true) ? ' DESC' : ' ASC'));
        $db->setQuery($query, 0, 1);
        $round = $db->loadObject();
        return $this->withRoundSlug($round ?: null);
    }

    /**
     * Format an effective round as a Joomla ID:alias record.
     *
     * Retains the old round_slug presentation property without depending on
     * MySQL CONCAT_WS for computing the alias and round ID.
     *
     * @param object|null $round Project-owned round record.
     * @return object|null Enriched round with round_slug, or null.
     */
    private function withRoundSlug(?object $round): ?object
    {
        if ($round !== null) {
            $round->round_slug = (int) ($round->id ?? 0) . ':' . (string) ($round->alias ?? '');
        }

        return $round;
    }

    /**
     * Read project scheduling metadata by a bound project identifier.
     *
     * Loads the persisted current round and scheduling settings for the
     * selected project without changing any stored project field.
     *
     * @return object|null Project configuration or null if not found.
     */
    private function loadProject(): ?object
    {
        $db = $this->database;
        $projectId = $this->projectId;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('current_round'),
                $db->quoteName('current_round_auto'),
                $db->quoteName('auto_time'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }
}
