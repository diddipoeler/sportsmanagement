<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       edit_singlematchbillard.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$matchId = (int) $this->match->id;
$startersHome = $this->lists['homeplayer'] ?? [];
$startersAway = $this->lists['awayplayer'] ?? [];

/** Ensure the five default Golf/Billard single matches exist. */
for ($matchNumber = 1; $matchNumber <= 5; $matchNumber++) :
    $singleMatchExists = $this->model->getSingleMatchData($matchId, $matchNumber);
    ?>
    <div class="row">
        <?php if ($singleMatchExists) : ?>
            <div class="text-bg-primary p-3">Spiel <?php echo $matchNumber; ?> vorhanden</div>
        <?php else : ?>
            <div class="text-bg-danger p-3">Spiel <?php echo $matchNumber; ?> nicht vorhanden</div>
            <?php
            foreach ($startersHome as $homePlayer) {
                if ((int) ($homePlayer->trikot_number ?? 0) !== $matchNumber) {
                    continue;
                }

                foreach ($startersAway as $awayPlayer) {
                    if ((int) ($awayPlayer->trikot_number ?? 0) !== $matchNumber) {
                        continue;
                    }

                    $created = $this->model->insertSingleMatchData(
                        $matchId,
                        $matchNumber,
                        (int) $homePlayer->teamplayer_id,
                        (int) $awayPlayer->teamplayer_id,
                        (int) $homePlayer->projectteam_id,
                        (int) $awayPlayer->projectteam_id
                    );
                    ?>
                    <div class="<?php echo $created ? 'text-bg-success' : 'text-bg-danger'; ?> p-3">
                        <?php echo $created
                            ? 'Spiel ' . $matchNumber . ' angelegt'
                            : 'Fehler beim Anlegen des Spiels ' . $matchNumber; ?>
                    </div>
                    <?php
                }
            }
            ?>
        <?php endif; ?>
    </div>
<?php endfor; ?>

<?php
$this->singlematches = $this->model->getSingleMatchDatas($matchId);
$this->pagination->total = count($this->singlematches);

$canChange = Factory::getApplication()->getIdentity()->authorise('core.edit.state', 'com_sportsmanagement');
$matchTypeOptions = [
    HTMLHelper::_('select.option', 'SINGLE', Text::_('COM_SPORTSMANAGEMENT_PERSON_SINGLE')),
    HTMLHelper::_('select.option', 'DOUBLE', Text::_('COM_SPORTSMANAGEMENT_PERSON_DOUBLE')),
];
$matchResultTypes = $this->lists['match_result_type'] ?? [];

$renderPlayerSelect = static function (
    array $players,
    string $name,
    int $selected,
    string $rowCheckbox,
    string $className
): string {
    $style = $selected === 0 ? ' style="background-color:#bbffff"' : '';
    $attributes = 'class="inputbox ' . $className . '" size="1"'
        . $style
        . ' data-row-checkbox="' . $rowCheckbox . '"';

    return HTMLHelper::_(
        'select.genericlist',
        $players,
        $name,
        $attributes,
        'value',
        'text',
        $selected
    );
};

$roundName = (string) ($this->roundws->name ?? '');
$projectName = (string) ($this->projectws->name ?? '');
$matchDate = trim((string) ($this->match->match_date ?? ''));

