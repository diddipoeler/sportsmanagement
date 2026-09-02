<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage globalviews
 * @file       default_sectionheader.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$app = Factory::getApplication();
$input = $app->getInput();
$view = $input->getCmd('view');

if ($view === 'roster' && !empty($this->config['show_drop_down_menue'])) {
    $this->getDocument()->getWebAssetManager()->registerAndUseScript(
        'com_sportsmanagement.site.sectionheader',
        'components/com_sportsmanagement/assets/js/sectionheader.js',
        ['version' => 'auto'],
        ['defer' => true]
    );
}
?>
<!-- START: Contentheading -->
<div class="<?php echo $this->divclassrow; ?>" id="sectionheader">
    <?php
    switch ($view) {
        case 'matchreport':
            ?>
            <table class="table">
                <tr>
                    <td class="contentheading">
                        <?php
                        $pageTitle = 'COM_SPORTSMANAGEMENT_MATCHREPORT_TITLE';
                        $timestamp = strtotime($this->match->match_date);

                        if (isset($this->round->name) && $timestamp) {
                            $matchDate = sportsmanagementHelper::getTimestamp($this->match->match_date, 1);
                            echo '&nbsp;' . Text::sprintf(
                                $pageTitle,
                                $this->round->name,
                                HTMLHelper::date($matchDate, Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_GAMES_DATE')),
                                sportsmanagementHelperHtml::showMatchTime($this->match, $this->config, $this->overallconfig, $this->project)
                            );
                        } elseif (isset($this->round->name)) {
                            echo '&nbsp;' . Text::sprintf($pageTitle, $this->round->name, '', '');
                        } else {
                            echo '&nbsp;' . Text::sprintf($pageTitle, '', '', '');
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <?php
            break;

        case 'player':
            ?>
            <table class="table">
                <tr>
                    <td class="contentheading">
                        <?php
                        echo Text::sprintf(
                            'COM_SPORTSMANAGEMENT_PLAYER_INFORMATION',
                            sportsmanagementHelper::formatName(
                                null,
                                $this->person->firstname,
                                $this->person->nickname,
                                $this->person->lastname,
                                $this->config['name_format']
                            )
                        );

                        if ($this->showediticon) {
                            $link = 'index.php?option=com_sportsmanagement&tmpl=component&view=editperson&id=' . $this->person->id;
                            echo sportsmanagementHelperHtml::getBootstrapModalImage(
                                'editperson' . $this->person->id,
                                'administrator/components/com_sportsmanagement/assets/images/edit.png',
                                Text::_('COM_SPORTSMANAGEMENT_PERSON_EDIT_DETAILS'),
                                '20',
                                $link,
                                $this->modalwidth,
                                $this->modalheight,
                                $this->overallconfig['use_jquery_modal']
                            );
                        }

                        if (isset($this->teamPlayer->injury) && $this->teamPlayer->injury) {
                            $imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_INJURED');
                            echo '&nbsp;&nbsp;' . HTMLHelper::image(
                                'images/com_sportsmanagement/database/events/' . $this->project->fs_sport_type_name . '/injured.gif',
                                $imageTitle,
                                ['title' => $imageTitle]
                            );
                        }

                        if (isset($this->teamPlayer->suspension) && $this->teamPlayer->suspension) {
                            $imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_SUSPENDED');
                            echo '&nbsp;&nbsp;' . HTMLHelper::image(
                                'images/com_sportsmanagement/database/events/' . $this->project->fs_sport_type_name . '/suspension.gif',
                                $imageTitle,
                                ['title' => $imageTitle]
                            );
                        }

                        if (isset($this->teamPlayer->away) && $this->teamPlayer->away) {
                            $imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_AWAY');
                            echo '&nbsp;&nbsp;' . HTMLHelper::image(
                                'images/com_sportsmanagement/database/events/' . $this->project->fs_sport_type_name . '/away.gif',
                                $imageTitle,
                                ['title' => $imageTitle]
                            );
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <?php
            break;

        case 'staff':
            ?>
            <table class="table">
                <tr>
                    <td class="contentheading">
                        <?php
                        echo $this->title;

                        if ($this->showediticon) {
                            $link = 'index.php?option=com_sportsmanagement&tmpl=component&view=editperson&id=' . $this->person->id;
                            echo sportsmanagementHelperHtml::getBootstrapModalImage(
                                'personedit' . $this->person->id,
                                'administrator/components/com_sportsmanagement/assets/images/edit.png',
                                Text::_('COM_SPORTSMANAGEMENT_PERSON_EDIT_DETAILS'),
                                '20',
                                $link,
                                $this->modalwidth,
                                $this->modalheight,
                                $this->overallconfig['use_jquery_modal']
                            );
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <?php
            break;

        case 'results':
            ?>
            <table class="table">
                <tr>
                    <td class="contentheading">
                        <?php
                        if ($this->roundid) {
                            $title = Text::_('COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS');

                            if (isset($this->division)) {
                                $title = Text::sprintf(
                                    'COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS2',
                                    '<i>' . $this->division->name . '</i>'
                                );
                            }

                            sportsmanagementHelperHtml::showMatchdaysTitle($title, $this->roundid, $this->config);

                            if ($this->showediticon) {
                                $routeparameter = [];
                                $routeparameter['cfg_which_database'] = $input->getInt('cfg_which_database', 0);
                                $routeparameter['s'] = $input->getInt('s', 0);
                                $routeparameter['p'] = sportsmanagementModelProject::$projectslug;
                                $routeparameter['r'] = sportsmanagementModelProject::$roundslug;
                                $routeparameter['division'] = sportsmanagementModelResults::$divisionid;
                                $routeparameter['mode'] = sportsmanagementModelResults::$mode;
                                $routeparameter['order'] = sportsmanagementModelResults::$order;
                                $routeparameter['layout'] = $this->config['result_style_edit'];
                                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);

                                $imgTitle = Text::_('COM_SPORTSMANAGEMENT_RESULTS_ENTER_EDIT_RESULTS');
                                $desc = HTMLHelper::image(
                                    'media/com_sportsmanagement/jl_images/edit.png',
                                    $imgTitle,
                                    ['title' => $imgTitle]
                                );
                                echo ' ' . HTMLHelper::link($link, $desc);
                            }
                        } else {
                            sportsmanagementHelperHtml::showMatchdaysTitle(
                                Text::_('COM_SPORTSMANAGEMENT_RESULTS_PLAN'),
                                0,
                                $this->config
                            );
                        }
                        ?>
                    </td>
                    <?php if ($this->config['show_matchday_dropdown'] == 1) : ?>
                        <form name="resultsRoundSelector" method="post">
                            <input type="hidden" name="option" value="com_sportsmanagement">
                            <td></td>
                            <td class="contentheading text-end">
                                <?php echo sportsmanagementHelperHtml::getRoundSelectNavigation(false, $input->getInt('cfg_which_database', 0)); ?>
                            </td>
                            <td></td>
                        </form>
                    <?php endif; ?>
                </tr>
            </table>
            <?php
            break;

        case 'teamplan':
            ?>
            <table class="table">
                <tr>
                    <td class="contentheading">
                        <?php
                        $output = '';

                        if (isset($this->division) && is_a($this->division, 'LeagueDivision')) {
                            $output .= ' ' . $this->division->name . ' ';
                        }

                        if (!empty($this->ptid)) {
                            $output .= ' ' . $this->teams[$this->ptid]->name;
                        } else {
                            $output .= ' ' . $this->project->name;
                        }

                        echo Text::sprintf('COM_SPORTSMANAGEMENT_TEAMPLAN_PAGE_TITLE', $output);
                        ?>
                    </td>
                    <?php if ($this->config['show_ical_link']) : ?>
                        <td class="contentheading text-end">
                            <?php
                            if (!is_null($this->ptid)) {
                                $routeparameter = [];
                                $routeparameter['cfg_which_database'] = $input->getInt('cfg_which_database', 0);
                                $routeparameter['s'] = $input->getInt('s', 0);
                                $routeparameter['p'] = $this->project->id;
                                $routeparameter['tid'] = $this->teams[$this->ptid]->team_id;
                                $routeparameter['division'] = 0;
                                $routeparameter['mode'] = 0;
                                $routeparameter['ptid'] = $this->ptid;
                                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('ical', $routeparameter);
                                $text = HTMLHelper::_(
                                    'image',
                                    'administrator/components/com_sportsmanagement/assets/images/calendar.png',
                                    Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_ICAL_EXPORT')
                                );
                                $attribs = ['title' => Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_ICAL_EXPORT')];
                                echo HTMLHelper::_('link', $link, $text, $attribs);
                            }
                            ?>
                        </td>
                    <?php endif; ?>
                </tr>
            </table>
            <br>
            <?php
            break;

        case 'curve':
            ?>
            <table class="table">
                <tr>
                    <td class="contentheading">
                        <a name="division<?php echo $this->divisions[0]->name; ?>"></a>
                        <?php
                        echo Text::_('COM_SPORTSMANAGEMENT_CURVE_TITLE');

                        if ($this->division) {
                            echo ' ' . $this->division->name;
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <br>
            <?php
            break;

        case 'matrix':
            $this->notes = [];
            $ausgabe = '&nbsp;' . Text::_('COM_SPORTSMANAGEMENT_MATRIX');

            if ($this->divisionid) {
                $ausgabe .= ' ' . $this->division->name;
            }

            if ($this->roundid) {
                $ausgabe .= ' - ' . $this->round->name;
            }
            ?>
            <br>
            <?php
            $this->notes[] = $ausgabe;
            echo $this->loadTemplate('jsm_notes');
            break;

        case 'roster':
            ?>
            <table class="table">
                <tr>
                    <td class="contentheading">
                        <?php
                        if ($this->config['show_team_shortform'] == 1 && !empty($this->team->short_name)) {
                            echo '&nbsp;' . Text::sprintf(
                                'COM_SPORTSMANAGEMENT_ROSTER_TITLE2',
                                $this->team->name,
                                $this->team->short_name
                            );
                        } else {
                            echo '&nbsp;' . Text::sprintf('COM_SPORTSMANAGEMENT_ROSTER_TITLE', $this->team->name);
                        }
                        ?>
                    </td>

                    <form name="resultsRoundSelector" method="post">
                        <input type="hidden" name="option" value="com_sportsmanagement">
                        <?php
                        if ($this->config['show_drop_down_menue']) {
                            if ($this->config['show_players']) {
                                echo '<td>' . HTMLHelper::_(
                                    'select.genericlist',
                                    $this->lists['type'],
                                    'type',
                                    'class="form-select" size="1" data-jsm-sectionheader-submit',
                                    'value',
                                    'text',
                                    $this->type
                                ) . '</td>';
                            }

                            if ($this->config['show_staff']) {
                                echo '<td>' . HTMLHelper::_(
                                    'select.genericlist',
                                    $this->lists['typestaff'],
                                    'typestaff',
                                    'class="form-select" size="1" data-jsm-sectionheader-submit',
                                    'value',
                                    'text',
                                    $this->typestaff
                                ) . '</td>';
                            }
                        }
                        ?>
                    </form>
                </tr>
            </table>
            <br>
            <?php
            break;

        case 'nextmatch':
            ?>
            <table class="table">
                <tr>
                    <td class="contentheading">
                        <?php
                        if ($this->match->match_date == '0000-00-00') {
                            echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_DATE_EMPTY');
                        } else {
                            echo HTMLHelper::date(
                                $this->match->match_date,
                                Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_GAMES_DATE')
                            ) . ' ' . sportsmanagementHelperHtml::showMatchTime(
                                $this->match,
                                $this->config,
                                $this->overallconfig,
                                $this->project
                            );
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <?php
            break;

        case 'teaminfo':
            $this->notes = [];
            $ausgabe = Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_PAGE_TITLE') . ' - ' . $this->team->tname;

            if ($this->showediticon) {
                $link = 'index.php?option=com_sportsmanagement&tmpl=component&view=editprojectteam&ptid=' . $this->projectteamid
                    . '&tid=' . $this->teamid . '&p=' . $this->project->id;
                $ausgabe .= sportsmanagementHelperHtml::getBootstrapModalImage(
                    'projectteamedit' . $this->projectteamid,
                    'administrator/components/com_sportsmanagement/assets/images/edit.png',
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMINFO_EDIT_DETAILS'),
                    '20',
                    $link,
                    $this->modalwidth,
                    $this->modalheight,
                    $this->overallconfig['use_jquery_modal']
                );

                $link = 'index.php?option=com_sportsmanagement&tmpl=component&view=editteam&ptid=' . $this->projectteamid
                    . '&tid=' . $this->teamid . '&p=' . $this->project->id;
                $ausgabe .= sportsmanagementHelperHtml::getBootstrapModalImage(
                    'teamedit' . $this->projectteamid,
                    'administrator/components/com_sportsmanagement/assets/images/teams.png',
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMINFO_EDIT_DETAILS'),
                    '20',
                    $link,
                    $this->modalwidth,
                    $this->modalheight,
                    $this->overallconfig['use_jquery_modal']
                );
            }

            $this->notes[] = $ausgabe;
            echo $this->loadTemplate('jsm_notes');
            break;

        case 'clubinfo':
            $this->notes = [];
            $this->notes[] = Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_TITLE') . ' ' . $this->club->name;

            if ($this->showediticon) {
                $link = sportsmanagementHelperRoute::getClubInfoRoute(
                    $this->project->id,
                    $this->club->id,
                    'club.edit'
                );
                $this->notes[] = sportsmanagementHelperHtml::getBootstrapModalImage(
                    'clubedit' . $this->club->id,
                    'administrator/components/com_sportsmanagement/assets/images/edit.png',
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBINFO_EDIT_DETAILS'),
                    '20',
                    $link,
                    $this->modalwidth,
                    $this->modalheight,
                    $this->overallconfig['use_jquery_modal']
                );
            }

            echo $this->loadTemplate('jsm_notes');
            break;

        default:
            ?>
            <div class="color-box" id="sectionheader">
                <div class="shadow">
                    <div class="info-tab note-icon" title="sectionheader"><i></i></div>
                    <div class="note-box">
                        <p><strong><?php echo $this->headertitle; ?></strong></p>
                    </div>
                </div>
            </div>
            <br>
            <?php
            break;
    }
    ?>
</div>
<!-- END: Contentheading -->
