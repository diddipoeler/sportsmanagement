<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Throwable;

/**
 * Batched Joomla 5/6 presentation data for the team-plan view.
 */
final class TeamplanViewDataModel extends SportsManagementProjectModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
    }

    /**
     * @param array<int, int> $matchIds
     * @return array<int, array<int, object>>
     */
    public function getMatchReferees(array $matchIds, bool $teamsAsReferees): array
    {
        $ids = [];
        foreach ($matchIds as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        if ($ids === []) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        if ($teamsAsReferees) {
            $query
                ->select([
                    $db->quoteName('mr.match_id'),
                    $db->quoteName('mr.project_referee_id', 'value'),
                    $db->quoteName('t.name', 'referee_name'),
                    $db->quoteName('pos.name', 'position_name'),
                    $db->quoteName('pos.ordering'),
                    $db->quoteName('mr.ordering', 'match_referee_ordering'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match_referee', 'mr'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_project_team', 'pt')
                    . ' ON ' . $db->quoteName('pt.id') . ' = ' . $db->quoteName('mr.project_referee_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                    . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_team', 't')
                    . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                    . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('mr.project_position_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_position', 'pos')
                    . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id')
                );
        } else {
            $query
                ->select([
                    $db->quoteName('link.match_id'),
                    $db->quoteName('ref.firstname', 'referee_firstname'),
                    $db->quoteName('ref.lastname', 'referee_lastname'),
                    $db->quoteName('ref.id', 'referee_id'),
                    $db->quoteName('ref.nickname', 'referee_nickname'),
                    "CONCAT_WS(':', ref.id, ref.alias) AS referee_slug",
                    $db->quoteName('ppos.position_id'),
                    $db->quoteName('pos.name', 'referee_position_name'),
                    $db->quoteName('pos.ordering'),
                    $db->quoteName('link.ordering', 'match_referee_ordering'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match_referee', 'link'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_project_referee', 'pref')
                    . ' ON ' . $db->quoteName('pref.id') . ' = ' . $db->quoteName('link.project_referee_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_season_person_id', 'sp')
                    . ' ON ' . $db->quoteName('sp.id') . ' = ' . $db->quoteName('pref.person_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_person', 'ref')
                    . ' ON ' . $db->quoteName('ref.id') . ' = ' . $db->quoteName('sp.person_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                    . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('link.project_position_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_position', 'pos')
                    . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id')
                )
                ->where('COALESCE(' . $db->quoteName('ref.published') . ', 1) = 1');
        }

        $query
            ->where($db->quoteName($teamsAsReferees ? 'mr.match_id' : 'link.match_id') . ' IN (' . implode(',', array_values($ids)) . ')')
            ->order([
                $db->quoteName($teamsAsReferees ? 'mr.match_id' : 'link.match_id') . ' ASC',
                $db->quoteName('pos.ordering') . ' ASC',
                $db->quoteName($teamsAsReferees ? 'mr.ordering' : 'link.ordering') . ' ASC',
            ]);

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (Throwable) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            $matchId = (int) ($row->match_id ?? 0);
            if ($matchId > 0) {
                $result[$matchId][] = $row;
            }
        }

        return $result;
    }
}
