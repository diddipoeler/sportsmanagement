<?php
/** Tournament-tree match assignment controls for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
?>
<legend>
    <?php echo Text::sprintf(
        'COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_ASSIGN_TITLE',
        '<i>' . htmlspecialchars((string) $this->projectws->name) . '</i>'
    ); ?>
</legend>

<div class="row g-3 align-items-center">
    <div class="col-md-5">
        <label class="form-label fw-bold" for="matcheslist">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_ASSIGN_AVAIL_MATCHES'); ?>
        </label>
        <?php echo $this->lists['matches']; ?>
    </div>

    <div class="col-md-2 d-flex flex-column justify-content-center align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary"
                onclick="move_list_items('matcheslist','node_matcheslist');">
            &gt;&gt;
        </button>
        <button type="button" class="btn btn-outline-secondary"
                onclick="move_list_items('node_matcheslist','matcheslist');">
            &lt;&lt;
        </button>
    </div>

    <div class="col-md-5">
        <label class="form-label fw-bold" for="node_matcheslist">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_ASSIGN_NODE_MATCHES'); ?>
        </label>
        <?php echo $this->lists['node_matches']; ?>
    </div>
</div>
