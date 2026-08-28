<?php
/** Joomla 5/6 team-plan main layout. */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<div class="<?php echo $this->escape($this->divclasscontainer); ?>" id="teamplan">
    <?php if (!empty($this->config['show_teamplan_print_option'])) : ?>
        <div class="d-flex gap-2 mb-3 d-print-none">
            <button id="exportButton" type="button" class="btn btn-primary">
                <span class="fa fa-file-pdf-o" aria-hidden="true"></span>
                Export to PDF
            </button>
            <button id="btnPrint" type="button" class="btn btn-primary">
                <span class="fa fa-print" aria-hidden="true"></span>
                <?php echo Text::_('JGLOBAL_PRINT'); ?>
            </button>
        </div>
    <?php endif; ?>

    <?php if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) : ?>
        <?php echo $this->loadTemplate('debug'); ?>
    <?php endif; ?>

    <?php if (!empty($this->project->id)) : ?>
        <?php echo $this->loadTemplate('projectheading'); ?>

        <?php if (!empty($this->config['show_sectionheader'])) : ?>
            <?php echo $this->loadTemplate('sectionheader'); ?>
        <?php endif; ?>

        <?php
        $this->groupMatchesByDate = ($this->config['show_plan_layout'] ?? 'plan_default') === 'plan_sorted_by_date';
        echo $this->loadTemplate('matches_native');
        ?>
    <?php else : ?>
        <p><?php echo Text::_('COM_SPORTSMANAGEMENT_ERROR_PROJECTMODEL_PROJECT_IS_REQUIRED'); ?></p>
    <?php endif; ?>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
