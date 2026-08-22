<?php
namespace Diddipoeler\Module\SportsManagementLiveticker\Site\Helper;

\defined('_JEXEC') or die;

use DateTimeImmutable;
use DateTimeZone;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

final class LivetickerHelper
{
    public function getData(Registry $params, CMSApplicationInterface $app, Input $input): array
    {
        $this->ensureSportsManagementHelper();

        $action = $input->getCmd('action', '');
        $isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
        $identity = $app->getIdentity();
        $userId = (int) $identity->id;
        $name = (string) $identity->name;
        $allowUnregistered = (bool) $params->get('allow_unregistered', false);
        $useSecretSalt = (bool) $params->get('use_secret_salt', false);
        $secretSalt = (string) $params->get('secret_salt', 'tGbd8mfTb4p3f1_aAQpn84Qds');
        $ajaxReturn = '';

        switch ($action) {
            case 'turtushout_shout':
                if (!$userId && !$allowUnregistered) {
                    $ajaxReturn = 'Login First!';
                    break;
                }

                if ($useSecretSalt && !$this->isValidRequestToken($input, $secretSalt)) {
                    $ajaxReturn = 'Access Error!';
                    break;
                }

                $errors = $this->shout(
                    (bool) $params->get('display_username', 1),
                    (bool) $params->get('display_title', 1),
                    (int) $params->get('add_timeout', 10)
                );
                $ajaxReturn = $errors ?: 'Shouted!';
                break;

            case 'turtushout_del':
                if (!$identity->authorise('core.delete', 'com_sportsmanagement')) {
                    $ajaxReturn = 'Access denied!';
                    break;
                }

                $errors = $this->delete();
                $ajaxReturn = $errors ?: 'Deleted!';
                break;

            case 'turtushout_token':
                $ajaxReturn = (string) $this->issueRequestToken($secretSalt);
                break;
        }

        $listHtml = '';

        if (!$isAjax || $action === 'turtushout_shouts') {
            $list = $this->getList($params, $app, (int) $params->get('display_num', 5));
            $commentary = (bool) $params->get('display_commentary', 1)
                ? $this->getListCommentary($list)
                : [];
            $listHtml = $this->buildListHtml($list, $commentary, $params, $app);
            $ajaxReturn = $listHtml;
        }

        return [
            'isAjax' => $isAjax,
            'ajaxReturn' => $ajaxReturn,
            'listHtml' => $listHtml,
            'userId' => $userId,
            'name' => $name,
            // The legacy shout form was explicitly disabled in the old template.
            'displayAddBox' => false,
            'displayUsername' => (bool) $params->get('display_username', 1),
            'displayTitle' => (bool) $params->get('display_title', 1),
            'displayWelcome' => (bool) $params->get('display_welcome', 1),
            'size' => (int) $params->get('size', 25),
            'cols' => (int) $params->get('cols', 17),
            'rows' => (int) $params->get('rows', 5),
            'updateTimeout' => max(1, (int) $params->get('update_timeout', 10)),
            'cssFile' => basename((string) $params->get('use_css', 'simple.css')),
            'endpoint' => Uri::getInstance()->toString(['path', 'query']),
        ];
    }

    public function getListCommentary(array $list): array
    {
        $db = \sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
        $matches = [];

        foreach ($list as $row) {
            $matchId = (int) ($row->match_id ?? 0);

            if ($matchId <= 0) {
                continue;
            }

            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__sportsmanagement_match_commentary'))
                ->where($db->quoteName('match_id') . ' = ' . $matchId)
                ->order($db->quoteName('event_time') . ' DESC');
            $db->setQuery($query);
            $rows = $db->loadObjectList();

            if ($rows) {
                $matches[$matchId] = $rows;
            }
        }

        return $matches;
    }

