<?php
/**
 * SportsManagement bootstrap results edit row.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

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
        'oldlayout' => 'form_bootstrap',
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
<div class="row-fluid">
    <div class="<?php echo $this->divclass; ?>">
        <input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo (int) $thismatch->id; ?>"/>
    </div>

    <div class="<?php echo $this->divclass; ?>">
        <?php
        $url = $editMatchRoute('edit', (int) $team1->projectteamid);
        echo sportsmanagementHelperHtml::getBootstrapModalImage(
            'edit' . $thismatch->id,
            'administrator/components/com_sportsmanagement/assets/images/edit.png',
            Text::_('COM_SPORTSMANAGEMENT_EDIT_MATCH_DETAILS_BACKEND'),
            '20',
            $url
        );
        ?>
    </div>

    <div class="<?php echo $this->divclass; ?>">
        <?php
        $append = ' class="inputbox" size="1" onchange="document.getElementById(\'cb' . $i . '\').checked=true;" style="font-size:9px;" ';
        echo HTMLHelper::_(
            'select.genericlist',
            $this->roundsoption,
            'round_id' . $thismatch->id,
            $append,
            'value',
            'text',
            $thismatch->round_id
        );
        ?>
    </div>

    <div class="<?php echo $this->divclass; ?>">
        <input type="text" style="font-size:9px;" class="inputbox" size="3"
               name="match_number<?php echo $thismatch->id; ?>"
               value="<?php echo htmlspecialchars((string) ($thismatch->match_number ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
               onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;"/>
    </div>

    <div class="<?php echo $this->divclass; ?>">
        <?php
        $calendarAttributes = [
            'class' => 'form-control',
            'onChange' => "document.getElementById('cb{$i}').checked=true",
            'showTime' => false,
            'todayBtn' => true,
            'weekNumbers' => false,
            'fillTable' => true,
            'singleHeader' => true,
        ];
        echo HTMLHelper::_(
            'calendar',
            sportsmanagementHelper::convertDate($datum, 1),
            'match_date' . $thismatch->id,
            'match_date' . $thismatch->id,
            '%d-%m-%Y',
            $calendarAttributes
        );
        ?>
    </div>

    <div class="<?php echo $this->divclass; ?>">
        <input type="text" style="font-size:9px;" size="3" name="match_time<?php echo $thismatch->id; ?>"
               value="<?php echo htmlspecialchars(substr($uhrzeit, 0, 5), ENT_QUOTES, 'UTF-8'); ?>"
               class="inputbox" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;"/>
    </div>

    <div class="<?php echo $this->divclass; ?>">
        <?php
        $url = $editMatchRoute('editlineup', (int) $team1->projectteamid);
        echo sportsmanagementHelperHtml::getBootstrapModalImage(
            'home_lineup' . $team1->projectteamid,
            'administrator/components/com_sportsmanagement/assets/images/players_add.png',
            Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EDIT_LINEUP_HOME'),
            '20',
            $url
        );

        $teamAppend = ' class="inputbox" size="1" onchange="document.getElementById(\'cb' . $i . '\').checked=true;" style="font-size:9px;" ';
        if (!$userIsTeamAdmin && empty($match->allowed)) {
            $teamAppend .= ' disabled="disabled"';
        }
        echo HTMLHelper::_(
            'select.genericlist',
            $teamsoptions,
            'projectteam1_id' . $thismatch->id,
            $teamAppend,
            'value',
            'text',
            (int) $team1->projectteamid
        );
        ?>
    </div>

    <div class="<?php echo $this->divclass; ?>">
        <?php
        echo HTMLHelper::_(
            'select.genericlist',
            $teamsoptions,
            'projectteam2_id' . $thismatch->id,
            $teamAppend,
            'value',
            'text',
            (int) $team2->projectteamid
        );
        $url = $editMatchRoute('editlineup', (int) $team2->projectteamid);
        echo sportsmanagementHelperHtml::getBootstrapModalImage(
            'away_lineup' . $team2->projectteamid,
            'administrator/components/com_sportsmanagement/assets/images/players_add.png',
            Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EDIT_LINEUP_AWAY'),
            '20',
            $url
        );
        ?>
    </div>

    <div class="<?php echo $this->divclass; ?>"></div>

    <div class="<?php echo $this->divclass; ?>">
        <?php
        $url = $editMatchRoute('editevents', (int) $team1->projectteamid);
        echo sportsmanagementHelperHtml::getBootstrapModalImage(
            'edit_events' . $thismatch->id,
            'administrator/components/com_sportsmanagement/assets/images/events.png',
            Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EVENTS_BACKEND'),
            '20',
            $url
        );

        $url = $editMatchRoute('editstats', (int) $team1->projectteamid);
        echo sportsmanagementHelperHtml::getBootstrapModalImage(
            'edit_statistics' . $thismatch->id,
            'administrator/components/com_sportsmanagement/assets/images/calc16.png',
            Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_STATISTICS_BACKEND'),
            '20',
            $url
        );

        $url = $editMatchRoute('editreferees', (int) $team1->projectteamid);
        echo sportsmanagementHelperHtml::getBootstrapModalImage(
            'editreferees' . $thismatch->id,
            'administrator/components/com_sportsmanagement/assets/images/players_add.png',
            Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_REFEREE_BACKEND'),
            '20',
            $url
        );
        ?>
    </div>
</div>
