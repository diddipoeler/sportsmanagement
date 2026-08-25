<?php
namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

/**
 * Narrow Joomla 5/6 presentation facade for the historical teamplan templates.
 *
 * It replaces the small sportsmanagementHelperHtml surface still used by
 * teamplan without loading site/helpers/html.php. The general
 * sportsmanagementHelper remains lazy until its team/name formatting surface
 * is migrated separately.
 */
final class TeamplanHtmlFacade
{
    public static $project = null;
    public static array $teams = [];

    public static function showEventsContainerInResults(
        $matchInfo = [],
        $projectevents = [],
        $matchevents = [],
        $substitutions = null,
        $config = [],
        $project = []
    ): string {
        $output = '';

        if (!empty($config['use_tabs_events'])) {
            $iPanel = 1;
            $selector = 'teamplan' . $matchInfo->id;
            $output .= HTMLHelper::_('bootstrap.startTabSet', $selector, ['active' => 'panel-' . $matchInfo->id . '-' . $iPanel]);
            $width = 20;
            $height = 20;
            $type = 4;
            $showEventInfo = 0;

            foreach ((array) $projectevents as $event) {
                $hasType = false;
                foreach ((array) $matchevents as $matchEvent) {
                    if ((int) ($matchEvent->event_type_id ?? 0) === (int) ($event->id ?? 0)) {
                        $hasType = true;
                        break;
                    }
                }

                if (!$hasType) {
                    continue;
                }

                if (!empty($config['show_events_with_icons'])) {
                    $imgTitle = Text::_((string) ($event->name ?? ''));
                    $tabContent = \sportsmanagementHelper::getPictureThumb(
                        $event->icon ?? '',
                        $imgTitle,
                        $width,
                        $height,
                        $type
                    );
                } else {
                    $tabContent = Text::_((string) ($event->name ?? ''));
                }

                $output .= HTMLHelper::_('bootstrap.addTab', $selector, 'panel-' . $matchInfo->id . '-' . $iPanel++, $tabContent);
                $output .= '<table class="matchreport" border="0"><tr><td class="list"><ul class="list-inline">';
                foreach ((array) $matchevents as $matchEvent) {
                    $output .= self::formatEventContainer($matchEvent, $event, $matchInfo->projectteam1_id, $showEventInfo, $config);
                }
                $output .= '</ul></td><td class="list"><ul class="list-inline">';
                foreach ((array) $matchevents as $matchEvent) {
                    $output .= self::formatEventContainer($matchEvent, $event, $matchInfo->projectteam2_id, $showEventInfo, $config);
                }
                $output .= '</ul></td></tr></table>';
                $output .= HTMLHelper::_('bootstrap.endTab');
            }

            if (!empty($substitutions)) {
                if (!empty($config['show_events_with_icons'])) {
                    $imgTitle = Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION');
                    $picTab = 'images/com_sportsmanagement/database/events/' . ($project->fs_sport_type_name ?? '') . '/change.png';
                    $tabContent = \sportsmanagementHelper::getPictureThumb($picTab, $imgTitle, $width, $height, $type);
                } else {
                    $tabContent = Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION');
                }

                $base = Uri::root() . 'images/com_sportsmanagement/database/events/' . ($project->fs_sport_type_name ?? '') . '/';
                $imgTime = HTMLHelper::image($base . 'playtime.gif', Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE'), ['title' => Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE')]);
                $imgOut = HTMLHelper::image($base . 'out.png', Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_WENT_OUT'), ['title' => Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_WENT_OUT')]);
                $imgIn = HTMLHelper::image($base . 'in.png', Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_CAME_IN'), ['title' => Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_CAME_IN')]);

                $output .= HTMLHelper::_('bootstrap.addTab', $selector, 'panel-' . $matchInfo->id . '-' . $iPanel++, $tabContent);
                $output .= '<table class="matchreport" border="0"><tr><td class="list"><ul class="list-inline">';
                foreach ((array) $substitutions as $substitution) {
                    $output .= self::formatSubstitutionContainer($substitution, $matchInfo->projectteam1_id, $imgTime, $imgOut, $imgIn, $config);
                }
                $output .= '</ul></td><td class="list"><ul class="list-inline">';
                foreach ((array) $substitutions as $substitution) {
                    $output .= self::formatSubstitutionContainer($substitution, $matchInfo->projectteam2_id, $imgTime, $imgOut, $imgIn, $config);
                }
                $output .= '</ul></td></tr></table>';
                $output .= HTMLHelper::_('bootstrap.endTab');
            }

            $output .= HTMLHelper::_('bootstrap.endTabSet');
            return $output;
        }

        $showEventInfo = !empty($config['show_events_with_icons']) ? 1 : 2;
        $output .= '<table class="matchreport" border="0"><tr><td class="list-left"><ul class="list-inline">';
        foreach ((array) $matchevents as $matchEvent) {
            if ((int) ($matchEvent->ptid ?? 0) === (int) ($matchInfo->projectteam1_id ?? 0)) {
                $event = $projectevents[$matchEvent->event_type_id] ?? null;
                if ($event) {
                    $output .= self::formatEventContainer($matchEvent, $event, $matchInfo->projectteam1_id, $showEventInfo, $config);
                }
            }
        }
        $output .= '</ul></td><td class="list-right"><ul class="list-inline">';
        foreach ((array) $matchevents as $matchEvent) {
            if ((int) ($matchEvent->ptid ?? 0) === (int) ($matchInfo->projectteam2_id ?? 0)) {
                $event = $projectevents[$matchEvent->event_type_id] ?? null;
                if ($event) {
                    $output .= self::formatEventContainer($matchEvent, $event, $matchInfo->projectteam2_id, $showEventInfo, $config);
                }
            }
        }
        $output .= '</ul></td></tr></table>';

        return $output;
    }

    public static function getBootstrapModalImage(
        $target = '',
        $picture = '',
        $text = '',
        $pictureheight = '20',
        $url = '',
        $width = '100',
        $height = '200',
        $useJqueryModal = 0,
        $schemaorg = 'itemprop',
        $schemaorgvalue = 'logo'
    ): string {
        $target = (string) $target;
        $picture = (string) $picture;
        $text = (string) $text;
        $url = (string) $url;
        $image = '<img ' . $schemaorg . '="' . $schemaorgvalue . '" src="' . $picture . '" alt="' . $text
            . '" style="width: auto;height: ' . $pictureheight . 'px" />';

        if ((int) $useJqueryModal === 2) {
            $href = $url !== '' ? $url : $picture;
            $classes = $url !== '' ? 'jcepopup' : 'jcepopup jcemediabox-image';
            return '<a class="' . $classes . '" title="' . $text . '" href="' . $href
                . '" data-mediabox="1" data-mediabox-title="' . $text . '">' . $image . '</a>';
        }

        if ((int) $useJqueryModal === 1) {
            $href = $url !== '' ? $url : $picture;
            return '<a id="' . $target . '" href="' . $href . '" target="SingleSecondaryWindowName"'
                . ' onclick="openRequestedSinglePopup(this.href,' . $width . ',' . $height . '); return false;"'
                . ' title="' . $text . '">' . $image . '</a>';
        }

        $href = $url !== '' ? $url : $picture;
        $output = '<a href="#' . $target . '" title="' . $text . '" data-bs-toggle="modal">' . $image . '</a>';
        $output .= HTMLHelper::_('bootstrap.renderModal', $target, [
            'title' => $text,
            'url' => $href,
            'height' => $height,
            'width' => $width,
            'footer' => '<button type="button" class="btn btn-default" data-bs-dismiss="modal">' . Text::_('JCANCEL') . '</button>',
        ]);

        return $output;
    }

    public static function showMatchTime(&$game, &$config, &$overallconfig, &$project): string
    {
        $output = '';
        $overallconfig['time_format'] = $overallconfig['time_format'] ?? 'H:i';
        $timeSuffix = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CLOCK');
        if ($timeSuffix === 'COM_SPORTSMANAGEMENT_GLOBAL_CLOCK') {
            $timeSuffix = '%1$s&nbsp;h';
        }

        if (strtotime((string) ($game->match_date ?? ''))) {
            $matchTime = HTMLHelper::date($game->match_date, $overallconfig['time_format'], 'UTC');
            $output .= !empty($config['show_time_suffix']) ? sprintf($timeSuffix, $matchTime) : $matchTime;
            $config['mark_now_playing'] = $config['mark_now_playing'] ?? 0;

            if (!empty($config['mark_now_playing'])) {
                $now = time();
                $elapsed = ((int) ($project->halftime ?? 0) * ((int) ($project->game_parts ?? 1) - 1)) + (int) ($project->game_regular_time ?? 0);
                if (!empty($project->allow_add_time) && ($game->team1_result ?? null) == ($game->team2_result ?? null)) {
                    $elapsed += (int) ($project->add_time ?? 0);
                }
                $elapsed *= 60;
                $parts = preg_split('/-| |:/', (string) $game->match_date);
                if (is_array($parts) && count($parts) >= 6) {
                    $matchStamp = mktime((int) $parts[3], (int) $parts[4], (int) $parts[5], (int) $parts[1], (int) $parts[2], (int) $parts[0]);
                    if ($now >= $matchStamp && $matchStamp + $elapsed >= $now) {
                        $title = str_replace('%STARTTIME%', $output . ' ', trim(htmlspecialchars((string) ($config['mark_now_playing_alt_text'] ?? ''))));
                        $title = str_replace('%ACTUALTIME%', self::mark_now_playing($now, $matchStamp, $config, $project), $title);
                        $style = !empty($config['mark_now_playing_blink']) ? ' style="text-decoration:blink"' : '';
                        $output = '<b><i><acronym title="' . $title . '"' . $style . '>'
                            . Text::_((string) ($config['mark_now_playing_text'] ?? '')) . '</acronym></i></b>';
                    }
                }
            }
        } else {
            $matchTime = '--&nbsp;:&nbsp;--';
            $output .= !empty($config['show_time_suffix']) ? sprintf($timeSuffix, $matchTime) : $matchTime;
        }

        return $output;
    }

    public static function mark_now_playing($thisTime, $matchStamp, &$config, &$project): string
    {
        $goneSinceBegin = (int) (($thisTime - $matchStamp) / 60);
        $gameParts = max(1, (int) ($project->game_parts ?? 1));
        $partsTime = (int) ((int) ($project->game_regular_time ?? 0) / $gameParts);
        $overtime = !empty($project->allow_add_time) ? 1 : 0;
        $text = Text::_('COM_SPORTSMANAGEMENT_RESULTS_LIVE_WRONG');

        for ($part = 1; $part <= $gameParts + $overtime; $part++) {
            $start = ($part - 1) * ((int) ($project->halftime ?? 0) + $partsTime);
            $end = $start + $partsTime;
            $next = $end + (int) ($project->halftime ?? 0);

            if ($goneSinceBegin >= $start && $goneSinceBegin <= $end) {
                $text = str_replace('%PART%', (string) $part, trim(htmlspecialchars((string) ($config['mark_now_playing_alt_actual_time'] ?? ''))));
                $text = str_replace('%MINUTE%', (string) ($goneSinceBegin + 1 - ($part - 1) * (int) ($project->halftime ?? 0)), $text);
                break;
            }
            if ($goneSinceBegin > $end && $goneSinceBegin < $next) {
                $text = str_replace('%PART%', (string) $part, trim(htmlspecialchars((string) ($config['mark_now_playing_alt_actual_break'] ?? ''))));
                break;
            }
        }

        return $text;
    }

    public static function showDivisonRemark(&$hometeam, &$guestteam, &$config, $divisionId = 0): string
    {
        $output = '';
        if (!empty($config['switch_home_guest'])) {
            $tmp = $hometeam;
            $hometeam = $guestteam;
            $guestteam = $tmp;
        }

        $divisionId = (int) $divisionId;
        if ($divisionId > 0) {
            $db = self::database();
            $query = $db->getQuery(true)
                ->select([$db->quoteName('id'), $db->quoteName('alias'), $db->quoteName('name'), $db->quoteName('shortname')])
                ->from($db->quoteName('#__sportsmanagement_division'))
                ->where($db->quoteName('id') . ' = ' . $divisionId);
            $db->setQuery($query, 0, 1);
            $division = $db->loadObject();

            foreach ([$hometeam, $guestteam] as $team) {
                $team->division_id = $divisionId;
                $team->division_slug = $division ? $division->id . ':' . $division->alias : $divisionId . ':';
                $team->division_name = (string) ($division->name ?? '');
                $team->division_shortname = (string) ($division->shortname ?? '');
            }
        } else {
            foreach ([$hometeam, $guestteam] as $team) {
                $team->division_id = 0;
                $team->division_slug = '0:';
                $team->division_name = '';
                $team->division_shortname = '';
            }
        }

        if ((int) ($hometeam->division_id ?? 0) <= 0 || (int) ($guestteam->division_id ?? 0) <= 0) {
            return '&nbsp;';
        }

        $config['spacer'] = $config['spacer'] ?? '/';
        $nameType = 'division_' . ($config['show_division_name'] ?? 'name');
        $output .= self::divisionLabel($hometeam, $nameType, $config);
        if ((int) $hometeam->division_id !== (int) $guestteam->division_id) {
            $output .= $config['spacer'] . self::divisionLabel($guestteam, $nameType, $config);
        }

        return $output;
    }

    public static function showMatchPlayground(&$game, $config = []): string
    {
        $projectTeamId = (int) ($game->projectteam1_id ?? 0);
        if (!isset(self::$teams[$projectTeamId])) {
            self::$teams[$projectTeamId] = (object) ['standard_playground' => 0, 'club_id' => 0];
        }
        $team = self::$teams[$projectTeamId];

        if (empty($config['show_playground']) && empty($config['show_playground_alert'])) {
            return '';
        }
        if (!property_exists($game, 'playground_id')) {
            return '';
        }

        $db = self::database();
        if (empty($game->playground_id)) {
            $game->playground_id = (int) ($team->standard_playground ?? 0);
        }
        if (empty($game->playground_id) && !empty($team->club_id)) {
            $query = $db->getQuery(true)
                ->select($db->quoteName('standard_playground'))
                ->from($db->quoteName('#__sportsmanagement_club'))
                ->where($db->quoteName('id') . ' = ' . (int) $team->club_id);
            $db->setQuery($query, 0, 1);
            $game->playground_id = (int) $db->loadResult();
            $team->standard_playground = $game->playground_id;
        }

        if (empty($config['show_playground']) && !empty($config['show_playground_alert'])
            && (int) ($team->standard_playground ?? 0) === (int) $game->playground_id) {
            echo '-';
            return '';
        }

        $boldStart = '';
        $boldEnd = '';
        $toolTipTitle = Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_MATCH');
        $alert = '';
        if ((int) ($team->standard_playground ?? 0) !== (int) $game->playground_id) {
            if ((int) ($config['show_playground_alert'] ?? 0) === 1) {
                $boldStart = '<b style="color:red; ">';
                $boldEnd = '</b>';
                $toolTipTitle = Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_NEWS');
            } elseif ((int) ($config['show_playground_alert'] ?? 0) === 2) {
                $alert = '<b style="color:red; ">' . Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_NEWS') . ':</b> ';
            }
        }

        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_playground'))
            ->where($db->quoteName('id') . ' = ' . (int) $game->playground_id);
        $db->setQuery($query, 0, 1);
        $playground = $db->loadObject() ?: (object) ['id' => 0, 'alias' => '', 'name' => '', 'short_name' => '', 'address' => '', 'zipcode' => '', 'city' => ''];
        $toolTipText = (string) ($playground->name ?? '') . '&lt;br /&gt;'
            . (string) ($playground->address ?? '') . '&lt;br /&gt;'
            . (string) ($playground->zipcode ?? '') . ' ' . (string) ($playground->city ?? '') . '&lt;br /&gt;';
        $input = Factory::getApplication()->getInput();
        $slug = (string) ($game->playground_slug ?? '');
        if ($slug === '' && !empty($playground->id)) {
            $slug = $playground->id . ':' . ($playground->alias ?? '');
        }
        $link = SiteRouteHelper::view('playground', [
            'cfg_which_database' => $input->getInt('cfg_which_database', 0),
            's' => $input->getInt('s', 0),
            'p' => (string) ($game->project_slug ?? ''),
            'pgid' => $slug,
        ]);
        $name = (($config['show_playground_name'] ?? 'name') === 'name') ? $playground->name : $playground->short_name;
        $html = '<span class="hasTip" title="' . $toolTipTitle . ' :: ' . $toolTipText . '">'
            . $alert . HTMLHelper::link($link, $boldStart . $name . $boldEnd) . ' </span>';
        echo $html;

        return '';
    }

    public static function getThumbUpDownImg($game, $projectteamId, $attributes = null)
    {
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $useFontAwesome = (bool) $params->get('use_fontawesome');
        $result = \sportsmanagementHelper::getTeamMatchResult($game, $projectteamId);
        if ($result === false) {
            return false;
        }

        $icons = [
            -1 => ['fa-thumbs-down', 'lost', Text::_('COM_SPORTSMANAGEMENT_LOST'), 'red'],
            0 => ['fa-handshake-o', 'draw', Text::_('COM_SPORTSMANAGEMENT_DRAW'), 'yellow'],
            1 => ['fa-thumbs-up', 'won', Text::_('COM_SPORTSMANAGEMENT_WON'), 'green'],
        ];
        $result = (int) $result;
        if (!isset($icons[$result])) {
            return false;
        }
        [$icon, $state, $alt, $colour] = $icons[$result];
        $attributes = $attributes ? array_merge(['title' => $alt], $attributes) : ['title' => $alt];

        if (version_compare(JVERSION, '4.0.0', 'ge') || $useFontAwesome) {
            $stackClass = 'fa-stack fa-xs ' . ($useFontAwesome ? $state : '" style="color:' . $colour);
            $title = implode('|', $attributes);
            return '<span class="' . $stackClass . '"><i class="fa fa-square fa-stack-2x"></i>'
                . '<i class="fa ' . $icon . ' fa-stack-1x fa-inverse" title="' . $title . '"></i></span>';
        }

        return HTMLHelper::image('media/com_sportsmanagement/jl_images/' . $state . '.png', $alt, $attributes);
    }

    private static function formatEventContainer($matchEvent, $event, $projectTeamId, int $showEventInfo, array $config): string
    {
        if ((int) ($matchEvent->event_type_id ?? 0) !== (int) ($event->id ?? 0)
            || (int) ($matchEvent->ptid ?? 0) !== (int) $projectTeamId) {
            return '';
        }

        $output = '<li class="list-inline-item">';
        if ($showEventInfo === 1) {
            $output .= \sportsmanagementHelper::getPictureThumb($event->icon ?? '', Text::_((string) ($event->name ?? '')), 20, 20, 4);
        }
        $minute = str_pad((string) ($matchEvent->event_time ?? 0), 2, '0', STR_PAD_LEFT);
        if (!empty($config['show_event_minute']) && (int) ($matchEvent->event_time ?? 0) > 0) {
            $output .= '<b>' . $minute . '\'</b> ';
        }
        if ($showEventInfo === 2) {
            $output .= Text::_((string) ($event->name ?? '')) . ' ';
        }
        if (strlen((string) ($matchEvent->firstname1 ?? '') . (string) ($matchEvent->lastname1 ?? '')) > 0) {
            $output .= \sportsmanagementHelper::formatName(null, $matchEvent->firstname1 ?? '', $matchEvent->nickname1 ?? '', $matchEvent->lastname1 ?? '', $config['name_format'] ?? 0);
        } else {
            $output .= Text::_('COM_SPORTSMANAGEMENT_UNKNOWN_PERSON');
        }
        if (!empty($config['show_event_sum']) || !empty($config['show_event_notice'])) {
            $sum = (int) ($matchEvent->event_sum ?? 0);
            $notice = (string) ($matchEvent->notice ?? '');
            if ((!empty($config['show_event_sum']) && $sum > 0) || (!empty($config['show_event_notice']) && $notice !== '')) {
                $parts = [];
                if (!empty($config['show_event_sum']) && $sum > 0) {
                    $parts[] = (string) $sum;
                }
                if (!empty($config['show_event_notice']) && $notice !== '') {
                    $parts[] = $notice;
                }
                $output .= ' (' . implode(' | ', $parts) . ')';
            }
        }

        return $output . '</li>';
    }

    private static function formatSubstitutionContainer($substitution, $projectTeamId, string $imgTime, string $imgOut, string $imgIn, array $config): string
    {
        if ((int) ($substitution->ptid ?? 0) !== (int) $projectTeamId) {
            return '';
        }
        $format = $config['name_format'] ?? 0;

        return '<li class="list-inline-item">&nbsp;' . ($substitution->in_out_time ?? '') . '. '
            . Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE') . '<br />'
            . $imgOut . '&nbsp;' . \sportsmanagementHelper::formatName(null, $substitution->out_firstname ?? '', $substitution->out_nickname ?? '', $substitution->out_lastname ?? '', $format)
            . '&nbsp;(' . Text::_((string) ($substitution->out_position ?? '')) . ')<br />'
            . $imgIn . '&nbsp;' . \sportsmanagementHelper::formatName(null, $substitution->firstname ?? '', $substitution->nickname ?? '', $substitution->lastname ?? '', $format)
            . '&nbsp;(' . Text::_((string) ($substitution->in_position ?? '')) . ')<br /><br /></li>';
    }

    private static function divisionLabel(object $team, string $nameType, array $config): string
    {
        $label = (string) ($team->{$nameType} ?? '');
        if (empty($config['show_division_link'])) {
            return $label;
        }
        $input = Factory::getApplication()->getInput();
        $project = self::$project;
        $link = SiteRouteHelper::view('ranking', [
            'cfg_which_database' => $input->getInt('cfg_which_database', 0),
            's' => $input->getInt('s', 0),
            'p' => (string) ($project->slug ?? ''),
            'type' => 0,
            'r' => (string) ($project->round_slug ?? ''),
            'from' => 0,
            'to' => 0,
            'division' => (string) ($team->division_slug ?? ''),
        ]);

        return HTMLHelper::link($link, $label);
    }

    private static function database(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }
}