if ($matchDate === '') {
    $matchDate = trim(
        (string) ($this->roundws->round_date_first ?? '')
        . ' '
        . (string) ($this->projectws->start_time ?? '')
    );
}
?>
<div class="table-responsive" id="editcell">
    <fieldset class="adminform">
        <legend>
            <?php echo Text::sprintf(
                'COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TITLE2',
                '<i>' . $escape($roundName) . '</i>',
                '<i>' . $escape($projectName) . '</i>'
            ); ?>
        </legend>

        <form action="<?php echo $escape($this->request_url); ?>" method="post" name="adminForm" id="adminForm">
            <fieldset>
                <div class="fltlft">
                    <button type="button" data-singlematch-submit-task="editmatch.applyshortsinglematch">
                        <?php echo Text::_('JSAVE'); ?>
                    </button>
                    <button
                        type="button"
                        data-singlematch-submit-task="editmatch.saveshortsinglematch"
                        data-close-before-submit="1"
                    >
                        <?php echo Text::_('JSAVEANDCLOSE'); ?>
                    </button>
                    <button type="button" data-singlematch-submit-task="editmatch.deletesinglematch">
                        <?php echo Text::_('JACTION_DELETE'); ?>
                    </button>
                    <button type="button" data-singlematch-submit-task="editmatch.cancel">
                        <?php echo Text::_('JCANCEL'); ?>
                    </button>
                </div>
            </fieldset>

            <table class="table table-striped" id="<?php echo $escape($this->view); ?>list">
                <thead>
                <tr>
                    <th><?php echo count($this->singlematches) . '/' . (int) $this->pagination->total; ?></th>
                    <th><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                    <th class="title"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MATCHNR'); ?></th>
                    <th class="title"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SINGLE_MATCH_TYPE'); ?></th>
                    <th class="title"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_HOME_TEAM_PLAYER'); ?></th>
                    <th class="title"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_AWAY_TEAM_PLAYER'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RESULT'); ?></th>
                    <?php if (!empty($this->projectws->allow_add_time)) : ?>
                        <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RESULT_TYPE'); ?></th>
                    <?php endif; ?>
                    <th><?php echo Text::_('JSTATUS'); ?></th>
                    <th class="title">
                        <?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'mc.id', $this->sortDirection, $this->sortColumn); ?>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($this->singlematches as $rowIndex => $item) : ?>
                    <?php
                    $itemId = (int) $item->id;
                    $checked = HTMLHelper::_('grid.checkedout', $item, $rowIndex, 'id');
                    $rowStyle = !empty($item->cancel)
                        ? 'text-align:center;background-color:#FF9999;'
                        : 'text-align:center;';
                    $rowCheckbox = 'cb' . (int) $rowIndex;
                    $isDouble = (string) ($item->match_type ?? '') === 'DOUBLE';
                    $isAltDecision = (int) ($item->alt_decision ?? 0) === 1;
                    $resultClass = 'inputbox' . ($isAltDecision ? ' subsequentdecision' : '');
                    $resultTitle = $isAltDecision
                        ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SUB_DECISION')
                        : '';
                    $team1ResultSplit = $item->team1_result_split ?? null;
                    $team2ResultSplit = $item->team2_result_split ?? null;
                    $partResults1 = $team1ResultSplit !== null
                        ? explode(';', (string) $team1ResultSplit)
                        : [];
                    $partResults2 = $team2ResultSplit !== null
                        ? explode(';', (string) $team2ResultSplit)
                        : [];
                    ?>
                    <tr class="row<?php echo (int) $rowIndex % 2; ?>">
                        <td style="<?php echo $rowStyle; ?>"></td>
                        <td class="text-center"><?php echo $checked; ?></td>
                        <td class="center">
                            <input
                                type="text"
                                name="match_number<?php echo $itemId; ?>"
                                value="<?php echo $escape($item->match_number ?? ''); ?>"
                                size="6"
                                tabindex="1"
                                class="inputbox"
                                data-row-checkbox="<?php echo $rowCheckbox; ?>"
                            >
                        </td>
                        <td class="text-center">
                            <?php
                            echo HTMLHelper::_(
                                'select.genericlist',
                                $matchTypeOptions,
                                'match_type' . $itemId,
                                'class="inputbox" data-row-checkbox="' . $rowCheckbox . '"',
                                'value',
                                'text',
                                (string) ($item->match_type ?? 'SINGLE')
                            );
                            ?>
                        </td>
                        <td>
                            <?php if (!$isDouble) : ?>
                                <?php echo $renderPlayerSelect(
                                    $startersHome,
                                    'teamplayer1_id' . $itemId,
                                    (int) ($item->teamplayer1_id ?? 0),
                                    $rowCheckbox,
                                    'select-hometeam'
                                ); ?>
                            <?php else : ?>
                                <?php echo $renderPlayerSelect(
                                    $startersHome,
                                    'double_team1_player1' . $itemId,
                                    (int) ($item->double_team1_player1 ?? 0),
                                    $rowCheckbox,
                                    'select-hometeam'
                                ); ?>
                                <br>
                                <?php echo $renderPlayerSelect(
                                    $startersHome,
                                    'double_team1_player2' . $itemId,
                                    (int) ($item->double_team1_player2 ?? 0),
                                    $rowCheckbox,
                                    'select-hometeam'
                                ); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$isDouble) : ?>
                                <?php echo $renderPlayerSelect(
                                    $startersAway,
                                    'teamplayer2_id' . $itemId,
                                    (int) ($item->teamplayer2_id ?? 0),
                                    $rowCheckbox,
                                    'select-awayteam'
                                ); ?>
                            <?php else : ?>
                                <?php echo $renderPlayerSelect(
                                    $startersAway,
                                    'double_team2_player1' . $itemId,
                                    (int) ($item->double_team2_player1 ?? 0),
                                    $rowCheckbox,
                                    'select-awayteam'
                                ); ?>
                                <br>
                                <?php echo $renderPlayerSelect(
                                    $startersAway,
                                    'double_team2_player2' . $itemId,
                                    (int) ($item->double_team2_player2 ?? 0),
                                    $rowCheckbox,
                                    'select-awayteam'
                                ); ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <input
                                type="text"
                                name="team1_result<?php echo $itemId; ?>"
                                value="<?php echo $escape($item->team1_result ?? ''); ?>"
                                size="2"
                                tabindex="4"
                                class="<?php echo $resultClass; ?>"
                                <?php echo $resultTitle !== '' ? 'title="' . $escape($resultTitle) . '"' : ''; ?>
                                data-row-checkbox="<?php echo $rowCheckbox; ?>"
                            >
                            :
                            <input
                                type="text"
                                name="team2_result<?php echo $itemId; ?>"
                                value="<?php echo $escape($item->team2_result ?? ''); ?>"
                                size="2"
                                tabindex="4"
                                class="<?php echo $resultClass; ?>"
                                <?php echo $resultTitle !== '' ? 'title="' . $escape($resultTitle) . '"' : ''; ?>
                                data-row-checkbox="<?php echo $rowCheckbox; ?>"
                            >

                            <table>
                                <tbody>
                                <?php for ($part = 0; $part < (int) ($this->projectws->game_parts ?? 0); $part++) : ?>
                                    <tr>
                                        <td>
                                            <?php echo $part + 1; ?>.:
                                            <input
                                                type="text"
                                                name="team1_result_split<?php echo $itemId; ?>[]"
                                                value="<?php echo $escape($partResults1[$part] ?? ''); ?>"
                                                size="3"
                                                tabindex="1"
                                                class="inputbox"
                                                style="font-size:9px;"
                                                data-row-checkbox="<?php echo $rowCheckbox; ?>"
                                            >
                                            <input
                                                type="text"
                                                name="team2_result_split<?php echo $itemId; ?>[]"
                                                value="<?php echo $escape($partResults2[$part] ?? ''); ?>"
                                                size="3"
                                                tabindex="1"
                                                class="inputbox"
                                                style="font-size:9px;"
                                                data-row-checkbox="<?php echo $rowCheckbox; ?>"
                                            >
                                        </td>
                                    </tr>
                                <?php endfor; ?>
                                </tbody>
                            </table>

                            <?php if (!empty($this->projectws->allow_add_time)) : ?>
                                OT:
                                <input
                                    type="text"
                                    name="team1_result_ot<?php echo $itemId; ?>"
                                    value="<?php echo $escape($item->team1_result_ot ?? ''); ?>"
                                    size="3"
                                    tabindex="1"
                                    class="inputbox"
                                    style="font-size:9px;"
                                    data-row-checkbox="<?php echo $rowCheckbox; ?>"
                                >
                                :
                                <input
                                    type="text"
                                    name="team2_result_ot<?php echo $itemId; ?>"
                                    value="<?php echo $escape($item->team2_result_ot ?? ''); ?>"
                                    size="3"
                                    tabindex="1"
                                    class="inputbox"
                                    style="font-size:9px;"
                                    data-row-checkbox="<?php echo $rowCheckbox; ?>"
                                >
                                <br>
                                SO:
                                <input
                                    type="text"
                                    name="team1_result_so<?php echo $itemId; ?>"
                                    value="<?php echo $escape($item->team1_result_so ?? ''); ?>"
                                    size="3"
                                    tabindex="1"
                                    class="inputbox"
                                    style="font-size:9px;"
                                    data-row-checkbox="<?php echo $rowCheckbox; ?>"
                                >
                                :
                                <input
                                    type="text"
                                    name="team2_result_so<?php echo $itemId; ?>"
                                    value="<?php echo $escape($item->team2_result_so ?? ''); ?>"
                                    size="3"
                                    tabindex="1"
                                    class="inputbox"
                                    style="font-size:9px;"
                                    data-row-checkbox="<?php echo $rowCheckbox; ?>"
                                >
                            <?php endif; ?>
                        </td>

                        <?php if (!empty($this->projectws->allow_add_time)) : ?>
                            <td>
                                <?php
                                echo HTMLHelper::_(
                                    'select.genericlist',
                                    $matchResultTypes,
                                    'match_result_type' . $itemId,
                                    'class="inputbox" size="1" data-row-checkbox="' . $rowCheckbox . '"',
                                    'value',
                                    'text',
                                    (int) ($item->match_result_type ?? 0)
                                );
                                ?>
                            </td>
                        <?php endif; ?>

                        <td class="center">
                            <div class="btn-group">
                                <?php
                                echo HTMLHelper::_(
                                    'jgrid.published',
                                    (int) ($item->published ?? 0),
                                    $rowIndex,
                                    'matches.',
                                    $canChange,
                                    'cb'
                                );
                                ?>
                                <?php if ($canChange) : ?>
                                    <?php
                                    HTMLHelper::_(
                                        'actionsdropdown.' . ((int) ($item->published ?? 0) === 2 ? 'un' : '') . 'archive',
                                        $rowCheckbox,
                                        'matches'
                                    );
                                    HTMLHelper::_(
                                        'actionsdropdown.' . ((int) ($item->published ?? 0) === -2 ? 'un' : '') . 'trash',
                                        $rowCheckbox,
                                        'matches'
                                    );
                                    echo HTMLHelper::_('actionsdropdown.render', $this->escape((string) $itemId));
                                    ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-center"><?php echo $itemId; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <input type="hidden" name="option" value="com_sportsmanagement">
            <input type="hidden" name="cfg_which_database" value="<?php echo Factory::getApplication()->getInput()->getInt('cfg_which_database', 0); ?>">
            <input type="hidden" name="match_date" value="<?php echo $escape($matchDate); ?>">
            <input type="hidden" name="act" id="short_act" value="">
            <input type="hidden" name="boxchecked" value="0">
            <input type="hidden" name="search_mode" value="<?php echo $escape($this->lists['search_mode'] ?? ''); ?>">
            <input type="hidden" name="filter_order" value="<?php echo $escape($this->sortColumn); ?>">
            <input type="hidden" name="filter_order_Dir" value="<?php echo $escape($this->sortDirection); ?>">
            <input type="hidden" name="rid" value="<?php echo (int) ($this->match->round_id ?? 0); ?>">
            <input type="hidden" name="project_id" value="<?php echo (int) $this->project->id; ?>">
            <input type="hidden" name="close" id="close" value="0">
            <input type="hidden" name="match_id" value="<?php echo $matchId; ?>">
            <input type="hidden" name="projectteam1_id" value="<?php echo (int) $this->match->projectteam1_id; ?>">
            <input type="hidden" name="projectteam2_id" value="<?php echo (int) $this->match->projectteam2_id; ?>">
            <input type="hidden" name="task" id="task" value="">
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </fieldset>
</div>
