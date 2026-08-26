<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$escape = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$hasEditable = false;
foreach ($this->matches as $match) {
    if (!empty($match->editable)) {
        $hasEditable = true;
        break;
    }
}
$mode = (int) ($this->entryProject->mode ?? 0);
$jokerEnabled = !empty($this->entryProject->joker) && $mode === 0;
$jokerLimit = max(0, (int) ($this->entryProject->joker_limit ?? 0));
?>
<form method="post" action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>" class="mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label" for="prediction-entry-project"><?php echo Text::_('COM_SPORTSMANAGEMENT_ALL_PROJECTS'); ?></label>
            <select class="form-select" id="prediction-entry-project" name="pj" onchange="this.form.submit();">
                <?php foreach ($this->projectOptions as $option) : ?>
                    <option value="<?php echo (int) $option->value; ?>"<?php echo (int) $option->value === (int) $this->projectID ? ' selected' : ''; ?>><?php echo $escape($option->text); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="prediction-entry-round"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_ROUND'); ?></label>
            <select class="form-select" id="prediction-entry-round" name="r" onchange="this.form.submit();">
                <?php foreach ($this->roundOptions as $option) : ?>
                    <option value="<?php echo (int) $option->value; ?>"<?php echo (int) $option->value === (int) $this->roundID ? ' selected' : ''; ?>><?php echo $escape($option->text); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <input type="hidden" name="option" value="com_sportsmanagement">
    <input type="hidden" name="task" value="predictionentry.selectprojectround">
    <input type="hidden" name="prediction_id" value="<?php echo (int) $this->predictionGameID; ?>">
    <input type="hidden" name="uid" value="<?php echo (int) $this->predictionMemberID; ?>">
    <input type="hidden" name="pggroup" value="<?php echo (int) $this->predictionGroupID; ?>">
    <input type="hidden" name="cfg_which_database" value="<?php echo (int) $this->databaseSelector; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php if (!$this->entryProject || !$this->projectID || !$this->roundID) : ?>
    <div class="alert alert-warning"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NO_PROJECT'); ?></div>
    <?php return; ?>
<?php endif; ?>

