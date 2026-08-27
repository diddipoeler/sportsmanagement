<?php
/**
 * SportsManagement DFC results edit row.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

$app = Factory::getApplication();
$input = $app->getInput();
$user = $app->getIdentity();
$match = $this->game;
$i = $this->i;
$thismatch = $this->model->getTable('match');
$thismatch->bind(get_object_vars($match));

[$datum, $uhrzeit] = array_pad(explode(' ', (string) ($thismatch->match_date ?? ''), 2), 2, '');
$databaseSelector = $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
$seasonId = (int) ($this->project->season_id ?? $input->getInt('s', 0));
$projectId = (int) ($this->project->id ?? $input->getInt('p', 0));
$roundId = (int) ($this->roundid ?? $input->getInt('r', 0));
$divisionId = $input->getInt('division', 0);
$mode = $input->getInt('mode', 0);
$order = $input->getInt('order', 0);

$editMatchRoute = static function (string $layout, int $teamId = 0) use (
    $thismatch,
    $datum,
    $databaseSelector,
    $seasonId,
    $projectId,
    $roundId,
    $divisionId,
    $mode,
    $order
): string {
    return SiteRouteHelper::view('editmatch', [
        'cfg_which_database' => $databaseSelector,
        's' => $seasonId,
        'p' => $projectId,
        'r' => $roundId,
        'division' => $divisionId,
        'mode' => $mode,
        'order' => $order,
        'layout' => $layout,
        'matchid' => (int) $thismatch->id,
        'tmpl' => 'component',
        'oldlayout' => 'form_dfcday',
        'team' => $teamId,
        'pteam' => $datum,
        'match_date' => null,
        'doubleevents' => 0,
    ]);
};

$team1 = $this->teams[(int) ($thismatch->projectteam1_id ?? 0)] ?? (object) [
    'projectteamid' => 0,
    'admin' => 0,
    'name' => '',
];
$team2 = $this->teams[(int) ($thismatch->projectteam2_id ?? 0)] ?? (object) [
    'projectteamid' => 0,
    'admin' => 0,
    'name' => '',
];
$userIsTeamAdmin = (int) $user->id > 0
    && ((int) $user->id === (int) ($team1->admin ?? 0) || (int) $user->id === (int) ($team2->admin ?? 0));

$teamsoptions = [HTMLHelper::_('select.option', '0', '- ' . Text::_('Select Team') . ' -')];
foreach ($this->teams as $team) {
    $teamsoptions[] = HTMLHelper::_('select.option', $team->projectteamid, $team->name, 'value', 'text');
}
?>
<tr id="result-<?php echo (int) $match->id; ?>">
    <td valign="top">
        <?php
        if ((int) ($thismatch->checked_out ?? 0) > 0 && (int) $thismatch->checked_out !== (int) $user->id) {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('username'))
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('id') . ' = ' . (int) $thismatch->checked_out);
            $db->setQuery($query, 0, 1);
            $username = (string) ($db->loadResult() ?? '');
            ?>
            <acronym title="CHECKED OUT BY <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">X</acronym>
            <?php
        } else {
            ?>
            <input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo (int) $thismatch->id; ?>"/>
            <?php
        }
        ?>
    </td>

    <td valign="top">
        <?php
        $url = $editMatchRoute('edit', (int) $team1->projectteamid);
        echo sportsmanagementHelperHtml::getBootstrapModalImage(
            'edit' . $thismatch->id,
            'administrator/components/com_sportsmanagement/assets/images/edit.png',
            Text::_('COM_SPORTSMANAGEMENT_EDIT_MATCH_DETAILS_BACKEND'),
            '20',
            $url,
            $this->modalwidth,
            $this->modalheight,
            $this->overallconfig['use_jquery_modal']
        );
        ?>
    </td>

    <?php if ($this->project->project_type == 'DIVISIONS_LEAGUE') { ?>
        <td style="text-align:center;">
            <?php echo htmlspecialchars((string) ($match->divhome ?? ''), ENT_QUOTES, 'UTF-8'); ?>
            <input type="hidden" name="division_id<?php echo (int) $thismatch->id; ?>"
                   value="<?php echo (int) ($match->divhomeid ?? 0); ?>"/>
        </td>
    <?php } ?>

    <td align="center" class="nowrap" valign="top">
        <?php
        $url = $editMatchRoute('editlineup', (int) $team1->projectteamid);
        echo sportsmanagementHelperHtml::getBootstrapModalImage(
            'home_lineup' . $team1->projectteamid,
            'administrator/components/com_sportsmanagement/assets/images/players_add.png',
            Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EDIT_LINEUP_HOME'),
            '20',
            $url,
            $this->modalwidth,
            $this->modalheight,
            $this->overallconfig['use_jquery_modal']
        );
        ?>
    </td>
    <td>
        <?php
        $append = ' class="inputbox" size="1" onchange="document.getElementById(\'cb' . $i . '\').checked=true;" style="font-size:9px;" ';
        if (!$userIsTeamAdmin && empty($match->allowed)) {
            $append .= ' disabled="disabled"';
        }
        echo HTMLHelper::_(
            'select.genericlist',
            $teamsoptions,
            'projectteam1_id' . $thismatch->id,
            $append,
            'value',
            'text',
            (int) $team1->projectteamid
        );
        if ($this->config['results_below']) {
            echo '<br/>';
        } else {
            echo '</td><td nowrap="nowrap" align="center" valign="top">';
        }
        echo HTMLHelper::_(
            'select.genericlist',
            $teamsoptions,
            'projectteam2_id' . $thismatch->id,
            $append,
            'value',
            'text',
            (int) $team2->projectteamid
        );
        ?>
    </td>
    <td>
        <?php
        $url = $editMatchRoute('editlineup', (int) $team2->projectteamid);
        echo sportsmanagementHelperHtml::getBootstrapModalImage(
            'away_lineup' . $team2->projectteamid,
            'administrator/components/com_sportsmanagement/assets/images/players_add.png',
            Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EDIT_LINEUP_AWAY'),
            '20',
            $url,
            $this->modalwidth,
            $this->modalheight,
            $this->overallconfig['use_jquery_modal']
        );
        ?>
    </td>

    <?php
    if ($this->config['results_below']) {
        $partresults1 = explode(';', (string) ($thismatch->team1_result_split ?? ''));
        $partresults2 = explode(';', (string) ($thismatch->team2_result_split ?? ''));
        for ($x = 0; $x < (int) $this->project->game_parts; $x++) {
            ?>
            <td align="center" valign="top">
                <input type="text" style="font-size:9px;" name="team1_result_split<?php echo $thismatch->id; ?>[]"
                       size="2" tabindex="1" class="inputbox"
                       value="<?php echo htmlspecialchars((string) ($partresults1[$x] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                       onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;"/><br/>
                <input type="text" style="font-size:9px;" name="team2_result_split<?php echo $thismatch->id; ?>[]"
                       size="2" tabindex="1" class="inputbox"
                       value="<?php echo htmlspecialchars((string) ($partresults2[$x] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                       onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;"/>
            </td>
            <?php
        }

        if ($this->project->allow_add_time) {
            ?>
            <td valign="top" align="center">
                <span id="ot<?php echo $thismatch->id; ?>"
                      style="visibility:<?php echo ((int) $thismatch->match_result_type > 0) ? 'visible' : 'hidden'; ?>">
                    <input type="text" style="font-size:9px;" name="team1_result_ot<?php echo $thismatch->id; ?>"
                           value="<?php echo htmlspecialchars((string) ($thismatch->team1_result_ot ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                           size="2" tabindex="1" class="inputbox"
                           onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;"/><br/>
                    <input type="text" style="font-size:9px;" name="team2_result_ot<?php echo $thismatch->id; ?>"
                           value="<?php echo htmlspecialchars((string) ($thismatch->team2_result_ot ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                           size="2" tabindex="1" class="inputbox"
                           onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;"/>
                </span>
            </td>
            <?php
        }
        ?>
        <td class="nowrap" valign="top" align="center">
            <input type="text" style="font-size:9px;" name="team1_result<?php echo $thismatch->id; ?>"
                   value="<?php echo htmlspecialchars((string) ($thismatch->team1_result ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                   size="2" tabindex="1" class="inputbox"
                   onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;"/><br/>
            <input type="text" style="font-size:9px;" name="team2_result<?php echo $thismatch->id; ?>"
                   value="<?php echo htmlspecialchars((string) ($thismatch->team2_result ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                   size="2" tabindex="1" class="inputbox"
                   onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;"/>
        </td>
        <?php
        if ($this->project->use_legs) {
            ?>
            <td valign="top" align="center">
                <input type="text" style="font-size:9px;" name="team1_legs<?php echo $thismatch->id; ?>"
                       value="<?php echo htmlspecialchars((string) ($thismatch->team1_legs ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                       size="2" tabindex="1" class="inputbox"
                       onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;"/><br/>
                <input type="text" style="font-size:9px;" name="team2_legs<?php echo $thismatch->id; ?>"
                       value="<?php echo htmlspecialchars((string) ($thismatch->team2_legs ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                       size="2" tabindex="1" class="inputbox"
                       onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;"/>
            </td>
            <?php
        }
    } else {
        ?>
        <td class="nowrap" align="right" valign="top">
            <input type="text" style="font-size:9px;" name="team1_result<?php echo $thismatch->id; ?>"
                   value="<?php echo htmlspecialchars((string) ($thismatch->team1_result ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                   size="1" tabindex="1" class="inputbox"
                   onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;"/>
            <b>:</b>
            <input type="text" style="font-size:9px;" name="team2_result<?php echo $thismatch->id; ?>"
                   value="<?php echo htmlspecialchars((string) ($thismatch->team2_result ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                   size="1" tabindex="1" class="inputbox"
                   onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;"/>
            &nbsp;<?php echo $this->editPartResults($i, $thismatch); ?>
        </td>
        <?php
        if ($this->project->use_legs) {
            ?>
            <td valign="top" align="center">
                <input type="text" style="font-size:9px;" name="team1_legs<?php echo $thismatch->id; ?>"
                       value="<?php echo htmlspecialchars((string) ($thismatch->team1_legs ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                       size="2" tabindex="1" class="inputbox"
                       onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;"/>
                <b>:</b>
                <input type="text" style="font-size:9px;" name="team2_legs<?php echo $thismatch->id; ?>"
                       value="<?php echo htmlspecialchars((string) ($thismatch->team2_legs ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                       size="2" tabindex="1" class="inputbox"
                       onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;"/>
            </td>
            <?php
        }

        if ($this->project->allow_add_time) {
            $resultTypes = [
                HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_RESULTS_REGULAR_TIME')),
                HTMLHelper::_('select.option', '1', Text::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME2')),
                HTMLHelper::_('select.option', '2', Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT2')),
            ];
            $onChange = "document.getElementById('cb{$i}').checked=true;"
                . "document.getElementById('ot{$thismatch->id}').style.visibility="
                . "this.selectedIndex===0?'hidden':'visible';";
            ?>
            <td align="center" valign="top">
                <?php
                echo HTMLHelper::_(
                    'select.genericlist',
                    $resultTypes,
                    'match_result_type' . $thismatch->id,
                    'class="inputbox" size="1" style="font-size:9px;" onchange="' . $onChange . '"',
                    'value',
                    'text',
                    $thismatch->match_result_type
                );
                ?>
            </td>
            <?php
        }

        if ($this->config['show_edit_match_events']) {
            ?>
            <td valign="top">
                <?php
                $url = $editMatchRoute('editevents', (int) $team1->projectteamid);
                echo sportsmanagementHelperHtml::getBootstrapModalImage(
                    'edit_events' . $thismatch->id,
                    'administrator/components/com_sportsmanagement/assets/images/events.png',
                    Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EVENTS_BACKEND'),
                    '20',
                    $url,
                    $this->modalwidth,
                    $this->modalheight,
                    $this->overallconfig['use_jquery_modal']
                );
                ?>
            </td>
            <?php
        }

        if ($this->config['show_edit_match_statistic']) {
            ?>
            <td valign="top">
                <?php
                $url = $editMatchRoute('editstats', (int) $team1->projectteamid);
                echo sportsmanagementHelperHtml::getBootstrapModalImage(
                    'edit_statistics' . $thismatch->id,
                    'administrator/components/com_sportsmanagement/assets/images/calc16.png',
                    Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_STATISTICS_BACKEND'),
                    '20',
                    $url,
                    $this->modalwidth,
                    $this->modalheight,
                    $this->overallconfig['use_jquery_modal']
                );
                ?>
            </td>
            <?php
        }
        ?>
        <td valign="top" style="text-align:center;">
            <input type="checkbox" name="published<?php echo $thismatch->id; ?>" id="cbp<?php echo $thismatch->id; ?>"
                   value="<?php echo !empty($thismatch->published) ? 1 : 0; ?>"
                   <?php echo !empty($thismatch->published) ? 'checked="checked"' : ''; ?>
                   onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;this.value=this.checked?1:0;"/>
        </td>
        <?php
    }
    ?>
</tr>
