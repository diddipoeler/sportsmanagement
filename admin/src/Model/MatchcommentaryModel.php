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
     * Read chronologically ordered commentary for one validated match.
     *
     * Ignores nonpositive match identifiers and binds the selected match ID
     * to the native Joomla database query. Database exceptions are logged
     * server-side while the model receives only a translated generic
     * message, preventing private SQL or connection details from surfacing
     * in the administrator view.
     *
     * @param int|string $matchId Requested match identifier.
     * @return array<int, object> Chronological commentary rows or an empty list.
     */
    public function getMatchCommentary($matchId = 0): array
    {
        $matchId = (int) $matchId;

        if ($matchId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('mc') . '.*')
            ->from($db->quoteName('#__sportsmanagement_match_commentary', 'mc'))
            ->where($db->quoteName('mc.match_id') . ' = :matchId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER)
            ->order($db->quoteName('mc.timelog') . ' ASC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
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
}
