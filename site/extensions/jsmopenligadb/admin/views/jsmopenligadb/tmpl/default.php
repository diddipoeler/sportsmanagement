<?php
/**
 * SportsManagement OpenLigaDB administrator preview template.
 */

defined('_JEXEC') or die('Restricted access');

$summary = $this->previewSummary ?? [
    'matches' => 0,
    'teams' => 0,
    'playgrounds' => 0,
    'goals' => 0,
];
?>
<div class="card">
    <div class="card-body">
        <h2 class="h4">OpenLigaDB Preview</h2>
        <dl class="row mb-0">
            <dt class="col-sm-6">Matches</dt>
            <dd class="col-sm-6"><?php echo (int) $summary['matches']; ?></dd>
            <dt class="col-sm-6">Teams</dt>
            <dd class="col-sm-6"><?php echo (int) $summary['teams']; ?></dd>
            <dt class="col-sm-6">Playgrounds</dt>
            <dd class="col-sm-6"><?php echo (int) $summary['playgrounds']; ?></dd>
            <dt class="col-sm-6">Goals</dt>
            <dd class="col-sm-6"><?php echo (int) $summary['goals']; ?></dd>
        </dl>
    </div>
</div>
