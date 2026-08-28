<?php
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
        $latestProjectSlug = '(SELECT CONCAT_WS(\':\', p.id, p.alias)'
            . ' FROM #__sportsmanagement_project AS p'
            . ' INNER JOIN #__sportsmanagement_project_team AS pt ON pt.project_id = p.id'
            . ' INNER JOIN #__sportsmanagement_season_team_id AS st ON st.id = pt.team_id'
            . ' INNER JOIN #__sportsmanagement_team AS t ON t.id = st.team_id'
            . ' WHERE t.club_id = c.id AND p.published = 1'
            . ' ORDER BY p.id DESC LIMIT 1)';

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('c.id'),
                $db->quoteName('c.name'),
                $db->quoteName('c.new_club_id'),
                $db->quoteName('c.logo_big'),
                $db->quoteName('c.founded_year'),
                "CONCAT_WS(':', c.id, c.alias) AS slug",
                'COALESCE(' . $latestProjectSlug . ", '0') AS project_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->where(
                '(' . $db->quoteName('c.new_club_id') . ' > 0'
                . ' OR EXISTS (SELECT 1 FROM #__sportsmanagement_club AS predecessor'
                . ' WHERE predecessor.new_club_id = c.id))'
            )
            ->order([$db->quoteName('c.new_club_id') . ' ASC', $db->quoteName('c.name') . ' ASC']);

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
