<?php
namespace Diddipoeler\Module\SportsManagementLiveticker\Site\Helper;

\defined('_JEXEC') or die;

use DateTimeImmutable;
use DateTimeZone;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class LivetickerHelper
{
    public function getData(Registry $params, object $module, CMSApplicationInterface $app): array
    {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);
        $db = $this->database($params, $joomlaDatabase);
        $list = $this->getList($params, $app, (int) $params->get('display_num', 5), $db);
        $commentary = (bool) $params->get('display_commentary', 1)
            ? $this->getListCommentary($list, $db)
            : [];

        return [
            'listHtml' => $this->buildListHtml($list, $commentary, $params, $app),
            'updateTimeout' => max(1, (int) $params->get('update_timeout', 10)),
            'cssFile' => basename((string) $params->get('use_css', 'simple.css')),
            'moduleId' => (int) ($module->id ?? 0),
        ];
    }

    /**
     * Read-only module refresh for Joomla com_ajax.
     *
     * Endpoint: index.php?option=com_ajax&module=sportsmanagement_liveticker&method=refresh&format=raw
     */
    public function refreshAjax(): string
    {
        $app = Factory::getApplication();
        $module = $this->requestedModule($app->getInput()->getInt('module_id', 0));

        if ($module === null) {
            return '';
        }

        $params = new Registry();
        $params->loadString((string) ($module->params ?? ''));
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);
        $db = $this->database($params, $joomlaDatabase);
        $list = $this->getList($params, $app, (int) $params->get('display_num', 5), $db);
        $commentary = (bool) $params->get('display_commentary', 1)
            ? $this->getListCommentary($list, $db)
            : [];

        return $this->buildListHtml($list, $commentary, $params, $app);
    }

    /** @return array<int,array<int,object>> */
    public function getListCommentary(array $list, DatabaseInterface $db): array
    {
        $matchIds = [];

        foreach ($list as $row) {
            $matchId = (int) ($row->match_id ?? 0);

            if ($matchId > 0) {
                $matchIds[$matchId] = $matchId;
            }
        }

        if ($matchIds === []) {
            return [];
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('*'))
            ->from($db->quoteName('#__sportsmanagement_match_commentary'))
            ->where($db->quoteName('match_id') . ' IN (' . implode(',', array_values($matchIds)) . ')')
            ->order([
                $db->quoteName('match_id') . ' ASC',
                $db->quoteName('event_time') . ' DESC',
            ]);
        $db->setQuery($query);

        $matches = [];

        foreach ($db->loadObjectList() ?: [] as $row) {
            $matchId = (int) ($row->match_id ?? 0);

            if ($matchId > 0) {
                $matches[$matchId][] = $row;
            }
        }

        return $matches;
    }

    /** @return array<int,object> */
    public function getList(
        Registry $params,
        CMSApplicationInterface $app,
        int $limit,
        DatabaseInterface $db
    ): array {
        $now = $this->getNow($app);
        $timestamp = $now->getTimestamp();
        $playtime = max(0, (int) $params->get('playtime', 105));
        $timestampFrom = $timestamp - ($playtime * 60);
        $timestampTo = $timestamp + ($playtime * 60);

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('jl.id'),
                $db->quoteName('jl.name'),
                $db->quoteName('jl.game_regular_time'),
                $db->quoteName('jl.halftime'),
                $db->quoteName('jl.fav_team'),
                $db->quoteName('jco.alpha2'),
                $db->quoteName('jco.picture', 'country_picture'),
                $db->quoteName('jco.alpha3', 'countries_iso_code_3'),
                $db->quoteName('jm.id', 'match_id'),
                $db->quoteName('jm.match_date'),
                $db->quoteName('jm.projectteam1_id'),
                $db->quoteName('jm.projectteam2_id'),
                $db->quoteName('jm.team1_result'),
                $db->quoteName('jm.team2_result'),
                $db->quoteName('jm.team1_result_split'),
                $db->quoteName('jm.team2_result_split'),
                $db->quoteName('jt1.name', 'heim'),
                $db->quoteName('jt1.short_name', 'heim_short_name'),
                $db->quoteName('jt1.middle_name', 'heim_middle_name'),
                $db->quoteName('jt2.name', 'gast'),
                $db->quoteName('jt2.short_name', 'gast_short_name'),
                $db->quoteName('jt2.middle_name', 'gast_middle_name'),
                $db->quoteName('jc1.logo_big', 'wappenheim'),
                $db->quoteName('jc2.logo_big', 'wappengast'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'jl'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'jr')
                . ' ON ' . $db->quoteName('jr.project_id') . ' = ' . $db->quoteName('jl.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match', 'jm')
                . ' ON ' . $db->quoteName('jm.round_id') . ' = ' . $db->quoteName('jr.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'jpt1')
                . ' ON ' . $db->quoteName('jpt1.id') . ' = ' . $db->quoteName('jm.projectteam1_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st1')
                . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('jpt1.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 'jt1')
                . ' ON ' . $db->quoteName('jt1.id') . ' = ' . $db->quoteName('st1.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_club', 'jc1')
                . ' ON ' . $db->quoteName('jc1.id') . ' = ' . $db->quoteName('jt1.club_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'jpt2')
                . ' ON ' . $db->quoteName('jpt2.id') . ' = ' . $db->quoteName('jm.projectteam2_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st2')
                . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('jpt2.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 'jt2')
                . ' ON ' . $db->quoteName('jt2.id') . ' = ' . $db->quoteName('st2.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_club', 'jc2')
                . ' ON ' . $db->quoteName('jc2.id') . ' = ' . $db->quoteName('jt2.club_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_league', 'jle')
                . ' ON ' . $db->quoteName('jle.id') . ' = ' . $db->quoteName('jl.league_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_countries', 'jco')
                . ' ON ' . $db->quoteName('jco.alpha3') . ' = ' . $db->quoteName('jle.country')
            )
            ->where($db->quoteName('jm.match_timestamp') . ' >= ' . $timestampFrom)
            ->where($db->quoteName('jm.match_timestamp') . ' <= ' . $timestampTo)
            ->order($db->quoteName('jm.match_date') . ' ASC');

        $db->setQuery($query, 0, max(1, $limit));

        try {
            return $db->loadObjectList() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function buildListHtml(
        array $list,
        array $commentary,
        Registry $params,
        CMSApplicationInterface $app
    ): string {
        $displayTitle = (bool) $params->get('display_title', 1);
        $displayLeagueName = (bool) $params->get('display_liganame', 0);
        $displayLeagueFlag = (bool) $params->get('display_ligaflagge', 0);
        $displayKickoff = (bool) $params->get('display_anstoss', 0);
        $displayFinalWhistle = (bool) $params->get('display_abpfiff', 0);
        $displayTeamLogo = (bool) $params->get('display_teamwappen', 0);
        $displayTeamName = (int) $params->get('display_teamname', 0);
        $displayLegs = (bool) $params->get('display_legs', 0);
        $tableClass = $this->escape((string) $params->get('table_class', 'table'));
        $html = '<div class="turtushout-entry"><div class="turtushout-name">';
        $html .= '<div class="table-responsive"><table class="' . $tableClass . '"><thead><tr>';
        $html .= '<td>aktuelle Zeit</td><td colspan="8">' . $this->escape($this->getNow($app)->format('H:i:s')) . '</td></tr>';

        if ($displayTitle) {
            $html .= '<tr>';

            if ($displayLeagueName || $displayLeagueFlag) {
                $html .= '<td colspan="' . (($displayLeagueName && $displayLeagueFlag) ? '2' : '1') . '">Liga</td>';
            }

            if ($displayKickoff) {
                $html .= '<td>Anpfiff</td>';
            }

            if ($displayFinalWhistle) {
                $html .= '<td>Abpfiff</td>';
            }

            if ($displayTeamLogo && $displayTeamName < 3) {
                $html .= '<td colspan="4">Paarung</td>';
            } elseif ($displayTeamLogo || $displayTeamName < 3) {
                $html .= '<td colspan="2">Paarung</td>';
            }

            $html .= '<td colspan="2">Ergebnis</td>';

            if ($displayLegs) {
                $html .= '<td>Sätze</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</thead><tbody>';

        foreach ($list as $match) {
            $kickoffParts = explode(' ', (string) ($match->match_date ?? ''));
            $kickoff = $kickoffParts[1] ?? '';
            $duration = ((int) ($match->game_regular_time ?? 0) + (int) ($match->halftime ?? 0)) * 60;
            $finalWhistle = $kickoff !== '' ? date('H:i:s', strtotime($kickoff) + $duration) : '';
            $homeLogo = trim((string) ($match->wappenheim ?? ''));
            $awayLogo = trim((string) ($match->wappengast ?? ''));

            $html .= '<tr>';

            if ($displayLeagueFlag) {
                $html .= '<td>' . $this->image((string) ($match->country_picture ?? ''), (string) ($match->countries_iso_code_3 ?? '')) . '</td>';
            }

            if ($displayLeagueName) {
                $html .= '<td>' . $this->escape((string) ($match->name ?? '')) . '</td>';
            }

            if ($displayKickoff) {
                $html .= '<td>' . $this->escape($kickoff) . '</td>';
            }

            if ($displayFinalWhistle) {
                $html .= '<td>' . $this->escape($finalWhistle) . '</td>';
            }

            if ($displayTeamLogo) {
                $html .= '<td>' . $this->image($homeLogo, (string) ($match->heim ?? ''), 20) . '</td>';
            }

            if ($displayTeamName < 3) {
                $html .= '<td>' . $this->escape($this->teamName($match, 'heim', $displayTeamName)) . '</td>';
            }

            if ($displayTeamLogo) {
                $html .= '<td>' . $this->image($awayLogo, (string) ($match->gast ?? ''), 20) . '</td>';
            }

            if ($displayTeamName < 3) {
                $html .= '<td>' . $this->escape($this->teamName($match, 'gast', $displayTeamName)) . '</td>';
            }

            $html .= '<td>' . $this->escape((string) ($match->team1_result ?? '')) . '</td>';
            $html .= '<td>' . $this->escape((string) ($match->team2_result ?? '')) . '</td>';

            if ($displayLegs) {
                $html .= '<td>' . $this->splitResults($match) . '</td>';
            }

            $html .= '</tr>';

            $matchId = (int) ($match->match_id ?? 0);

            if (isset($commentary[$matchId])) {
                $html .= '<tr><td colspan="9"><div class="overflow-auto" style="max-height:80px"><table class="table table-sm mb-0">';

                foreach ($commentary[$matchId] as $entry) {
                    $icon = HTMLHelper::image(
                        Uri::root() . 'media/com_sportsmanagement/jl_images/discuss_active.gif',
                        'Kommentar',
                        ['title' => 'Kommentar', 'loading' => 'lazy']
                    );
                    $html .= '<tr><td style="width:10%">' . $this->escape((string) ($entry->event_time ?? '')) . '</td>';
                    $html .= '<td style="width:10%">' . $icon . '</td>';
                    $html .= '<td style="width:80%">' . $this->escape((string) ($entry->notes ?? '')) . '</td></tr>';
                }

                $html .= '</table></div></td></tr>';
            }
        }

        return $html . '</tbody></table></div></div></div>';
    }

    private function teamName(object $match, string $side, int $mode): string
    {
        return match ($mode) {
            1 => (string) ($match->{$side . '_middle_name'} ?? ''),
            2 => (string) ($match->{$side . '_short_name'} ?? ''),
            default => (string) ($match->{$side} ?? ''),
        };
    }

    private function splitResults(object $match): string
    {
        $left = explode(';', (string) ($match->team1_result_split ?? ''));
        $right = explode(';', (string) ($match->team2_result_split ?? ''));
        $count = max(count($left), count($right));
        $parts = [];

        for ($i = 0; $i < $count; $i++) {
            $leftValue = $left[$i] ?? '';
            $rightValue = $right[$i] ?? '';

            if ($leftValue === '' && $rightValue === '') {
                continue;
            }

            $parts[] = $this->escape($leftValue !== '' ? $leftValue : '0')
                . ':' . $this->escape($rightValue !== '' ? $rightValue : '0');
        }

        return implode(' ', $parts);
    }

    private function image(string $src, string $alt, ?int $width = null): string
    {
        if ($src === '') {
            return '';
        }

        $widthAttribute = $width !== null ? ' width="' . $width . '"' : '';

        return '<img src="' . $this->escape($src) . '" alt="' . $this->escape($alt) . '"'
            . $widthAttribute . ' loading="lazy">';
    }

    private function getNow(CMSApplicationInterface $app): DateTimeImmutable
    {
        $timezone = (string) $app->get('offset', 'UTC');

        try {
            return new DateTimeImmutable('now', new DateTimeZone($timezone));
        } catch (\Throwable) {
            return new DateTimeImmutable('now', new DateTimeZone('UTC'));
        }
    }

    private function database(Registry $params, DatabaseInterface $joomlaDatabase): DatabaseInterface
    {
        $selector = (int) $params->get('cfg_which_database', 0) === 1 ? 1 : 0;

        return SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector);
    }

    private function requestedModule(int $moduleId): ?object
    {
        if ($moduleId <= 0) {
            return null;
        }

        $module = ModuleHelper::getModuleById($moduleId);

        if (
            !is_object($module)
            || (int) ($module->id ?? 0) !== $moduleId
            || (string) ($module->module ?? '') !== 'mod_sportsmanagement_liveticker'
        ) {
            return null;
        }

        return $module;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
