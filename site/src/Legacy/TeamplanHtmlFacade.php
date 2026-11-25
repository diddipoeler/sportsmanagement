<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
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
    /**
     * Return the active Joomla application for the historical teamplan route context.
     * @return CMSApplicationInterface Active Joomla application.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        return Factory::getApplication();
    }

    public static $project = null;
    public static array $teams = [];

    /**
     * Build the match event display for the home and away teams. In tab mode group recorded events by type and include a substitution tab when available; otherwise render the events in two inline columns.
     * @param mixed $matchInfo Match and project-team identifiers.
     * @param mixed $projectevents Project event definitions.
     * @param mixed $matchevents Recorded match events.
     * @param mixed $substitutions Recorded player substitutions.
     * @param array $config Event presentation options.
     * @param mixed $project Project sport context.
     * @return string Event container markup.
     */
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

    /**
     * Generate a thumbnail link using the selected legacy JCE popup, secondary browser window or Bootstrap modal mode. The supplied image and link attributes are used to construct the markup.
     * @param mixed $target Modal or link identifier.
     * @param mixed $picture Image source.
     * @param mixed $text Image label and title.
     * @param mixed $pictureheight Thumbnail height in pixels.
     * @param mixed $url Optional destination URL.
     * @param mixed $width Popup width.
     * @param mixed $height Popup height.
     * @param mixed $useJqueryModal Legacy popup mode.
     * @param mixed $schemaorg Image metadata attribute name.
     * @param mixed $schemaorgvalue Image metadata attribute value.
     * @return string Thumbnail and popup markup.
     */
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

    /**
     * Format the scheduled match time using the configured UTC display format and optional clock suffix. When live marking is enabled, replace the time with a live indicator during the calculated playing window.
     * @param mixed $game Match record passed by reference.
     * @param array $config Match display configuration passed by reference.
     * @param array $overallconfig Overall time format configuration passed by reference.
     * @param mixed $project Project timing rules passed by reference.
     * @return string Match time or live indicator markup.
     */
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

    /**
     * Calculate the elapsed match minute and playing period, including configured breaks and the optional extra period. Substitute period and minute placeholders in the configured live tooltip.
     * @param mixed $thisTime Current Unix timestamp.
     * @param mixed $matchStamp Scheduled match Unix timestamp.
     * @param array $config Live display settings passed by reference.
     * @param mixed $project Match duration rules passed by reference.
     * @return string Live period or break description.
     */
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

    /**
     * Apply the requested home-away display order, update both team objects with the selected division metadata or clear it, then render one or two division labels according to the configuration.
     * @param mixed $hometeam Home team passed by reference.
     * @param mixed $guestteam Away team passed by reference.
     * @param array $config Division display options passed by reference.
     * @param mixed $divisionId Selected division identifier.
     * @return string Division labels or nonbreaking space.
     */
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

    /**
     * Resolve the match venue from the match record, home team or club default and cache the fallback on the team object. Emit the configured venue link or alternative-venue warning directly.
     * @param mixed $game Match record passed by reference.
     * @param array $config Venue display options.
     * @return string Empty string; output is emitted directly.
     */
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
        $input = self::siteApplication()->getInput();
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

    /**
     * Determine the selected team's match outcome using the legacy helper and render the corresponding win, draw or loss icon in Font Awesome or historical image format.
     * @param mixed $game Match record.
     * @param mixed $projectteamId Project-team identifier.
     * @param array|null $attributes Optional icon attributes.
     * @return string|false Outcome icon markup or false for an unavailable result.
     */
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

    /**
     * Render a recorded event only when its event type and project team match the requested column. Include the configured event icon or name, minute, player name, count and notice.
     * @param mixed $matchEvent Recorded match event.
     * @param mixed $event Project event definition.
     * @param mixed $projectTeamId Requested project-team identifier.
     * @param int $showEventInfo Event display mode.
     * @param array $config Event display options.
     * @return string Event list item or empty string.
     */
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

    /**
     * Render a substitution for the requested project team, including its minute, outgoing and incoming players, position labels and supplied icons.
     * @param mixed $substitution Recorded substitution.
     * @param mixed $projectTeamId Requested project-team identifier.
     * @param string $imgTime Retained time icon markup, currently unused.
     * @param string $imgOut Outgoing-player icon markup.
     * @param string $imgIn Incoming-player icon markup.
     * @param array $config Name display options.
     * @return string Substitution list item or empty string.
     */
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

    /**
     * Read the selected division name property and optionally link it to the project ranking using the current request database, season and project context.
     * @param object $team Team division metadata.
     * @param string $nameType Division label property.
     * @param array $config Division link options.
     * @return string Division label or link markup.
     */
    private static function divisionLabel(object $team, string $nameType, array $config): string
    {
        $label = (string) ($team->{$nameType} ?? '');
        if (empty($config['show_division_link'])) {
            return $label;
        }
        $input = self::siteApplication()->getInput();
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

    /**
     * Retrieve the primary Joomla database service from the dependency injection container for venue and division lookups.
     * @return DatabaseInterface Joomla database connection.
     */
    private static function database(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }
}