<form method="post" action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>" id="prediction-entry-tip-form">
    <?php if (!empty($this->entryProject->use_goals) || !empty($this->entryProject->use_penalties) || !empty($this->entryProject->use_cards)) : ?>
        <fieldset class="mb-4">
            <legend class="h5"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_ROUND'); ?></legend>
            <div class="row g-2">
                <?php if (!empty($this->entryProject->use_goals)) : ?>
                    <div class="col-sm-4">
                        <label class="form-label" for="entry-goals"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_USE_GOALS'); ?></label>
                        <input class="form-control" type="number" min="0" max="999" id="entry-goals" name="goals" value="<?php echo (int) ($this->roundExtras->goals ?? 0); ?>"<?php echo $this->roundExtrasEditable ? '' : ' disabled'; ?>>
                    </div>
                <?php endif; ?>
                <?php if (!empty($this->entryProject->use_penalties)) : ?>
                    <div class="col-sm-4">
                        <label class="form-label" for="entry-penalties"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_USE_PENALTIES'); ?></label>
                        <input class="form-control" type="number" min="0" max="999" id="entry-penalties" name="penalties" value="<?php echo (int) ($this->roundExtras->penalties ?? 0); ?>"<?php echo $this->roundExtrasEditable ? '' : ' disabled'; ?>>
                    </div>
                <?php endif; ?>
                <?php if (!empty($this->entryProject->use_cards)) : ?>
                    <div class="col-sm-4">
                        <label class="form-label" for="entry-yellow-cards"><?php echo Text::_('COM_SPORTSMANAGEMENT_E_YELLOW_CARD'); ?></label>
                        <input class="form-control" type="number" min="0" max="999" id="entry-yellow-cards" name="yellow_cards" value="<?php echo (int) ($this->roundExtras->yellow_cards ?? 0); ?>"<?php echo $this->roundExtrasEditable ? '' : ' disabled'; ?>>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label" for="entry-yellow-red-cards"><?php echo Text::_('COM_SPORTSMANAGEMENT_E_YELLOW-RED_CARD'); ?></label>
                        <input class="form-control" type="number" min="0" max="999" id="entry-yellow-red-cards" name="yellow_red_cards" value="<?php echo (int) ($this->roundExtras->yellow_red_cards ?? 0); ?>"<?php echo $this->roundExtrasEditable ? '' : ' disabled'; ?>>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label" for="entry-red-cards"><?php echo Text::_('COM_SPORTSMANAGEMENT_E_RED_CARD'); ?></label>
                        <input class="form-control" type="number" min="0" max="999" id="entry-red-cards" name="red_cards" value="<?php echo (int) ($this->roundExtras->red_cards ?? 0); ?>"<?php echo $this->roundExtrasEditable ? '' : ' disabled'; ?>>
                    </div>
                <?php endif; ?>
            </div>
        </fieldset>
    <?php endif; ?>

    <?php if ($jokerEnabled) : ?>
        <div class="alert alert-secondary py-2">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_JOKER'); ?>:
            <strong><?php echo (int) $this->jokerCount; ?><?php echo $jokerLimit > 0 ? '/' . $jokerLimit : ''; ?></strong>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="<?php echo $escape($this->config['table_class'] ?? 'table'); ?>">
            <thead>
            <tr>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_DATE_TIME'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_HOME_TEAM'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_AWAY_TEAM'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_PREDICTIONS'); ?></th>
                <?php if ($jokerEnabled) : ?><th><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_JOKER'); ?></th><?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php if (!$this->matches) : ?>
                <tr><td colspan="<?php echo $jokerEnabled ? 5 : 4; ?>" class="text-center"><strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NO_POSSIBLE_PREDICTIONS'); ?></strong></td></tr>
            <?php endif; ?>
            <?php foreach ($this->matches as $match) : ?>
                <tr>
                    <td>
                        <?php echo $escape($match->display_match_date); ?>
                        <?php if (!$match->editable && !empty($match->deadline)) : ?>
                            <div class="small text-muted"><?php echo $escape($match->deadline); ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($match->home_logo)) : ?>
                            <?php echo HTMLHelper::image($match->home_logo, $match->home_display_name, ['height' => (int) ($this->config['club_logo_height'] ?? 20), 'class' => 'me-1']); ?>
                        <?php elseif (($this->config['show_logo_small'] ?? '') === 'country_flag' && !empty($match->home_country)) : ?>
                            <span class="badge text-bg-light"><?php echo $escape($match->home_country); ?></span>
                        <?php endif; ?>
                        <?php echo $escape($match->home_display_name); ?>
                    </td>
                    <td>
                        <?php if (!empty($match->away_logo)) : ?>
                            <?php echo HTMLHelper::image($match->away_logo, $match->away_display_name, ['height' => (int) ($this->config['club_logo_height'] ?? 20), 'class' => 'me-1']); ?>
                        <?php elseif (($this->config['show_logo_small'] ?? '') === 'country_flag' && !empty($match->away_country)) : ?>
                            <span class="badge text-bg-light"><?php echo $escape($match->away_country); ?></span>
                        <?php endif; ?>
                        <?php echo $escape($match->away_display_name); ?>
                    </td>
                    <td>
                        <?php if ($mode === 0) : ?>
                            <div class="d-flex align-items-center gap-1">
                                <input class="form-control text-center" style="max-width:5rem" type="number" min="0" max="99" name="homes[<?php echo (int) $match->id; ?>]" value="<?php echo $match->tipp_home === null ? '' : (int) $match->tipp_home; ?>"<?php echo $match->editable ? '' : ' disabled'; ?>>
                                <strong><?php echo $escape($this->config['seperator'] ?? ':'); ?></strong>
                                <input class="form-control text-center" style="max-width:5rem" type="number" min="0" max="99" name="aways[<?php echo (int) $match->id; ?>]" value="<?php echo $match->tipp_away === null ? '' : (int) $match->tipp_away; ?>"<?php echo $match->editable ? '' : ' disabled'; ?>>
                            </div>
                        <?php else : ?>
                            <select class="form-select" name="tipps[<?php echo (int) $match->id; ?>]"<?php echo $match->editable ? '' : ' disabled'; ?>>
                                <option value=""><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NO_TIPP'); ?></option>
                                <option value="1"<?php echo (string) $match->tipp === '1' ? ' selected' : ''; ?>><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_HOME_WIN'); ?></option>
                                <option value="0"<?php echo (string) $match->tipp === '0' ? ' selected' : ''; ?>><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_DRAW'); ?></option>
                                <option value="2"<?php echo (string) $match->tipp === '2' ? ' selected' : ''; ?>><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_AWAY_WIN'); ?></option>
                            </select>
                        <?php endif; ?>
                        <?php if (!empty($this->config['show_tipp_tendence']) && (int) ($match->tendency['total'] ?? 0) > 0) : ?>
                            <?php $total = (int) $match->tendency['total']; ?>
                            <div class="small mt-2">
                                <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_HOME_WIN', round(100 * (int) $match->tendency['home'] / $total, 2), (int) $match->tendency['home']); ?><br>
                                <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_DRAW', round(100 * (int) $match->tendency['draw'] / $total, 2), (int) $match->tendency['draw']); ?><br>
                                <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_AWAY_WIN', round(100 * (int) $match->tendency['away'] / $total, 2), (int) $match->tendency['away']); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <?php if ($jokerEnabled) : ?>
                        <td>
                            <input class="form-check-input" type="checkbox" name="jokers[<?php echo (int) $match->id; ?>]" value="1"<?php echo !empty($match->joker) ? ' checked' : ''; ?><?php echo $match->editable ? '' : ' disabled'; ?>>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($hasEditable) : ?>
        <button type="submit" class="btn btn-primary" name="task" value="predictionentry.addtipp"><?php echo Text::_('JSAVE'); ?></button>
    <?php endif; ?>

    <input type="hidden" name="option" value="com_sportsmanagement">
    <input type="hidden" name="view" value="predictionentry">
    <input type="hidden" name="prediction_id" value="<?php echo (int) $this->predictionGameID; ?>">
    <input type="hidden" name="member_id" value="<?php echo (int) $this->predictionMemberID; ?>">
    <input type="hidden" name="uid" value="<?php echo (int) $this->predictionMemberID; ?>">
    <input type="hidden" name="pj" value="<?php echo (int) $this->projectID; ?>">
    <input type="hidden" name="r" value="<?php echo (int) $this->roundID; ?>">
    <input type="hidden" name="pggroup" value="<?php echo (int) $this->predictionGroupID; ?>">
    <input type="hidden" name="cfg_which_database" value="<?php echo (int) $this->databaseSelector; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
