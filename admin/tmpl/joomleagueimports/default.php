<?php
/** Joomla 5/6 JoomLeague import progress view. */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$step = (string) $this->jl_table_import_step;
$continuationTask = $step === 'ENDE'
    ? 'joomleagueimports.importjoomleagueagegroup'
    : ($step !== '0' ? 'joomleagueimports.importjoomleaguenew' : '');
$delay = $step === 'ENDE' ? 5 : 3;

if ($continuationTask !== '') {
    $script = <<<'JS'
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('adminForm');
    const message = document.getElementById('delayMsg');
    const counter = document.getElementById('countDown');
    let remaining = __DELAY__;

    if (!form || !message || !counter) {
        return;
    }

    message.textContent = __STEP_TEXT__;
    counter.textContent = String(remaining);

    const timer = window.setInterval(() => {
        remaining -= 1;
        counter.textContent = String(Math.max(remaining, 0));

        if (remaining <= 0) {
            window.clearInterval(timer);
            message.textContent = __START_TEXT__;
            Joomla.submitform(__TASK__, form);
        }
    }, 1000);
});
JS;
    $script = str_replace(
        ['__DELAY__', '__STEP_TEXT__', '__START_TEXT__', '__TASK__'],
        [
            (string) $delay,
            json_encode(Text::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT_STEP')),
            json_encode(Text::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT_STEP_START')),
            json_encode($continuationTask),
        ],
        $script
    );
    $this->getDocument()->addScriptDeclaration($script);
}
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=joomleagueimports&layout=default', false); ?>"
      method="post" id="adminForm" name="adminForm">
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="filter_sports_type" class="form-label">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'); ?>
                    </label>
                    <select name="filter_sports_type" id="filter_sports_type" class="form-select">
                        <?php foreach ($this->sportstypeOptions as $option) : ?>
                            <option value="<?php echo (int) $option->value; ?>"
                                <?php echo (int) $option->value === $this->selectedSportstype ? ' selected' : ''; ?>>
                                <?php echo $this->escape((string) $option->text); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 text-md-end">
                    <div id="delayMsg" class="fw-semibold"></div>
                    <div id="countDown" class="display-6" aria-live="polite"></div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($this->success)) : ?>
        <div class="card mb-3">
            <div class="card-header"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT'); ?></div>
            <div class="card-body">
                <?php if (is_array($this->success)) : ?>
                    <?php foreach ($this->success as $key => $value) : ?>
                        <section class="mb-3">
                            <?php if (!is_int($key)) : ?>
                                <h3 class="h6"><?php echo Text::_((string) $key); ?></h3>
                            <?php endif; ?>
                            <div><?php echo (string) $value; ?></div>
                        </section>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div><?php echo (string) $this->success; ?></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="jl_table_import_step" value="<?php echo $this->escape($step); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
