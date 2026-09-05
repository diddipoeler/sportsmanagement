<?php
/**
 * Native Joomla 5/6 frontend referees list model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class RefereesModel extends SportsManagementProjectModel
{
    /**
     * Legacy public static state retained for existing views/extensions.
     */
    public static int $cfg_which_database = 0;
    public static int $projectid = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$projectid = $this->projectId;
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0);

        if (class_exists('sportsmanagementModelProject')) {
            \sportsmanagementModelProject::$projectid = self::$projectid;
        }
    }

    public function getReferees(): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $subquery = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'ptt1') . ' ON ' . $db->quoteName('ptt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'ptt2') . ' ON ' . $db->quoteName('ptt2.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match_referee', 'mr') . ' ON ' . $db->quoteName('mr.match_id') . ' = ' . $db->quoteName('m.id'))
            ->where('(' . $db->quoteName('ptt1.project_id') . ' = ' . $db->quoteName('pr.project_id') . ' OR ' . $db->quoteName('ptt2.project_id') . ' = ' . $db->quoteName('pr.project_id') . ')')
            ->where($db->quoteName('mr.project_referee_id') . ' = ' . $db->quoteName('pr.id'));

        $query = $db->createQuery()
            ->select([
                'p.*',
                $db->quoteName('p.id', 'pid'),
                "CONCAT_WS(':', p.id, p.alias) AS slug",
                $db->quoteName('pr.id', 'prid'),
                $db->quoteName('pr.notes', 'description'),
                $db->quoteName('ppos.position_id'),
                $db->quoteName('pos.name', 'position'),
                $db->quoteName('pos.parent_id'),
                '(' . $subquery . ') AS countGames',
            ])
            ->from($db->quoteName('#__sportsmanagement_project_referee', 'pr'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_person_id', 'o') . ' ON ' . $db->quoteName('o.id') . ' = ' . $db->quoteName('pr.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('o.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'pro') . ' ON ' . $db->quoteName('pro.id') . ' = ' . $db->quoteName('pr.project_id') . ' AND ' . $db->quoteName('pro.season_id') . ' = ' . $db->quoteName('o.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('pr.project_position_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('pr.project_id') . ' = ' . $this->projectId)
            ->where($db->quoteName('pr.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('pro.published') . ' = 1')
            ->order($db->quoteName('pos.ordering') . ' ASC')
            ->order($db->quoteName('pos.id') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
