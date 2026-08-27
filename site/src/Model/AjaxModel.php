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
    public function getProjectTeams(int $projectId): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t.id', 'value'),
                $db->quoteName('t.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
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
            ->where($db->quoteName('pt.project_id') . ' = ' . max(0, $projectId))
            ->group([$db->quoteName('t.id'), $db->quoteName('t.name')])
            ->order($db->quoteName('t.name') . ' ASC');

        $db->setQuery($query);

        return $this->withPrompt($db->loadObjectList(), '-- Team selektieren --', '-- keine Teams -- ');
    }

    public function getProjectSelect(int $leagueId): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id', 'value'),
                $db->quoteName('p.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id')
            )
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('p.league_id') . ' = ' . max(0, $leagueId))
            ->order($db->quoteName('s.name') . ' DESC')
            ->order($db->quoteName('p.name') . ' ASC');

        $db->setQuery($query);

        return $this->withPrompt($db->loadObjectList(), '-- Projekt selektieren --', '-- keine Projekte -- ');
    }

    public function getAssocLeagueSelect(string $country, int $associationId): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('l.id', 'value'),
                $db->quoteName('l.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_league', 'l'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.league_id') . ' = ' . $db->quoteName('l.id')
            )
            ->where($db->quoteName('l.country') . ' = ' . $db->quote($country))
            ->group([$db->quoteName('l.id'), $db->quoteName('l.name')])
            ->order($db->quoteName('l.name') . ' ASC');

        if ($associationId > 0) {
            $query->where($db->quoteName('l.associations') . ' = ' . $associationId);
        }

        $db->setQuery($query);

        return $this->withPrompt($db->loadObjectList(), '-- Liga selektieren --', '-- keine Ligen -- ');
    }

    public function getCountrySubSubAssocSelect(int $subAssociationId): array
    {
        return $this->getAssociationOptions(
            [$this->getDatabase()->quoteName('s.parent_id') . ' = ' . max(0, $subAssociationId),
             $this->getDatabase()->quoteName('s.published') . ' = 1'],
            '-- Kreisverbände -- ',
            '-- keine Kreisverbände -- '
        );
    }

    public function getCountrySubAssocSelect(int $associationId): array
    {
        return $this->getAssociationOptions(
            [$this->getDatabase()->quoteName('s.parent_id') . ' = ' . max(0, $associationId)],
            '-- Landesverbände -- ',
            '-- keine Landesverbände -- '
        );
    }

    public function getCountryAssocSelect(string $country): array
    {
        $db = $this->getDatabase();

        return $this->getAssociationOptions(
            [
                $db->quoteName('s.country') . ' = ' . $db->quote($country),
                $db->quoteName('s.parent_id') . ' = 0',
            ],
            '-- Regionalverbände -- ',
            '-- keine Regionalverbände -- '
        );
    }

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

    private function getAssociationOptions(array $where, string $prompt, string $emptyPrompt): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('s.id', 'value'),
                $db->quoteName('s.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_associations', 's'))
            ->where($where)
            ->order($db->quoteName('s.name') . ' ASC');

        $db->setQuery($query);

        return $this->withPrompt($db->loadObjectList(), $prompt, $emptyPrompt);
    }

    private function withPrompt(array $rows, string $prompt, string $emptyPrompt): array
    {
        return array_merge([
            (object) [
                'value' => 0,
                'text' => $rows === [] ? $emptyPrompt : $prompt,
            ],
        ], $rows);
    }
}