    public function getList(Registry $params, CMSApplicationInterface $app, int $limit): array
    {
        $now = $this->getNow($app);
        $timestamp = $now->getTimestamp();
        $playtime = max(0, (int) $params->get('playtime', 105));
        $timestampFrom = $timestamp - ($playtime * 60);
        $timestampTo = $timestamp + ($playtime * 60);

        $db = \sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true)
            ->select('jl.id,jl.name,jl.game_regular_time,jl.halftime,jl.fav_team')
            ->select('jco.alpha2')
            ->select('jco.picture AS country_picture')
            ->select('jco.alpha3 AS countries_iso_code_3')
            ->select('jm.id AS match_id,jm.match_date,jm.projectteam1_id,jm.projectteam2_id,jm.team1_result,jm.team2_result')
            ->select('jm.team1_result_split,jm.team2_result_split')
            ->select('jt1.name AS heim,jt1.short_name AS heim_short_name,jt1.middle_name AS heim_middle_name')
            ->select('jt2.name AS gast,jt2.short_name AS gast_short_name,jt2.middle_name AS gast_middle_name')
            ->select('jc1.logo_big AS wappenheim')
            ->select('jc2.logo_big AS wappengast')
            ->from('#__sportsmanagement_project AS jl')
            ->join('INNER', '#__sportsmanagement_round AS jr ON jr.project_id = jl.id')
            ->join('INNER', '#__sportsmanagement_match AS jm ON jm.round_id = jr.id')
            ->join('INNER', '#__sportsmanagement_project_team AS jpt1 ON jpt1.id = jm.projectteam1_id')
            ->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = jpt1.team_id')
            ->join('INNER', '#__sportsmanagement_team AS jt1 ON jt1.id = st1.team_id')
            ->join('INNER', '#__sportsmanagement_club AS jc1 ON jc1.id = jt1.club_id')
            ->join('INNER', '#__sportsmanagement_project_team AS jpt2 ON jpt2.id = jm.projectteam2_id')
            ->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = jpt2.team_id')
            ->join('INNER', '#__sportsmanagement_team AS jt2 ON jt2.id = st2.team_id')
            ->join('INNER', '#__sportsmanagement_club AS jc2 ON jc2.id = jt2.club_id')
            ->join('INNER', '#__sportsmanagement_league AS jle ON jle.id = jl.league_id')
            ->join('LEFT', '#__sportsmanagement_countries AS jco ON jco.alpha3 = jle.country')
            ->where('jm.match_timestamp >= ' . $timestampFrom)
            ->where('jm.match_timestamp <= ' . $timestampTo);

        $db->setQuery($query, 0, max(1, $limit));

        try {
            return $db->loadObjectList() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function buildListHtml(array $list, array $commentary, Registry $params, CMSApplicationInterface $app): string
    {
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
        $html .= '<table class="' . $tableClass . '"><thead><tr>';
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
                $html .= '<tr><td colspan="9"><div style="height:80px; overflow:auto;"><table width="100%">';

                foreach ($commentary[$matchId] as $entry) {
                    $icon = HTMLHelper::image(
                        Uri::root() . 'media/com_sportsmanagement/jl_images/discuss_active.gif',
                        'Kommentar',
                        ['title' => 'Kommentar']
                    );
                    $html .= '<tr><td width="10%">' . $this->escape((string) ($entry->event_time ?? '')) . '</td>';
                    $html .= '<td width="10%">' . $icon . '</td>';
                    $html .= '<td width="80%">' . $this->escape((string) ($entry->notes ?? '')) . '</td></tr>';
                }

                $html .= '</table></div></td></tr>';
            }
        }

        return $html . '</tbody></table></div></div>';
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

    private function isValidRequestToken(Input $input, string $secretSalt): bool
    {
        $timestamp = $input->getInt('ts', 0);
        $cookieValue = (string) $input->cookie->getString('tstoken', '');

        if ($timestamp <= 0 || $cookieValue === '' || abs(time() - $timestamp) > 120) {
            return false;
        }

        return hash_equals(md5($secretSalt . $timestamp), $cookieValue);
    }

    private function issueRequestToken(string $secretSalt): int
    {
        $timestamp = time();
        $uri = Uri::getInstance();

        setcookie('tstoken', md5($secretSalt . $timestamp), [
            'expires' => 0,
            'path' => '/',
            'secure' => strtolower($uri->getScheme()) === 'https',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        return $timestamp;
    }

    private function shout(bool $displayUsername, bool $displayTitle, int $addTimeout): ?string
    {
        // The legacy implementation was empty; keep the behaviour until commentary posting is migrated.
        return null;
    }

    private function delete(): ?string
    {
        // The legacy implementation was empty; keep the behaviour until commentary deletion is migrated.
        return null;
    }

    private function ensureSportsManagementHelper(): void
    {
        if (!class_exists('sportsmanagementHelper')) {
            $file = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';

            if (is_file($file)) {
                require_once $file;
            }
        }

        if (!class_exists('sportsmanagementHelper')) {
            throw new \RuntimeException('SportsManagement database helper is not available.');
        }
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
