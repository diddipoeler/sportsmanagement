<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

final class LeagueModel extends SportsManagementAdminModel
{
    public function getlogohistoryLeague($leagueId = 0, $seasonId = 0, $logoonly = false): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('cl') . '.*',
                $db->quoteName('se.name', 'seasonname'),
            ])
            ->from($db->quoteName('#__sportsmanagement_league_logos', 'cl'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season', 'se')
                . ' ON ' . $db->quoteName('se.id') . ' = ' . $db->quoteName('cl.season_id')
            )
            ->order($db->quoteName('se.name') . ' DESC');

        $leagueId = (int) $leagueId;
        $seasonId = (int) $seasonId;

        if ($leagueId > 0) {
            $query->where($db->quoteName('cl.league_id') . ' = ' . $leagueId);
        }

        if ($seasonId > 0) {
            $query->where($db->quoteName('se.id') . ' = ' . $seasonId);
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function saveshort(): bool
    {
        $input = Factory::getApplication()->getInput();
        $ids = array_values(array_filter(array_map('intval', (array) $input->post->get('cid', [], 'array'))));
        $post = $input->post->getArray();
        $result = true;

        foreach ($ids as $id) {
            $table = $this->getTable();

            if (!$table->load($id)) {
                $result = false;
                continue;
            }

            $table->country = (string) ($post['country' . $id] ?? $table->country);
            $table->associations = (int) ($post['association' . $id] ?? $table->associations);
            $table->agegroup_id = (int) ($post['agegroup' . $id] ?? $table->agegroup_id);
            $table->published_act_season = (int) ($post['published_act_season' . $id] ?? $table->published_act_season);
            $table->champions_complete = (int) ($post['champions_complete' . $id] ?? $table->champions_complete);

            if (!$table->check() || !$table->store()) {
                $result = false;
            }
        }

        return $result;
    }
}
