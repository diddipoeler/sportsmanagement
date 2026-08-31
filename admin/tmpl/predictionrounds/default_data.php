<?php
/** Native Joomla 5/6 prediction rounds list rows. */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$listOrder = (string) $this->state->get('list.ordering', 'roundcode');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
$lockModes = [
    'FIRSTMATCH_OF_TIPPGAME' => Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_RIEN_NE_VA_PLUS_FIRSTMATCH_OF_TIPPGAME'),
    'FIRSTMATCH_OF_TIPPROUND' => Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_RIEN_NE_VA_PLUS_FIRSTMATCH_OF_TIPPROUND'),
    'BEGIN_OF_MATCH' => Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_RIEN_NE_VA_PLUS_BEGIN_OF_MATCH'),
];
?>
<div class="table-responsive">
    <?php if (!empty($this->pred_project->name)) : ?>
        <div class="alert alert-info py-2">
            <?php echo Text::sprintf(
                'COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_LEGEND',
                '<strong>' . $this->escape((string) $this->pred_project->name) . '</strong>'
            ); ?>
        </div>
    <?php endif; ?>

    <table class="table table-striped table-hover align-middle" id="predictionroundsList">
        <thead>
        <tr>
            <th class="text-center">#</th>
            <th class="text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
            <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ROUND_NR', 'roundcode', $listDirn, $listOrder); ?></th>
            <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ROUND_TITLE', 'roundname', $listDirn, $listOrder); ?></th>
            <th class="text-center"><?php echo Text::_('JSTATUS'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_RIEN_NE_VA_PLUS'); ?></th>
            <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_POINTS_WRONG_PREDICTION'); ?></th>
            <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_POINTS_CORRECT_PREDICTION'); ?></th>
            <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_POINTS_CORRECT_MARGIN'); ?></th>
            <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_POINTS_DRAW_DIFFERENCE'); ?></th>
            <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_POINTS_CORRECT_TENDENCY'); ?></th>
            <th class="text-center"><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$this->items) : ?>
            <tr>
                <td colspan="12" class="text-center py-4"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td>
            </tr>
        <?php endif; ?>

        <?php foreach ($this->items as $i => $item) : ?>
            <?php
            $id = (int) $item->id;
            $checkedOut = (int) ($item->checked_out ?? 0);
            $checkedOutByOther = $checkedOut > 0 && $checkedOut !== (int) $this->user->id;
            $canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement') && !$checkedOutByOther;
            $canChange = $this->user->authorise('core.edit.state', 'com_sportsmanagement') && !$checkedOutByOther;
            $disabled = $canEdit ? '' : ' disabled';
            $markChecked = "var c=document.getElementById('cb{$i}');if(c&&!c.checked){c.checked=true;Joomla.isChecked(true);}";
            ?>
            <tr>
                <td class="text-center"><?php echo $this->pagination ? $this->pagination->getRowOffset($i) : $i + 1; ?></td>
                <td class="text-center">
                    <?php echo HTMLHelper::_('grid.id', $i, $id, !$canEdit); ?>
                </td>
                <td class="text-center"><?php echo $this->escape((string) ($item->roundcode ?? '')); ?></td>
                <td>
                    <?php if ($checkedOutByOther) : ?>
                        <span class="icon-lock me-1" aria-hidden="true"></span>
                    <?php endif; ?>
                    <?php echo $this->escape((string) ($item->roundname ?? '')); ?>
                </td>
                <td class="text-center">
                    <?php echo HTMLHelper::_('jgrid.published', (int) ($item->published ?? 0), $i, 'predictionrounds.', $canChange, 'cb'); ?>
                </td>
                <td>
                    <label class="visually-hidden" for="predictionround-lock-<?php echo $id; ?>">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_RIEN_NE_VA_PLUS'); ?>
                    </label>
                    <select
                        id="predictionround-lock-<?php echo $id; ?>"
                        name="rien_ne_va_plus<?php echo $id; ?>"
                        class="form-select form-select-sm"
                        onchange="<?php echo $markChecked; ?>"
                        <?php echo $disabled; ?>
                    >
                        <?php foreach ($lockModes as $value => $label) : ?>
                            <option value="<?php echo $this->escape($value); ?>"<?php echo (string) ($item->rien_ne_va_plus ?? '') === $value ? ' selected' : ''; ?>>
                                <?php echo $this->escape($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <?php foreach ([
                    'points_tipp',
                    'points_correct_result',
                    'points_correct_diff',
                    'points_correct_draw',
                    'points_correct_tendence',
                ] as $fieldName) : ?>
                    <td class="text-center">
                        <label class="visually-hidden" for="<?php echo $fieldName . '-' . $id; ?>">
                            <?php echo $this->escape($fieldName); ?>
                        </label>
                        <input
                            id="<?php echo $fieldName . '-' . $id; ?>"
                            type="number"
                            name="<?php echo $fieldName . $id; ?>"
                            value="<?php echo (int) ($item->{$fieldName} ?? 0); ?>"
                            class="form-control form-control-sm d-inline-block text-center"
                            style="width: 5.5rem"
                            onchange="<?php echo $markChecked; ?>"
                            <?php echo $disabled; ?>
                        >
                    </td>
                <?php endforeach; ?>
                <td class="text-center"><?php echo $id; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($this->pagination) : ?>
    <div class="mt-3"><?php echo $this->pagination->getListFooter(); ?></div>
<?php endif; ?>
