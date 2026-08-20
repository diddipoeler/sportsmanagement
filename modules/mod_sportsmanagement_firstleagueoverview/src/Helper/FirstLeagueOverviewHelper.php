<?php
namespace Diddipoeler\Module\SportsManagementFirstLeagueOverview\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class FirstLeagueOverviewHelper
{
    /**
     * @return array{projects: array<int,object>, federations: array<int,object>}
     */
    public function getData(Registry $params): array
    {
        $db = $this->database($params);
        $componentParams = ComponentHelper::getParams('com_sportsmanagement');

        $federations = $this->getFederations($db);
        $projects = [];

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('l.id'),
                $db->quoteName('l.country'),
                $db->quoteName('l.name', 'league_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_league', 'l'))
            ->where($db->quoteName('l.champions_complete') . ' = 1')
            ->where(
                '('
                . $db->quoteName('l.league_level') . ' = 1 OR '
                . $db->quoteName('l.league_level') . ' = 41)'
            )
            ->order($db->quoteName('l.country') . ' ASC, ' . $db->quoteName('l.name') . ' ASC');

        $db->setQuery($query);
        $leagues = $db->loadObjectList() ?: [];

        foreach ($leagues as $league) {
            $project = $this->latestProjectForLeague($db, (int) $league->id);
            if (!$project) {
                continue;
            }

            $project->ranking_link = $this->rankingLink($project, $params);
            $project->country_label = Text::_((string) ($project->country_name ?: $project->country));
            $project->flag_html = $this->flagHtml($project, $componentParams);
            $projects[] = $project;
        }

        return [
            'projects' => $projects,
            'federations' => $federations,
        ];
    }

    /**
     * @return array<int,object>
     */
    private function getFederations(DatabaseInterface $db): array
    {
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_federations'))
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);

        $result = [];
        foreach ($db->loadObjectList() ?: [] as $row) {
            $row->picture_url = trim((string) ($row->picture ?? '')) !== ''
                ? rtrim((string) Uri::root(), '/') . '/' . ltrim((string) $row->picture, '/')
                : '';
            $result[(int) $row->id] = $row;
        }

        return $result;
    }

    private function latestProjectForLeague(DatabaseInterface $db, int $leagueId): ?object
    {
        if ($leagueId <= 0) {
            return null;
        }

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.alias'),
                $db->quoteName('p.name'),
                $db->quoteName('p.season_id'),
                $db->quoteName('l.country'),
                $db->quoteName('c.alpha2'),
                $db->quoteName('c.name', 'country_name'),
                $db->quoteName('c.picture', 'country_picture'),
                $db->quoteName('c.federation'),
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_league', 'l')
                . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_countries', 'c')
                . ' ON ' . $db->quoteName('c.alpha3') . ' = ' . $db->quoteName('l.country')
            )
            ->where($db->quoteName('p.league_id') . ' = ' . $leagueId)
            ->order($db->quoteName('p.name') . ' DESC');

        $db->setQuery($query, 0, 1);
        $result = $db->loadObject();

        return $result ?: null;
    }

    private function rankingLink(object $project, Registry $params): string
    {
        return Route::_(
            'index.php?' . http_build_query([
                'option' => 'com_sportsmanagement',
                'view' => 'ranking',
                'cfg_which_database' => (int) $params->get('cfg_which_database', 0),
                's' => (int) ($project->season_id ?? 0),
                'p' => (string) ($project->project_slug ?? $project->id ?? ''),
                'type' => 0,
                'r' => 0,
                'from' => 0,
                'to' => 0,
                'division' => 0,
            ]),
            false
        );
    }

    private function flagHtml(object $row, Registry $componentParams): string
    {
        $alpha3 = strtoupper((string) ($row->country ?? ''));
        $alpha2 = strtolower((string) ($row->alpha2 ?? ''));
        $label = htmlspecialchars((string) ($row->country_label ?? $alpha3), ENT_QUOTES, 'UTF-8');

        if ((int) $componentParams->get('cfg_flags_css', 0) === 1) {
            $cssCode = match ($alpha3) {
                'WAL' => 'gb-wls',
                'SCO' => 'gb-sct',
                'GBR' => 'gb-eng',
                default => $alpha2,
            };

            return $cssCode !== ''
                ? '<span class="fi fi-' . htmlspecialchars($cssCode, ENT_QUOTES, 'UTF-8') . '" title="' . $label . '"></span>'
                : '';
        }

        $path = $alpha2 !== ''
            ? 'images/com_sportsmanagement/database/flags/' . $alpha2 . '.png'
            : (string) ($row->country_picture ?? $componentParams->get('ph_flags', ''));

        if ($path === '') {
            return '';
        }

        return '<img src="'
            . htmlspecialchars(rtrim((string) Uri::root(), '/') . '/' . ltrim($path, '/'), ENT_QUOTES, 'UTF-8')
            . '" alt="' . $label . '" title="' . $label . '" />';
    }

    private function database(Registry $params): DatabaseInterface
    {
        if (!class_exists('sportsmanagementHelper', false)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';
        }

        try {
            $db = \sportsmanagementHelper::getDBConnection(
                true,
                (int) $params->get('cfg_which_database', 0)
            );

            if ($db instanceof DatabaseInterface) {
                return $db;
            }
        } catch (\Throwable) {
        }

        return Factory::getContainer()->get(DatabaseInterface::class);
    }
}
