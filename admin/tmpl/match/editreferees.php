<?php
/**
 * Native Joomla 5/6 administrator layout for assigning match referees.
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

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$matchId = (int) ($this->match->id ?? $this->item->id ?? 0);
$action = Route::_('index.php?option=com_sportsmanagement&view=match&layout=editreferees&tmpl=component&id=' . $matchId);
?>
<form action="<?php echo $action; ?>" method="post" name="adminForm" id="match-referees-form">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h4 mb-1"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_TITLE'); ?></h2>
            <div class="text-muted">
                <?php echo $escape($this->match->hometeam ?? ''); ?>
                &nbsp;&ndash;&nbsp;
                <?php echo $escape($this->match->awayteam ?? ''); ?>
            </div>
        </div>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-success" data-jsm-referee-submit="apply">
                <?php echo Text::_('JAPPLY'); ?>
            </button>
            <button type="button" class="btn btn-primary" data-jsm-referee-submit="save">
                <?php echo Text::_('JSAVE'); ?>
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='<?php echo Route::_('index.php?option=com_sportsmanagement&view=close&tmpl=component'); ?>';">
                <?php echo Text::_('JCANCEL'); ?>
            </button>
        </div>
    </div>

    <div class="alert alert-info">
        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_DESCR'); ?>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <label class="form-label fw-semibold" for="jsm-referee-pool">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_REFS'); ?>
            </label>
            <select id="jsm-referee-pool" class="form-select" multiple size="18">
                <?php foreach ($this->availableReferees as $referee) : ?>
                    <option value="<?php echo (int) ($referee->value ?? 0); ?>">
                        <?php echo $escape($referee->text ?? ''); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12 col-lg-8">
            <?php if (!$this->refereePositions) : ?>
                <div class="alert alert-warning mb-0">
                    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_REF_POS'); ?>
                </div>
            <?php endif; ?>

            <?php foreach ($this->refereePositions as $positionKey => $position) : ?>
                <?php $targetId = 'jsm-referee-position-' . (int) $positionKey; ?>
                <div class="card mb-3" data-jsm-referee-position>
                    <div class="card-header fw-semibold"><?php echo $escape($position->text ?? ''); ?></div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-md-3 d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary" data-jsm-referee-assign="<?php echo $targetId; ?>">
                                    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_ASSIGN'); ?> &rarr;
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-jsm-referee-unassign="<?php echo $targetId; ?>">
                                    &larr; <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_UNASSIGN'); ?>
                                </button>
                            </div>
                            <div class="col-12 col-md-7">
                                <select
                                    id="<?php echo $targetId; ?>"
                                    class="form-select jsm-referee-assigned"
                                    name="position<?php echo (int) $positionKey; ?>[]"
                                    multiple
                                    size="6"
                                >
                                    <?php foreach (($this->assignedReferees[(int) $positionKey] ?? []) as $referee) : ?>
                                        <option value="<?php echo (int) ($referee->value ?? 0); ?>">
                                            <?php echo $escape($referee->text ?? ''); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-2 d-grid gap-2">
                                <button type="button" class="btn btn-outline-secondary" data-jsm-referee-up="<?php echo $targetId; ?>">
                                    <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-jsm-referee-down="<?php echo $targetId; ?>">
                                    <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DOWN'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="id" value="<?php echo $matchId; ?>">
    <input type="hidden" name="project_id" value="<?php echo (int) $this->projectId; ?>">
    <input type="hidden" name="close" id="jsm-referee-close" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<script>
(() => {
    const form = document.getElementById('match-referees-form');
    const pool = document.getElementById('jsm-referee-pool');
    if (!form || !pool) return;

    const moveSelected = (source, target) => {
        [...source.selectedOptions].forEach((option) => {
            option.selected = false;
            target.append(option);
        });
    };

    const moveOption = (select, direction) => {
        const selected = [...select.selectedOptions];
        const items = direction < 0 ? selected : selected.reverse();
        items.forEach((option) => {
            const sibling = direction < 0 ? option.previousElementSibling : option.nextElementSibling;
            if (!sibling) return;
            if (direction < 0) select.insertBefore(option, sibling);
            else select.insertBefore(sibling, option);
        });
    };

    document.querySelectorAll('[data-jsm-referee-assign]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = document.getElementById(button.dataset.jsmRefereeAssign || '');
            if (target) moveSelected(pool, target);
        });
    });

    document.querySelectorAll('[data-jsm-referee-unassign]').forEach((button) => {
        button.addEventListener('click', () => {
            const source = document.getElementById(button.dataset.jsmRefereeUnassign || '');
            if (source) moveSelected(source, pool);
        });
    });

    document.querySelectorAll('[data-jsm-referee-up]').forEach((button) => {
        button.addEventListener('click', () => {
            const select = document.getElementById(button.dataset.jsmRefereeUp || '');
            if (select) moveOption(select, -1);
        });
    });

    document.querySelectorAll('[data-jsm-referee-down]').forEach((button) => {
        button.addEventListener('click', () => {
            const select = document.getElementById(button.dataset.jsmRefereeDown || '');
            if (select) moveOption(select, 1);
        });
    });

    document.querySelectorAll('[data-jsm-referee-submit]').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.jsm-referee-assigned option').forEach((option) => {
                option.selected = true;
            });
            document.getElementById('jsm-referee-close').value = button.dataset.jsmRefereeSubmit === 'save' ? '1' : '0';
            Joomla.submitform('matches.saveReferees', form);
        });
    });
})();
</script>
