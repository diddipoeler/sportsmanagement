<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Native Joomla 5/6 data model for frontend JSON endpoints.
 */
final class AjaxModel extends BaseDatabaseModel
{
    /**
     * Return published projects in the option shape expected by the navigation module.
     */
    public function getProjectsOptions(int $seasonId = 0, int $leagueId = 0, int $ordering = 0): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.name'),
                $db->quoteName('p.season_id'),
                $db->quoteName('p.league_id'),
                $db->quoteName('s.name', 'season_name'),
                $db->quoteName('l.name', 'league_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_league', 'l')
                . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id')
            )
            ->where($db->quoteName('p.published') . ' = 1');

        if ($seasonId > 0) {
            $query->where($db->quoteName('p.season_id') . ' = ' . $seasonId);
        }

        if ($leagueId > 0) {
            $query->where($db->quoteName('p.league_id') . ' = ' . $leagueId);
        }

        switch ($ordering) {
            case 1:
                $query->order($db->quoteName('p.name') . ' ASC');
                break;

            case 2:
                $query->order($db->quoteName('p.name') . ' ASC');
                $query->order($db->quoteName('s.ordering') . ' DESC');
                break;

            case 3:
                $query->order($db->quoteName('s.ordering') . ' DESC');
                $query->order($db->quoteName('p.name') . ' ASC');
                break;

            case 4:
                $query->order($db->quoteName('l.ordering') . ' ASC');
                $query->order($db->quoteName('p.name') . ' ASC');
                break;

            default:
                $query->order($db->quoteName('p.ordering') . ' ASC');
                break;
        }

        $db->setQuery($query);
        $projects = $db->loadObjectList();
        $options = [];

        foreach ($projects as $project) {
            $options[] = (object) [
                'value' => (int) $project->id,
                'text' => Text::_((string) $project->name),
                'season_name' => (string) $project->season_name,
                'league_name' => (string) $project->league_name,
            ];
        }

        return $options;
    }
}
