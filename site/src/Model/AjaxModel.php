<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

/**
 * Native Joomla 5/6 data model for frontend JSON endpoints.
 */
final class AjaxModel extends BaseDatabaseModel
{
    public function getLink(
        string $view = '',
        int $projectId = 0,
        int $roundId = 0,
        int $divisionId = 0,
        int $seasonId = 0
    ): string {
        $view = strtolower($view);
        $projectId = max(0, $projectId);

        if ($projectId === 0 || !in_array($view, ['ranking', 'results', 'resultsranking', 'teams', 'teamstree'], true)) {
            return '';
        }

        $parameters = [
            'cfg_which_database' => (int) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0),
            's' => max(0, $seasonId),
            'p' => $projectId,
        ];

        if ($view === 'ranking') {
            $parameters += [
                'type' => 0,
                'r' => max(0, $roundId),
                'from' => 0,
                'to' => 0,
                'division' => max(0, $divisionId),
            ];
        } elseif ($view === 'results' || $view === 'resultsranking') {
            $parameters += [
                'r' => max(0, $roundId),
                'division' => max(0, $divisionId),
                'mode' => 0,
                'order' => '',
                'layout' => '',
            ];
        } else {
            $parameters['division'] = max(0, $divisionId);
        }

        return SiteRouteHelper::view($view, $parameters);
    }

    public function getProjectTeams(int $projectId): array
    {
        $db = $this->sportsDatabase();
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
        $db = $this->sportsDatabase();
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
        $db = $this->sportsDatabase();
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
            max(0, $subAssociationId),
            null,
            true,
            '-- Kreisverbände -- ',
            '-- keine Kreisverbände -- '
        );
    }

    public function getCountrySubAssocSelect(int $associationId): array
    {
        return $this->getAssociationOptions(
            max(0, $associationId),
            null,
            false,
            '-- Landesverbände -- ',
            '-- keine Landesverbände -- '
        );
    }

    public function getCountryAssocSelect(string $country): array
    {
        return $this->getAssociationOptions(
            0,
            $country,
            false,
            '-- Regionalverbände -- ',
            '-- keine Regionalverbände -- '
        );
    }

    /**
     * Return published projects in the option shape expected by the navigation module.
     */
    public function getProjectsOptions(int $seasonId = 0, int $leagueId = 0, int $ordering = 0): array
    {
        $db = $this->sportsDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id', 'id'),
                $db->quoteName('p.name', 'name'),
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
                $query->order($db->quoteName('p.ordering') . ' DESC');
                break;

            case 2:
                $query->order($db->quoteName('s.ordering') . ' ASC')
                    ->order($db->quoteName('l.ordering') . ' ASC')
                    ->order($db->quoteName('p.ordering') . ' ASC');
                break;

            case 3:
                $query->order($db->quoteName('s.ordering') . ' DESC')
                    ->order($db->quoteName('l.ordering') . ' DESC')
                    ->order($db->quoteName('p.ordering') . ' DESC');
                break;

            case 4:
                $query->order($db->quoteName('p.name') . ' ASC');
                break;

            case 5:
                $query->order($db->quoteName('p.name') . ' DESC');
                break;

            default:
                $query->order($db->quoteName('p.ordering') . ' ASC');
                break;
        }

        $db->setQuery($query);
        $projects = $db->loadObjectList();

        return array_map(
            static fn ($project): object => (object) [
                'value' => (int) $project->id,
                'text' => (string) $project->name,
                'season_name' => (string) $project->season_name,
                'league_name' => (string) $project->league_name,
            ],
            $projects
        );
    }

    private function getAssociationOptions(
        int $parentId,
        ?string $country,
        bool $publishedOnly,
        string $prompt,
        string $emptyPrompt
    ): array {
        $db = $this->sportsDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('s.id', 'value'),
                $db->quoteName('s.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_associations', 's'))
            ->where($db->quoteName('s.parent_id') . ' = ' . $parentId)
            ->order($db->quoteName('s.name') . ' ASC');

        if ($country !== null) {
            $query->where($db->quoteName('s.country') . ' = ' . $db->quote($country));
        }

        if ($publishedOnly) {
            $query->where($db->quoteName('s.published') . ' = 1');
        }

        $db->setQuery($query);

        return $this->withPrompt($db->loadObjectList(), $prompt, $emptyPrompt);
    }

    private function sportsDatabase(): DatabaseInterface
    {
        $joomlaDatabase = $this->getDatabase();
        $container = Factory::getContainer();
        /** @var SiteApplication $app */
        $app = $container->get(SiteApplication::class);
        $selector = $app->getInput()->getInt(
            'cfg_which_database',
            (int) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0)
        );

        return SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector);
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
