<?php
/** Native Joomla 5/6 compact frontend results editor. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\NamePresentationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

if (!$this->project) {
    return;
}

$layout = $this->getLayout();
$divisionId = (int) ($this->division->id ?? $this->input->getInt('division', 0));
$mode = $this->input->getInt('mode', 0);
$order = $this->input->getInt('order', 0);
$gameParts = max(0, (int) ($this->project->game_parts ?? 0));
$useLegs = !empty($this->project->use_legs);
$allowAddTime = !empty($this->project->allow_add_time);
$nameFormat = (int) ($this->config['team_name_format'] ?? $this->config['team_names'] ?? 2);
$showMatchNumber = !empty($this->config['show_edit_match_number']);
$showMatchDate = !empty($this->config['show_edit_match_date']);
$showMatchTime = !empty($this->config['show_edit_match_time']);
$cancelUrl = Route::_('index.php?option=com_sportsmanagement&view=results'
    . '&cfg_which_database=' . $this->cfg_which_database
    . '&s=' . $this->season_id
    . '&p=' . (int) $this->project->id
    . '&r=' . $this->roundid
    . ($divisionId > 0 ? '&division=' . $divisionId : ''));
?>
<div class="<?php echo $this->escape($this->divclasscontainer); ?>" id="results-editor">
    <?php echo $this->loadTemplate('projectheading'); ?>

    <div class="<?php echo $this->escape($this->divclassrow); ?>">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h4 mb-0">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_ENTER_EDIT_RESULTS'); ?>
                <?php if ($this->roundcode !== '') : ?>
                    - <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_RESULTS_GAMEDAY_NB', $this->roundcode); ?>
                <?php endif; ?>
            </h2>
            <a class="btn btn-outline-secondary" href="<?php echo $this->escape($cancelUrl); ?>">
                <?php echo Text::_('JCANCEL'); ?>
            </a>
        </div>

        <?php if ($this->matches === []) : ?>
            <div class="alert alert-info"><?php echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_NO_MATCHES'); ?></div>
        <?php else : ?>
            <form
                action="<?php echo Route::_('index.php?option=com_sportsmanagement&task=results.saveshort'); ?>"
                method="post"
                id="results-edit-form"
            >
                <input type="hidden" name="option" value="com_sportsmanagement">
                <input type="hidden" name="task" value="results.saveshort">
                <input type="hidden" name="view" value="results">
                <input type="hidden" name="layout" value="<?php echo $this->escape($layout); ?>">
                <input type="hidden" name="cfg_which_database" value="<?php echo $this->cfg_which_database; ?>">
                <input type="hidden" name="s" value="<?php echo $this->season_id; ?>">
                <input type="hidden" name="p" value="<?php echo (int) $this->project->id; ?>">
                <input type="hidden" name="r" value="<?php echo $this->roundid; ?>">
                <input type="hidden" name="division" value="<?php echo $divisionId; ?>">
                <input type="hidden" name="mode" value="<?php echo $mode; ?>">
                <input type="hidden" name="order" value="<?php echo $order; ?>">
                <input type="hidden" name="use_legs" value="<?php echo $useLegs ? 1 : 0; ?>">

                <div class="table-responsive">
                    <table class="table table-striped align-middle" id="results-edit-table">
                        <thead>
                        <tr>
                            <th class="text-center" style="width:3rem">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="results-select-all"
                                    aria-label="<?php echo $this->escape(Text::_('JGLOBAL_CHECK_ALL')); ?>"
                                >
                            </th>
                            <?php if ($showMatchNumber) : ?>
                                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_MATCH_NUMBER'); ?></th>
                            <?php endif; ?>
                            <?php if ($showMatchDate) : ?>
                                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_DATE'); ?></th>
                            <?php endif; ?>
                            <?php if ($showMatchTime) : ?>
                                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_TIME'); ?></th>
                            <?php endif; ?>
                            <th class="text-end"><?php echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_HOME_TEAM'); ?></th>
                            <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_RESULT'); ?></th>
                            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_AWAY_TEAM'); ?></th>
                            <?php if ($gameParts > 0 || $useLegs) : ?>
                                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_EDIT_MATRIX_ROUNDS_PART_RESULT'); ?></th>
                            <?php endif; ?>
                            <?php if ($allowAddTime) : ?>
                                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_RESULT_TYPE'); ?></th>
                            <?php endif; ?>
                            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_ATTENDANCE'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->matches as $match) : ?>
                            <?php
                            $matchId = (int) ($match->id ?? 0);
                            $homeProjectTeamId = (int) ($match->projectteam1_id ?? 0);
                            $awayProjectTeamId = (int) ($match->projectteam2_id ?? 0);
                            $homeTeam = $this->teams[$homeProjectTeamId] ?? null;
                            $awayTeam = $this->teams[$awayProjectTeamId] ?? null;
                            if ($matchId <= 0 || !$homeTeam || !$awayTeam) {
                                continue;
                            }

                            $allowed = !empty($match->allowed);
                            $disabled = $allowed ? '' : ' disabled';
                            $dateValue = substr((string) ($match->match_date ?? ''), 0, 10);
                            $timeValue = substr((string) ($match->match_date ?? ''), 11, 5);
                            $homeSplits = explode(';', (string) ($match->team1_result_split ?? ''));
                            $awaySplits = explode(';', (string) ($match->team2_result_split ?? ''));
                            $partCount = max($gameParts, count($homeSplits), count($awaySplits));
                            ?>
                            <tr data-match-id="<?php echo $matchId; ?>"<?php echo $allowed ? '' : ' class="table-secondary"'; ?>>
                                <td class="text-center">
                                    <input
                                        class="form-check-input result-row-selector"
                                        type="checkbox"
                                        name="cid[]"
                                        value="<?php echo $matchId; ?>"
                                        aria-label="<?php echo $this->escape(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_SAVED', $matchId)); ?>"
                                        <?php echo $disabled; ?>
                                    >
                                </td>

                                <?php if ($showMatchNumber) : ?>
                                    <td>
                                        <input
                                            class="form-control form-control-sm result-edit-field"
                                            type="text"
                                            name="match_number<?php echo $matchId; ?>"
                                            value="<?php echo $this->escape((string) ($match->match_number ?? '')); ?>"
                                            <?php echo $disabled; ?>
                                        >
                                    </td>
                                <?php endif; ?>

                                <?php if ($showMatchDate) : ?>
                                    <td>
                                        <input
                                            class="form-control form-control-sm result-edit-field"
                                            type="date"
                                            name="match_date<?php echo $matchId; ?>"
                                            value="<?php echo $this->escape($dateValue); ?>"
                                            <?php echo $disabled; ?>
                                        >
                                    </td>
                                <?php endif; ?>

                                <?php if ($showMatchTime) : ?>
                                    <td>
                                        <input
                                            class="form-control form-control-sm result-edit-field"
                                            type="time"
                                            name="match_time<?php echo $matchId; ?>"
                                            value="<?php echo $this->escape($timeValue); ?>"
                                            <?php echo $disabled; ?>
                                        >
                                    </td>
                                <?php endif; ?>

                                <td class="text-end">
                                    <?php echo NamePresentationHelper::team($homeTeam, $nameFormat); ?>
                                </td>
                                <td class="text-center text-nowrap">
                                    <?php if (!$useLegs) : ?>
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <input
                                                class="form-control form-control-sm text-center result-edit-field"
                                                style="width:4.5rem"
                                                inputmode="decimal"
                                                name="team1_result<?php echo $matchId; ?>"
                                                value="<?php echo $this->escape((string) ($match->team1_result ?? '')); ?>"
                                                <?php echo $disabled; ?>
                                            >
                                            <span><?php echo $this->escape((string) ($this->config['seperator'] ?? ':')); ?></span>
                                            <input
                                                class="form-control form-control-sm text-center result-edit-field"
                                                style="width:4.5rem"
                                                inputmode="decimal"
                                                name="team2_result<?php echo $matchId; ?>"
                                                value="<?php echo $this->escape((string) ($match->team2_result ?? '')); ?>"
                                                <?php echo $disabled; ?>
                                            >
                                        </div>
                                    <?php else : ?>
                                        <span class="badge bg-secondary">
                                            <?php echo $this->escape((string) ($match->team1_result ?? '')); ?>
                                            <?php echo $this->escape((string) ($this->config['seperator'] ?? ':')); ?>
                                            <?php echo $this->escape((string) ($match->team2_result ?? '')); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo NamePresentationHelper::team($awayTeam, $nameFormat); ?></td>

                                <?php if ($gameParts > 0 || $useLegs) : ?>
                                    <td>
                                        <details>
                                            <summary><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_EDIT_MATRIX_ROUNDS_PART_RESULT'); ?></summary>
                                            <div class="mt-2 d-grid gap-1">
                                                <?php for ($part = 0; $part < $partCount; $part++) : ?>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <span class="small text-muted" style="width:2rem"><?php echo $part + 1; ?>.</span>
                                                        <input
                                                            class="form-control form-control-sm text-center result-edit-field"
                                                            style="width:4.5rem"
                                                            inputmode="decimal"
                                                            name="team1_result_split<?php echo $matchId; ?>[]"
                                                            value="<?php echo $this->escape((string) ($homeSplits[$part] ?? '')); ?>"
                                                            <?php echo $disabled; ?>
                                                        >
                                                        <span><?php echo $this->escape((string) ($this->config['seperator'] ?? ':')); ?></span>
                                                        <input
                                                            class="form-control form-control-sm text-center result-edit-field"
                                                            style="width:4.5rem"
                                                            inputmode="decimal"
                                                            name="team2_result_split<?php echo $matchId; ?>[]"
                                                            value="<?php echo $this->escape((string) ($awaySplits[$part] ?? '')); ?>"
                                                            <?php echo $disabled; ?>
                                                        >
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                        </details>
                                    </td>
                                <?php endif; ?>

                                <?php if ($allowAddTime) : ?>
                                    <td>
                                        <select
                                            class="form-select form-select-sm result-edit-field"
                                            name="match_result_type<?php echo $matchId; ?>"
                                            <?php echo $disabled; ?>
                                        >
                                            <option value="0"<?php echo (int) ($match->match_result_type ?? 0) === 0 ? ' selected' : ''; ?>>
                                                <?php echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_REGULAR_TIME'); ?>
                                            </option>
                                            <option value="1"<?php echo (int) ($match->match_result_type ?? 0) === 1 ? ' selected' : ''; ?>>
                                                <?php echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME'); ?>
                                            </option>
                                            <option value="2"<?php echo (int) ($match->match_result_type ?? 0) === 2 ? ' selected' : ''; ?>>
                                                <?php echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT'); ?>
                                            </option>
                                        </select>
                                        <div class="mt-2 d-flex gap-1">
                                            <input
                                                class="form-control form-control-sm text-center result-edit-field"
                                                style="width:4.5rem"
                                                name="team1_result_ot<?php echo $matchId; ?>"
                                                value="<?php echo $this->escape((string) ($match->team1_result_ot ?? '')); ?>"
                                                placeholder="OT"
                                                <?php echo $disabled; ?>
                                            >
                                            <input
                                                class="form-control form-control-sm text-center result-edit-field"
                                                style="width:4.5rem"
                                                name="team2_result_ot<?php echo $matchId; ?>"
                                                value="<?php echo $this->escape((string) ($match->team2_result_ot ?? '')); ?>"
                                                placeholder="OT"
                                                <?php echo $disabled; ?>
                                            >
                                        </div>
                                        <div class="mt-1 d-flex gap-1">
                                            <input
                                                class="form-control form-control-sm text-center result-edit-field"
                                                style="width:4.5rem"
                                                name="team1_result_so<?php echo $matchId; ?>"
                                                value="<?php echo $this->escape((string) ($match->team1_result_so ?? '')); ?>"
                                                placeholder="SO"
                                                <?php echo $disabled; ?>
                                            >
                                            <input
                                                class="form-control form-control-sm text-center result-edit-field"
                                                style="width:4.5rem"
                                                name="team2_result_so<?php echo $matchId; ?>"
                                                value="<?php echo $this->escape((string) ($match->team2_result_so ?? '')); ?>"
                                                placeholder="SO"
                                                <?php echo $disabled; ?>
                                            >
                                        </div>
                                    </td>
                                <?php endif; ?>

                                <td>
                                    <input
                                        class="form-control form-control-sm text-end result-edit-field"
                                        style="width:6rem"
                                        type="number"
                                        min="0"
                                        name="crowd<?php echo $matchId; ?>"
                                        value="<?php echo (int) ($match->crowd ?? 0); ?>"
                                        <?php echo $disabled; ?>
                                    >
                                </td>
                            </tr>

                            <?php foreach ([
                                'result_type',
                                'round_id',
                                'division_id',
                                'projectteam1_id',
                                'projectteam2_id',
                                'team1_single_matchpoint',
                                'team2_single_matchpoint',
                                'team1_single_sets',
                                'team2_single_sets',
                                'team1_single_games',
                                'team2_single_games',
                                'content_id',
                            ] as $hiddenField) : ?>
                                <input
                                    type="hidden"
                                    name="<?php echo $hiddenField . $matchId; ?>"
                                    value="<?php echo $this->escape((string) ($match->{$hiddenField} ?? '0')); ?>"
                                >
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                    <?php if ($this->pagination) : ?>
                        <div><?php echo $this->pagination->getPagesLinks(); ?></div>
                    <?php endif; ?>
                    <div class="d-flex gap-2 ms-auto">
                        <a class="btn btn-outline-secondary" href="<?php echo $this->escape($cancelUrl); ?>">
                            <?php echo Text::_('JCANCEL'); ?>
                        </a>
                        <button class="btn btn-primary" type="submit">
                            <?php echo Text::_('JSAVE'); ?>
                        </button>
                    </div>
                </div>

                <?php echo HTMLHelper::_('form.token'); ?>
            </form>
        <?php endif; ?>
    </div>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>

<script>
(() => {
    const form = document.getElementById('results-edit-form');
    if (!form) return;

    const selectAll = document.getElementById('results-select-all');
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            form.querySelectorAll('.result-row-selector:not(:disabled)').forEach((checkbox) => {
                checkbox.checked = selectAll.checked;
            });
        });
    }

    form.addEventListener('input', (event) => {
        const field = event.target.closest('.result-edit-field');
        if (!field) return;
        const row = field.closest('tr[data-match-id]');
        const selector = row ? row.querySelector('.result-row-selector:not(:disabled)') : null;
        if (selector) selector.checked = true;
    });

    form.addEventListener('change', (event) => {
        const field = event.target.closest('.result-edit-field');
        if (!field) return;
        const row = field.closest('tr[data-match-id]');
        const selector = row ? row.querySelector('.result-row-selector:not(:disabled)') : null;
        if (selector) selector.checked = true;
    });
})();
</script>
