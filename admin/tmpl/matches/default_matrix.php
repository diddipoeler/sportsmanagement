<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$divisionId = (int) $this->state->get('filter.division', 0);
$teams = $this->model->getProjectTeamOptions($this->project_id, $divisionId);

if (!$teams) {
    return;
}

$existing = [];
foreach ($this->matches as $match) {
    $existing[(int) $match->projectteam1_id . ':' . (int) $match->projectteam2_id] = true;
}
?>
<form method="post" name="matrixForm" id="matrixForm" class="mt-4">
    <h2 class="h4"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MATRIX_TITLE'); ?></h2>
    <p><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MATRIX_HINT'); ?></p>

    <div class="table-responsive">
        <table class="table table-striped table-sm align-middle">
            <thead>
                <tr>
                    <th></th>
                    <?php foreach ($teams as $away) : ?>
                        <th class="text-center"><?php echo $this->escape((string) $away->text); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teams as $home) : ?>
                    <tr>
                        <th><?php echo $this->escape((string) $home->text); ?></th>
                        <?php foreach ($teams as $away) :
                            $homeId = (int) $home->value;
                            $awayId = (int) $away->value;
                            $exists = isset($existing[$homeId . ':' . $awayId]);
                        ?>
                            <td class="text-center">
                                <?php if ($homeId === $awayId) : ?>
                                    &mdash;
                                <?php elseif ($exists) : ?>
                                    <span class="badge bg-success"><?php echo Text::_('JYES'); ?></span>
                                <?php else : ?>
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary btn-sm"
                                        onclick="SaveMatch('<?php echo $homeId; ?>','<?php echo $awayId; ?>')"
                                        title="<?php echo $this->escape((string) $home->text . ' - ' . (string) $away->text); ?>"
                                    >+</button>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <input type="hidden" name="match_date" value="<?php echo $this->escape((string) ($this->roundws->round_date_first ?? '') . ' ' . (string) ($this->projectws->start_time ?? '')); ?>">
    <input type="hidden" name="projectteam1_id" value="">
    <input type="hidden" name="projectteam2_id" value="">
    <input type="hidden" name="published" value="1">
    <input type="hidden" name="task" value="match.addmatch">
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="rid" value="<?php echo (int) $this->rid; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
