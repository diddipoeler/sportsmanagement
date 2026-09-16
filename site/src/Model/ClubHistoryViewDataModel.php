<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Throwable;

/**
 * Read club predecessor/successor relations for the Clubinfo fusion view in a
 * single query, without the historical mutable static tree state.
 */
final class ClubHistoryViewDataModel extends SportsManagementProjectModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
    }

    /**
     * @return array<int, object> Clubs keyed by club id.
     */
    public function getRelations(): array
    {
        $db = $this->getDatabase();
        $latestProject = $db->createQuery()
            ->select(
                "CONCAT_WS(':', " . $db->quoteName('p.id') . ', ' . $db->quoteName('p.alias') . ')'
            )
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.project_id') . ' = ' . $db->quoteName('p.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
            )
            ->where($db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id'))
            ->where($db->quoteName('p.published') . ' = 1')
            ->order($db->quoteName('p.id') . ' DESC')
            ->setLimit(1);

        $predecessor = $db->createQuery()
            ->select('1')
            ->from($db->quoteName('#__sportsmanagement_club', 'predecessor'))
            ->where($db->quoteName('predecessor.new_club_id') . ' = ' . $db->quoteName('c.id'));

        $query = $db->createQuery()
            ->select([
                $db->quoteName('c.id'),
                $db->quoteName('c.name'),
                $db->quoteName('c.new_club_id'),
                $db->quoteName('c.logo_big'),
                $db->quoteName('c.founded_year'),
                "CONCAT_WS(':', " . $db->quoteName('c.id') . ', ' . $db->quoteName('c.alias') . ') AS ' . $db->quoteName('slug'),
                'COALESCE((' . $latestProject . "), '0') AS " . $db->quoteName('project_slug'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->where(
                '(' . $db->quoteName('c.new_club_id') . ' > 0'
                . ' OR EXISTS (' . $predecessor . '))'
            )
            ->order([
                $db->quoteName('c.new_club_id') . ' ASC',
                $db->quoteName('c.name') . ' ASC',
            ]);

        try {
            $db->setQuery($query);
            $relations = [];
            foreach ($db->loadObjectList() ?: [] as $club) {
                $id = (int) ($club->id ?? 0);
                if ($id > 0) {
                    $relations[$id] = $club;
                }
            }

            return $relations;
        } catch (Throwable) {
            return [];
        }
    }
}
