<?php
/**
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementFirstLeagueOverview\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class FirstLeagueOverviewHelper
{
    /**
     * @return array{projects: array<int,object>, federations: array<int,object>}
     */
    public function getData(Registry $params, DatabaseInterface $fallbackDatabase): array
    {
        $db = SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            (int) $params->get('cfg_which_database', 0)
        );
        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $projects = $this->latestProjects($db);

        foreach ($projects as $project) {
            $project->ranking_link = $this->rankingLink($project, $params);
            $project->country_label = Text::_((string) ($project->country_name ?: $project->country));
            $project->flag_html = $this->flagHtml($project, $componentParams);
        }

        return [
            'projects' => $projects,
            'federations' => $this->getFederations($db),
        ];
    }

    /** @return array<int,object> */
    private function latestProjects(DatabaseInterface $db): array
    {
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.alias'),
                $db->quoteName('p.name'),
                $db->quoteName('p.season_id'),
                $db->quoteName('p.league_id'),
                $db->quoteName('l.country'),
                $db->quoteName('l.name', 'league_name'),
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
            ->where($db->quoteName('l.champions_complete') . ' = 1')
            ->where(
                '(' . $db->quoteName('l.league_level') . ' = 1 OR '
                . $db->quoteName('l.league_level') . ' = 41)'
            )
            ->order([
                $db->quoteName('l.country') . ' ASC',
                $db->quoteName('l.name') . ' ASC',
                $db->quoteName('p.name') . ' DESC',
                $db->quoteName('p.id') . ' DESC',
            ]);

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $projects = [];
        $seenLeagues = [];

        foreach ($rows as $row) {
            $leagueId = (int) ($row->league_id ?? 0);
            if ($leagueId <= 0 || isset($seenLeagues[$leagueId])) {
                continue;
            }

            $seenLeagues[$leagueId] = true;
            $projects[] = $row;
        }

        return $projects;
    }

    /** @return array<int,object> */
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

    private function rankingLink(object $project, Registry $params): string
    {
        return SiteRouteHelper::view('ranking', [
            'cfg_which_database' => (int) $params->get('cfg_which_database', 0),
            's' => (int) ($project->season_id ?? 0),
            'p' => (string) ($project->project_slug ?? $project->id ?? ''),
            'type' => 0,
            'r' => 0,
            'from' => 0,
            'to' => 0,
            'division' => 0,
        ]);
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
            . '" alt="' . $label . '" title="' . $label . '" loading="lazy" />';
    }
}
