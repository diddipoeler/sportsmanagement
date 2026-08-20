<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<form action="<?php echo $this->escape($this->request_url); ?>" method="post" id="adminForm" name="adminForm">
    <div class="row g-2 align-items-end mb-3">
        <div class="col-md-5">
            <label class="form-label" for="filter_search"><?php echo Text::_('JSEARCH_FILTER_LABEL'); ?></label>
            <input type="search" class="form-control" name="filter_search" id="filter_search"
                   value="<?php echo $this->escape((string) $this->state->get('filter.search')); ?>"
                   placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>">
        </div>
        <div class="col-md-3">
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
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" class="btn btn-secondary"
                    onclick="document.getElementById('filter_search').value='';this.form.submit();">
                <?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
            </button>
        </div>
    </div>

    <?php echo $this->loadTemplate('data'); ?>

    <input type="hidden" name="project_team_id" value="<?php echo (int) $this->project_team_id; ?>">
    <input type="hidden" name="team_id" value="<?php echo (int) $this->team_id; ?>">
    <input type="hidden" name="season_team_id" value="<?php echo (int) $this->season_team_id; ?>">
    <input type="hidden" name="season_id" value="<?php echo (int) $this->season_id; ?>">
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="persontype" value="<?php echo (int) $this->_persontype; ?>">
    <input type="hidden" name="search_mode" value="<?php echo $this->escape($this->lists['search_mode']); ?>">
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($this->sortColumn); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->sortDirection); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
