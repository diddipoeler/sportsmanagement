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

/**
 * Read-only access to chronological match commentary entries.
 */
final class MatchcommentaryModel extends SportsManagementAdminModel
{
    /** @return array<int,object> */
    public function getMatchCommentary($matchId = 0): array
    {
        $matchId = (int) $matchId;

        if ($matchId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('mc') . '.*')
            ->from($db->quoteName('#__sportsmanagement_match_commentary', 'mc'))
            ->where($db->quoteName('mc.match_id') . ' = ' . $matchId)
            ->order($db->quoteName('mc.timelog') . ' ASC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return [];
        }
    }
}
