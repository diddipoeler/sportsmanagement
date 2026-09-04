<?php
/**
 * Native Joomla 5/6 prediction games administrator layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.multiselect');

$predictionOptions = [
    HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME')),
];
$predictionOptions = array_merge($predictionOptions, $this->predictionOptions);
$statusOptions = [
    HTMLHelper::_('select.option', '', Text::_('JOPTION_SELECT_PUBLISHED')),
    HTMLHelper::_('select.option', '1', Text::_('JPUBLISHED')),
    HTMLHelper::_('select.option', '0', Text::_('JUNPUBLISHED')),
];
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=predictiongames'); ?>"
    method="post"
    id="adminForm"
    name="adminForm"
>
    <div class="row g-2 align-items-end mb-3">
        <div class="col-12 col-lg-4">
            <label class="form-label" for="filter-search">
                <?php echo Text::_('JSEARCH_FILTER'); ?>
            </label>
            <input
                type="search"
                class="form-control"
                id="filter-search"
                name="filter_search"
                value="<?php echo $this->escape((string) $this->state->get('filter.search', '')); ?>"
                placeholder="<?php echo $this->escape(Text::_('JSEARCH_FILTER')); ?>"
            >
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label" for="filter-prediction-id">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_PREDICTIONS'); ?>
            </label>
            <?php echo HTMLHelper::_(
                'select.genericlist',
                $predictionOptions,
                'filter_prediction_id',
                'id="filter-prediction-id" class="form-select" onchange="this.form.submit();"',
                'value',
                'text',
                $this->prediction_id
            ); ?>
        </div>
        <div class="col-12 col-md-6 col-lg-2">
            <label class="form-label" for="filter-state">
                <?php echo Text::_('JSTATUS'); ?>
            </label>
            <?php echo HTMLHelper::_(
                'select.genericlist',
                $statusOptions,
                'filter_state',
                'id="filter-state" class="form-select" onchange="this.form.submit();"',
                'value',
                'text',
                (string) $this->state->get('filter.state', '')
            ); ?>
        </div>
        <div class="col-12 col-lg-auto d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <span class="icon-search" aria-hidden="true"></span>
                <?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>
            </button>
            <a
                class="btn btn-secondary"
                href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=predictiongames&filter_prediction_id=0&filter_search=&filter_state='); ?>"
            >
                <?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
            </a>
        </div>
    </div>

    <?php echo $this->loadTemplate('data'); ?>

    <?php if ($this->prediction_id > 0) : ?>
        <?php echo $this->loadTemplate('projects'); ?>
    <?php endif; ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($this->sortColumn); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->sortDirection); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
