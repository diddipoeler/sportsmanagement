<?php
/** Native ranking range and division navigation. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

$selectedDivision = $this->input->getInt('division', 0);
?>
<div class="ranking-navigation card card-body mb-3">
    <form action="index.php" method="get" class="row g-2 align-items-end">
        <input type="hidden" name="option" value="com_sportsmanagement">
        <input type="hidden" name="view" value="ranking">
        <input type="hidden" name="cfg_which_database" value="<?php echo $this->cfg_which_database; ?>">
        <input type="hidden" name="s" value="<?php echo $this->season_id; ?>">
        <input type="hidden" name="p" value="<?php echo (int) ($this->project->id ?? 0); ?>">

        <div class="col-12 col-md-3">
            <label class="form-label" for="ranking-from"><?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_FROM_MATCHDAY'); ?></label>
            <select class="form-select" id="ranking-from" name="from">
                <option value="0"><?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_FROM_MATCHDAY'); ?></option>
                <?php foreach ($this->roundsoption as $round) : ?>
                    <option value="<?php echo (int) ($round->value ?? 0); ?>"<?php echo (int) ($round->value ?? 0) === $this->from ? ' selected' : ''; ?>>
                        <?php echo $this->escape((string) ($round->text ?? '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12 col-md-3">
            <label class="form-label" for="ranking-to"><?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_TO_MATCHDAY'); ?></label>
            <select class="form-select" id="ranking-to" name="to">
                <option value="0"><?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_TO_MATCHDAY'); ?></option>
                <?php foreach ($this->roundsoption as $round) : ?>
                    <option value="<?php echo (int) ($round->value ?? 0); ?>"<?php echo (int) ($round->value ?? 0) === $this->to ? ' selected' : ''; ?>>
                        <?php echo $this->escape((string) ($round->text ?? '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label" for="ranking-type"><?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING'); ?></label>
            <select class="form-select" id="ranking-type" name="type">
                <?php foreach ($this->lists['type'] ?? [] as $option) : ?>
                    <option value="<?php echo (int) ($option->value ?? 0); ?>"<?php echo (int) ($option->value ?? 0) === $this->type ? ' selected' : ''; ?>>
                        <?php echo $this->escape((string) ($option->text ?? '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($this->divisions !== []) : ?>
            <div class="col-12 col-md-3">
                <label class="form-label" for="ranking-division"><?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_DIVISION'); ?></label>
                <select class="form-select" id="ranking-division" name="division">
                    <option value="0"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'); ?></option>
                    <?php foreach ($this->divisions as $division) : ?>
                        <option value="<?php echo (int) ($division->id ?? 0); ?>"<?php echo (int) ($division->id ?? 0) === $selectedDivision ? ' selected' : ''; ?>>
                            <?php echo $this->escape((string) ($division->name ?? '')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="col-12 col-md-auto">
            <button class="btn btn-primary" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
        </div>
    </form>
</div>
