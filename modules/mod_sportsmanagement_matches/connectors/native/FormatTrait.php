<?php
/**
 * Joomla 5/6 SportsManagement matches module PHP implementation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementMatches\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

trait NativeFormatTrait
{
    /**
     * Calculate whether a fixture is live, upcoming or already finished.
     * Use its Unix start time and configured period and break durations to
     * populate the legacy status flags consumed by match module templates.
     * @param object $match Mutable match row with timing metadata.
     * @return void
     */
    private function applyStatus(object $match): void
    {
        $now = time();
        $start = (int) $match->match_timestamp;
        $duration = max(0, (int) $match->game_regular_time + ((int) $match->game_parts * (int) $match->halftime) - (int) $match->halftime) * 60;
        $end = $start + $duration;
        $match->live = $now >= $start && $now <= $end;
        $match->actplaying = $match->live;
        $match->upcoming = $now < $start;
        $match->alreadyplayed = $now > $end;
    }

    /**
     * Determine the template status key for a match.
     * Respect the status-notice module option before selecting live,
     * upcoming or played states from the computed match flags.
     * @param object $match Match row after applying status flags.
     * @param Registry $params Display and status notice configuration.
     * @return string Known status name or undefined when disabled.
     */
    private function statusType(object $match, Registry $params): string
    {
        if ((int) $params->get('show_status_notice', 0) !== 1) {
            return 'undefined';
        }
        return $match->live ? 'live' : ($match->actplaying ? 'actplaying' : ($match->upcoming ? 'upcoming' : 'alreadyplayed'));
    }

    /**
     * Select the configured human-readable heading for a match status.
     * Derive the current match state and read its optional notice text,
     * returning an empty heading when status messages are disabled.
     * @param object $match Match row with current status flags.
     * @param Registry $params Status heading configuration.
     * @return string Configured status description or empty text.
     */
    private function statusHeading(object $match, Registry $params): string
    {
        $type = $this->statusType($match, $params);
        return $type === 'undefined' ? '' : (string) $params->get($type . '_notice', '');
    }

    /**
     * Build HTML-safe project and round headings for the fixture row.
     * Escape database titles and routed links before constructing optional
     * anchor markup according to the module's project and round settings.
     * @param object $match Match containing project and round labels.
     * @param Registry $params Title visibility and link configuration.
     * @return string Escaped HTML heading segments.
     */
    private function heading(object $match, Registry $params): string
    {
        $parts = [];
        if ((int) $params->get('show_project_title', 0) === 1) {
            $label = htmlspecialchars((string) $match->project_name, ENT_QUOTES, 'UTF-8');
            $parts[] = (int) $params->get('link_project_title', 0) === 1
                ? '<a href="' . htmlspecialchars($this->componentRoute((string) $params->get('p_link_func', 'results'), $match, [], $params), ENT_QUOTES, 'UTF-8') . '">' . $label . '</a>'
                : $label;
        }
        if ((int) $params->get('show_matchday_title', 0) === 1) {
            $label = htmlspecialchars((string) $match->round_name, ENT_QUOTES, 'UTF-8');
            $parts[] = (int) $params->get('link_matchday_title', 0) === 1
                ? '<a href="' . htmlspecialchars($this->componentRoute((string) $params->get('r_link_func', 'results'), $match, ['r' => (string) $match->round_slug], $params), ENT_QUOTES, 'UTF-8') . '">' . $label . '</a>'
                : $label;
        }
        return implode(' · ', $parts);
    }

    /**
     * Prepare a team's name, image and navigation links for a fixture.
     * Choose configured name and picture variants, validate media URLs and
     * return the existing structured template data contract. Missing optional
     * names or logo fields become empty strings for partially imported teams.
     * @param object $match Match row containing both teams.
     * @param bool $home True for the first team, false for the second.
     * @param Registry $params Team name, image and link settings.
     * @return array{name:string,logo:?array{src:string,alt:string,width:int,height:int},links:array<int,array{label:string,url:string,external:bool}>} Prepared team display data.
     */
    private function team(object $match, bool $home, Registry $params): array
    {
        $n = $home ? '1' : '2';
        $nameField = match ((string) $params->get('team_names', 'short_name')) {
            'middle_name' => 'team' . $n . '_middle_name',
            'name' => 'team' . $n . '_name',
            default => 'team' . $n . '_short_name',
        };
        $name = trim((string) ($match->{$nameField} ?? ''))
            ?: (string) ($match->{'team' . $n . '_name'} ?? '');
        $logo = null;
        if ((int) $params->get('show_picture', 1) === 1) {
            $pictureType = (string) $params->get('picture_type', 'club_big');
            if ($pictureType === 'country') {
                $alpha2 = strtolower((string) ($match->{'club' . $n . '_alpha2'} ?? ''));
                $src = preg_match('/^[a-z]{2}$/', $alpha2)
                    ? 'images/com_sportsmanagement/database/flags/' . $alpha2 . '.png'
                    : '';
            } elseif ($pictureType === 'team_picture') {
                $src = (string) ($match->{'team' . $n . '_picture'} ?? '');
            } else {
                $src = (string) ($match->{'club' . $n . '_big'} ?? '');
            }
            $src = $this->imageSource($src);

            if ($src !== '') {
                $logo = [
                    'src' => $src,
                    'alt' => $name,
                    'width' => max(0, (int) $params->get('xsize', 0)),
                    'height' => max(0, (int) $params->get('ysize', 30)),
                ];
            }
        }
        return ['name' => $name, 'logo' => $logo, 'links' => $this->teamLinks($match, $n, $params)];
    }

    /**
     * Convert a stored team or club image path into a safe site URL.
     * Allow HTTP(S) images without embedded credentials, reject traversal,
     * protocol-relative and unsupported schemes, and encode local path parts.
     * @param string $source Database image URL or Joomla-relative path.
     * @return string Safe URL, or empty text for an invalid image.
     */
    private function imageSource(string $source): string
    {
        $source = trim($source);

        if ($source === ''
            || preg_match('/[\x00-\x1F\x7F]/', $source)
            || str_contains($source, '\\')
        ) {
            return '';
        }

        if (preg_match('~^https?://~i', $source)) {
            if (preg_match('/[\x00-\x20\x7F]/', $source)
                || filter_var($source, FILTER_VALIDATE_URL) === false
            ) {
                return '';
            }

            $parts = parse_url($source);

            return is_array($parts)
                && !empty($parts['host'])
                && !isset($parts['user'])
                && !isset($parts['pass'])
                ? $source
                : '';
        }

        if (str_starts_with($source, '//')
            || preg_match('~^[a-z][a-z0-9+.-]*:~i', $source)
            || preg_match('~(?:^|/)\.\.?(?:/|$)~', $source)
            || str_contains($source, '%')
            || strpbrk($source, '?#') !== false
        ) {
            return '';
        }

        $relative = ltrim($source, '/');
        $encoded = implode('/', array_map('rawurlencode', explode('/', $relative)));

        return rtrim((string) Uri::root(), '/') . '/' . $encoded;
    }

    /**
     * Prepare optional venue text and its Joomla playground route.
     * Use the configured full or abbreviated playground name and only build
     * a link when the module enables playground navigation.
     * @param object $match Fixture row with playground metadata.
     * @param Registry $params Venue display and linking settings.
     * @return null|array{name:string,url:string} Venue details, or null if hidden.
     */
    private function venue(object $match, Registry $params): ?array
    {
        if ((int) $params->get('show_venue', 0) !== 1 || (int) $match->playground_id <= 0) {
            return null;
        }
        $name = (string) ((string) $params->get('venue_name', 'short_name') === 'name' ? $match->playground_name : $match->playground_short_name);
        $name = trim($name) ?: (string) $match->playground_name;
        $url = (int) $params->get('link_venue', 0) === 1 ? $this->route('playground', [
            'p' => (string) $match->project_slug,
            'pgid' => (int) $match->playground_id,
            'cfg_which_database' => (int) $params->get('cfg_which_database', 0),
        ]) : '';
        return ['name' => $name, 'url' => $url];
    }

    /**
     * Load referees for all displayed matches with one query.
     *
     * @param array<int,int> $matchIds
     * @return array<int,array<int,array{name:string,position:string}>>
     */
    /**
     * Load and group the referees assigned to displayed matches.
     * Fetch published people and referee positions in a single database query,
     * then format each official and group entries by their match identifier.
     * Use Joomla DatabaseInterface::createQuery() to avoid the deprecated
     * getQuery(true) mode removed in newer framework database releases.
     * @param DatabaseInterface $db Current SportsManagement connection.
     * @param array<int,int> $matchIds Positive identifiers of displayed matches.
     * @param int $format Requested legacy person name format.
     * @return array<int,array<int,array{name:string,position:string}>> Officials grouped by match.
     */
    private function refereesByMatch(DatabaseInterface $db, array $matchIds, int $format): array
    {
        $matchIds = array_values(array_unique(array_filter(array_map('intval', $matchIds))));
        if ($matchIds === []) {
            return [];
        }

        $query = $db->createQuery()
            ->select([
                $db->quoteName('mr.match_id'),
                $db->quoteName('p.firstname'),
                $db->quoteName('p.nickname'),
                $db->quoteName('p.lastname'),
                $db->quoteName('pos.name', 'position_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_referee', 'mr'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_referee', 'pref')
                . ' ON ' . $db->quoteName('pref.id') . ' = ' . $db->quoteName('mr.project_referee_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_person_id', 'spi')
                . ' ON ' . $db->quoteName('spi.id') . ' = ' . $db->quoteName('pref.person_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_person', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('spi.person_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('mr.project_position_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_position', 'pos')
                . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id')
            )
            ->where($db->quoteName('mr.match_id') . ' IN (' . implode(',', $matchIds) . ')')
            ->where($db->quoteName('p.published') . ' = 1')
            ->order([
                $db->quoteName('mr.match_id') . ' ASC',
                $db->quoteName('pos.name') . ' ASC',
                $db->quoteName('mr.ordering') . ' ASC',
            ]);
        $db->setQuery($query);

        $out = [];
        foreach ($db->loadObjectList() ?: [] as $referee) {
            $matchId = (int) ($referee->match_id ?? 0);
            if ($matchId <= 0) {
                continue;
            }

            $out[$matchId][] = [
                'name' => $this->formatName($referee, $format),
                'position' => (string) ($referee->position_name ?? ''),
            ];
        }

        return $out;
    }

    /**
     * Write the fixture result and optional set scores into a template row.
     * Handle cancelled fixtures, ordinary scores, overtime and penalty
     * notations, plus configured per-period results without changing markup.
     * @param array<string,mixed> $row Mutable prepared fixture template row.
     * @param object $match Match row with scores and cancellation flags.
     * @param Registry $params Result and separator display options.
     * @return void
     */
    private function result(array &$row, object $match, Registry $params): void
    {
        if ($row['cancel']) {
            $row['result'] = (string) $match->cancel_reason;
            $row['partresults'] = '';
            return;
        }
        $home = $match->team1_result === null ? '-' : (string) $match->team1_result;
        $away = $match->team2_result === null ? '-' : (string) $match->team2_result;
        $separator = (string) $params->get('team_separator', ':');
        $row['result'] = $home . $separator . $away;
        if ((int) $params->get('show_text_penalty', 1) === 1 && $match->team1_result_so !== null) {
            $row['result'] = Text::_('INP') . ' ' . $match->team1_result_so . $separator . $match->team2_result_so;
        } elseif ((int) $params->get('show_text_overtime', 1) === 1 && $match->team1_result_ot !== null) {
            $row['result'] = Text::_('IET') . ' ' . $match->team1_result_ot . $separator . $match->team2_result_ot;
        }
        $row['partresults'] = '';
        if ((int) $params->get('part_result', 0) === 1) {
            $h = $this->split((string) $match->team1_result_split);
            $a = $this->split((string) $match->team2_result_split);
            $pairs = [];
            foreach (array_keys($h) as $i) {
                if (isset($a[$i]) && $h[$i] !== '-' && $a[$i] !== '-') {
                    $pairs[] = $h[$i] . ':' . $a[$i];
                }
            }
            $row['partresults'] = $pairs ? '(' . implode((string) $params->get('part_results_separator', '-'), $pairs) . ')' : '';
        }
    }

    /**
     * Format a referee's name using the configured legacy name variant.
     * Combine optional given name, surname and nickname without creating
     * extra separators for absent fields.
     * @param object $person Referee record with optional name parts.
     * @param int $format Legacy display variant.
     * @return string Referee display name.
     */
    private function formatName(object $person, int $format): string
    {
        $first = trim((string) ($person->firstname ?? ''));
        $nick = trim((string) ($person->nickname ?? ''));
        $last = trim((string) ($person->lastname ?? ''));
        return match ($format) {
            1, 2, 4, 17, 18 => trim($last . ' ' . $first),
            10 => $last,
            12 => $nick ?: $first ?: $last,
            default => trim($first . ($nick !== '' ? " '$nick'" : '') . ' ' . $last),
        };
    }

    /**
     * Split stored per-period results into printable score components.
     * Treat blank positions as dashes and remove trailing empty periods
     * so home and away period pairs can be aligned.
     * @param string $value Semicolon-separated score values.
     * @return array<int,string> Normalised period scores.
     */
    private function split(string $value): array
    {
        $parts = array_map(static fn ($v): string => trim((string) $v), explode(';', $value));
        while ($parts && end($parts) === '') {
            array_pop($parts);
        }
        return array_map(static fn ($v): string => $v === '' ? '-' : $v, $parts);
    }
}
