<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<legend><?php echo Text::sprintf(
    'COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_ASSIGN_TITLE',
    '<i>' . htmlspecialchars((string) $this->projectws->name, ENT_QUOTES, 'UTF-8') . '</i>'
); ?></legend>

<div class="row g-3 align-items-center">
    <div class="col-md-5">
        <label class="form-label fw-bold" for="matcheslist">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_ASSIGN_AVAIL_MATCHES'); ?>
        </label>
        <?php echo $this->lists['matches']; ?>
    </div>

    <div class="col-md-2 d-flex flex-column justify-content-center align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary" data-jsm-move data-source="matcheslist" data-target="node_matcheslist">
            <span class="icon-angle-double-right" aria-hidden="true"></span>
            <span class="visually-hidden"><?php echo Text::_('JTOOLBAR_ASSIGN'); ?></span>
        </button>
        <button type="button" class="btn btn-outline-secondary" data-jsm-move data-source="node_matcheslist" data-target="matcheslist">
            <span class="icon-angle-double-left" aria-hidden="true"></span>
            <span class="visually-hidden"><?php echo Text::_('JTOOLBAR_UNPUBLISH'); ?></span>
        </button>
    </div>

    <div class="col-md-5">
        <label class="form-label fw-bold" for="node_matcheslist">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_ASSIGN_NODE_MATCHES'); ?>
        </label>
        <?php echo $this->lists['node_matches']; ?>
    </div>
</div>
