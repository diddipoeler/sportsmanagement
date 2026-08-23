<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

if (!$this->ress) {
    return;
}

$currentId = (int) ($this->roundws->id ?? 0);
$previousId = 0;
$nextId = 0;
$seenCurrent = false;

foreach ($this->ress as $round) {
    $roundId = (int) $round->id;
    if ($seenCurrent) {
        $nextId = $roundId;
        break;
    }
    if ($roundId === $currentId) {
        $seenCurrent = true;
        continue;
    }
    $previousId = $roundId;
}
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=matches&pid=' . (int) $this->project_id); ?>"
    method="post"
    name="roundForm"
    id="roundForm"
    class="mb-3"
>
    <input type="hidden" name="act" value="" id="short_act">
    <input type="hidden" name="task" value="">
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">

    <div class="d-flex flex-wrap align-items-center gap-2">
        <?php if ($previousId > 0) : ?>
            <a class="btn btn-outline-secondary btn-sm" href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=matches&pid=' . (int) $this->project_id . '&rid=' . $previousId); ?>">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_PREV_MATCH'); ?>
            </a>
        <?php endif; ?>

        <?php echo $this->lists['project_rounds'] ?? ''; ?>

        <?php if ($nextId > 0) : ?>
            <a class="btn btn-outline-secondary btn-sm" href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=matches&pid=' . (int) $this->project_id . '&rid=' . $nextId); ?>">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_NEXT_MATCH'); ?>
            </a>
        <?php endif; ?>
    </div>

    <?php echo HTMLHelper::_('form.token'); ?>
</form>
