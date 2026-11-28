<?php
/**
 * Native Joomla 5/6 administrator read model for match commentary entries.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\ParameterType;

/**
 * Read-only access to chronological match commentary entries.
 */
final class MatchcommentaryModel extends SportsManagementAdminModel
{
    /**
     * Resolve the native commentary table on the model's active database.
     * Retain Joomla's factory for custom table requests and options.
     * @param string $name Requested table name, or empty for the default.
     * @param string $prefix Optional table prefix.
     * @param array $options Optional factory configuration.
     * @return mixed Native table or parent factory result.
     */
    public function getTable($name = '', $prefix = '', $options = [])
    {
        if (is_string($name) && ($name === '' || strcasecmp($name, 'Matchcommentary') === 0)
            && in_array($prefix, ['', 'Administrator', 'sportsmanagementTable'], true)
            && $options === []) {
            $table = new \Diddipoeler\Component\SportsManagement\Administrator\Table\MatchcommentaryTable($this->getDatabase(), true);
            if (method_exists($this, 'shouldUseExceptions') && $this->shouldUseExceptions()) {
                $table->setUseExceptions(true);
            }
            return $table;
        }

        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Read chronologically ordered commentary for one validated match.
     *
     * Validates positive decimal match identifiers without lossy PHP casts
     * and binds the selected match ID to a native Joomla database query.
     * Query construction, execution and result access are handled together
     * so a failed driver produces a generic administrator error rather than
     * a fatal exception. Only object rows reach the MVC view.
     *
     * @param mixed $matchId Requested match identifier from Joomla input.
     * @return array<int, object> Chronological commentary rows or an empty list.
     */
    public function getMatchCommentary($matchId = 0): array
    {
        $matchId = self::positiveMatchId($matchId);

        if ($matchId === null) {
            return [];
        }

        try {
            $db = $this->getDatabase();
            $query = $db->createQuery()
                ->select($db->quoteName('mc') . '.*')
                ->from($db->quoteName('#__sportsmanagement_match_commentary', 'mc'))
                ->where($db->quoteName('mc.match_id') . ' = :matchId')
                ->bind(':matchId', $matchId, ParameterType::INTEGER)
                ->order($db->quoteName('mc.timelog') . ' ASC');
            $db->setQuery($query);

            $records = $db->loadObjectList();

            return is_array($records) ? array_values(array_filter($records, 'is_object')) : [];
        } catch (\Throwable $e) {
            Log::add(
                'SportsManagement match commentary read failed: ' . $e->getMessage(),
                Log::ERROR,
                'com_sportsmanagement'
            );
            $this->setError(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));

            return [];
        }
    }
    /**
     * Parse one positive match identifier without PHP's permissive casts.
     *
     * Accepts Joomla integer IDs and zero-padded decimal strings while
     * rejecting arrays, booleans, suffixes, negative values and numbers
     * beyond PHP_INT_MAX. Invalid IDs never reach the database driver.
     *
     * @param mixed $value Input match ID or historical route parameter.
     * @return int|null Valid positive ID, or null when malformed.
     */
    private static function positiveMatchId(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (!is_string($value) || strlen($value) > 64
            || preg_match('/^[0-9]+$/D', $value) !== 1) {
            return null;
        }

        $digits = ltrim($value, '0');
        $max = (string) PHP_INT_MAX;

        if ($digits === '' || strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return null;
        }

        return (int) $digits;
    }
}
