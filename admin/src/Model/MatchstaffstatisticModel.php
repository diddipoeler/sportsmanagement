<?php
/**
 * Native Joomla 5/6 administrator model for match staff statistics.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchstaffstatisticTable;
use Joomla\Database\ParameterType;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

final class MatchstaffstatisticModel extends SportsManagementAdminModel
{
    /**
     * Read validated staff statistics for the selected match.
     *
     * Accepts only full positive decimal identifiers for the match and both
     * project teams, avoiding PHP 8 warnings and integer truncation. Uses a
     * Joomla bound query and retains the existing nested team, relation and
     * statistic keys expected by the administrator editor. Discards malformed
     * or foreign-team rows before rendering. Logs database failures by
     * exception class and supplies only a generic error to Joomla forms.
     *
     * @param mixed $matchId Match ID from the administrator request.
     * @param mixed $projectTeam1Id First project team ID.
     * @param mixed $projectTeam2Id Second project team ID.
     * @return array<int,array<int,array<int,mixed>>> Validated match statistics.
     */
    public function getMatchStaffStatsInput($matchId = 0, $projectTeam1Id = 0, $projectTeam2Id = 0): array
    {
        $matchId = self::positiveStatisticId($matchId);
        $projectTeam1Id = self::positiveStatisticId($projectTeam1Id);
        $projectTeam2Id = self::positiveStatisticId($projectTeam2Id);
        // Preserve the historical team-keyed empty response, including the
        // placeholder key zero, for Joomla's existing statistic editor.
        $stats = [
            $projectTeam1Id ?? 0 => [],
            $projectTeam2Id ?? 0 => [],
        ];

        if ($matchId === null || ($projectTeam1Id === null && $projectTeam2Id === null)) {
            return $stats;
        }

        try {
            $db = $this->getDatabase();
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('projectteam_id'),
                    $db->quoteName('team_staff_id'),
                    $db->quoteName('statistic_id'),
                    $db->quoteName('value'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match_staff_statistic'))
                ->where($db->quoteName('match_id') . ' = :matchId')
                ->bind(':matchId', $matchId, ParameterType::INTEGER);
            $db->setQuery($query);
            $rows = $db->loadObjectList();
        } catch (\Throwable $error) {
            Log::add(
                __METHOD__ . ': match statistics read failed (' . get_class($error) . ').',
                Log::ERROR,
                'com_sportsmanagement'
            );
            $this->setError(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));

            return $stats;
        }

        if (!is_array($rows)) {
            return $stats;
        }

        foreach ($rows as $row) {
            if (!is_object($row)) {
                continue;
            }

            $teamId = self::positiveStatisticId($row->projectteam_id ?? null);
            $relationId = self::positiveStatisticId($row->team_staff_id ?? null);
            $statisticId = self::positiveStatisticId($row->statistic_id ?? null);
            $value = $row->value ?? null;

            if ($teamId === null || !array_key_exists($teamId, $stats)
                || $relationId === null || $statisticId === null
                || (!is_string($value) && !is_int($value) && !is_float($value))
                || (is_float($value) && !is_finite($value))
                || (is_string($value) && (strlen($value) > 512
                    || preg_match('/[\\x00-\\x1F\\x7F]/', $value) === 1))) {
                continue;
            }

            $stats[$teamId][$relationId][$statisticId] = $value;
        }

        return $stats;
    }

    /**
     * Parse a positive match, project team or statistic relation ID.
     *
     * Supports decimal database IDs and leading-zero strings but rejects
     * partial strings, arrays, booleans and values exceeding PHP_INT_MAX
     * before they can become nested array keys or SQL query parameters.
     *
     * @param mixed $candidate Raw Joomla form or database identifier.
     * @return int|null Positive integer ID or null when invalid.
     */
    private static function positiveStatisticId(mixed $candidate): ?int
    {
        if (is_int($candidate)) {
            return $candidate > 0 ? $candidate : null;
        }

        if (!is_string($candidate) || strlen($candidate) > 64
            || preg_match('/^[0-9]+$/D', $candidate) !== 1) {
            return null;
        }

        $digits = ltrim($candidate, '0');
        $max = (string) PHP_INT_MAX;

        if ($digits === '' || strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return null;
        }

        return (int) $digits;
    }

    /**
     * Resolve the native match-staff-statistic table.
     * @param string $type Requested table name.
     * @param string $prefix Legacy Joomla table namespace.
     * @param array $config Optional Joomla table settings.
     * @return mixed MatchstaffstatisticTable or parent Joomla table.
     */
    public function getTable($type = 'matchstaffstatistic', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'matchstaffstatistic') === 0) {
            return new MatchstaffstatisticTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }
}
