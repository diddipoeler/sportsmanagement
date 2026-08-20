<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<form action="<?php echo $this->escape($this->request_url); ?>" method="post" id="adminForm" name="adminForm">
    <div class="row g-2 align-items-end mb-3">
        <div class="col-md-4">
            <label class="form-label" for="filter_search"><?php echo Text::_('JSEARCH_FILTER_LABEL'); ?></label>
            <input type="search" class="form-control" name="filter_search" id="filter_search"
                   value="<?php echo $this->escape((string) $this->state->get('filter.search')); ?>"
                   placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>">
        </div>
        <div class="col-md-2">
            <?php echo HTMLHelper::_(
                'select.genericlist',
                HTMLHelper::_('jgrid.publishedOptions'),
                'filter_state',
                'class="form-select" onchange="this.form.submit()"',
                'value',
                'text',
                $this->state->get('filter.state')
            ); ?>
        </div>
        <div class="col-md-2">
            <?php echo HTMLHelper::_(
                'select.genericlist',
                $this->lists['nation'],
                'filter_search_nation',
                'class="form-select" onchange="this.form.submit()"',
                'value',
                'text',
                $this->state->get('filter.search_nation')
            ); ?>
        </div>
        <div class="col-md-2">
            <?php echo HTMLHelper::_(
                'select.genericlist',
                $this->lists['agegroup'],
                'filter_search_agegroup',
                'class="form-select" onchange="this.form.submit()"',
                'value',
                'text',
                $this->state->get('filter.search_agegroup')
            ); ?>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('filter_search').value='';this.form.submit();">
                <?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
            </button>
        </div>
    </div>

    <?php if ($this->assign) : ?>
        <div class="mb-3 d-flex gap-2">
            <button type="button" class="btn btn-success" onclick="Joomla.submitform('seasons.applypersons', this.form);">
                <?php echo Text::_('JAPPLY'); ?>
            </button>
            <button type="button" class="btn btn-secondary" onclick="Joomla.submitform('seasons.cancel', this.form);">
                <?php echo Text::_('JCANCEL'); ?>
            </button>
            <?php echo $this->pagination->getLimitBox(); ?>
        </div>
    <?php endif; ?>

    <?php echo $this->loadTemplate('data'); ?>

    <input type="hidden" name="search_mode" value="<?php echo $this->escape($this->lists['search_mode']); ?>">
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="season_id" value="<?php echo (int) $this->season_id; ?>">
    <input type="hidden" name="project_id" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="team_id" value="<?php echo (int) $this->team_id; ?>">
    <input type="hidden" name="persontype" value="<?php echo (int) $this->persontype; ?>">
    <input type="hidden" name="whichview" value="<?php echo $this->escape($this->whichview); ?>">
    <input type="hidden" name="assignclub" value="<?php echo (int) $this->assignclub; ?>">
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($this->sortColumn); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->sortDirection); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
