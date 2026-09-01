<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$attributes = ['width' => '16px', 'height' => '18px'];
$dl = HTMLHelper::_('image', $this->path . 'treedl.gif', '', $attributes);
$ul = HTMLHelper::_('image', $this->path . 'treeul.gif', '', $attributes);
$cl = HTMLHelper::_('image', $this->path . 'treecl.gif', '', $attributes);
$p = HTMLHelper::_('image', $this->path . 'treep.gif', '', $attributes);
$currentUserId = (int) $this->getCurrentUser()->id;

$renderCheckbox = static function (object $node, int $rowIndex, bool $checked = false) use ($currentUserId): string {
    $checkedOut = (int) ($node->checked_out ?? 0);

    if ($checkedOut > 0 && $checkedOut !== $currentUserId) {
        return '<span class="icon-lock" aria-label="' . htmlspecialchars(Text::_('JLIB_HTML_CHECKED_OUT'), ENT_QUOTES, 'UTF-8') . '"></span>';
    }

    return '<input class="form-check-input treetonode-selector" type="checkbox"'
        . ' id="cb' . $rowIndex . '" name="cid[]" value="' . (int) $node->id . '"'
        . ($checked ? ' checked' : '')
        . ' onclick="Joomla.isChecked(this.checked);">';
};
?>
<div id="editcell" class="table-responsive">
    <legend>
        <?php echo Text::sprintf(
            'COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_LEGEND',
            '<i>' . htmlspecialchars((string) $this->projectws->name, ENT_QUOTES, 'UTF-8') . '</i>'
        ); ?>
    </legend>

    <?php
    $depth = (int) $this->treetows->tree_i;
    $rows = 2 * ((int) pow(2, $depth));
    $columns = 2 * $depth + 1;
    $visibleColumns = $columns - 2 * (int) $this->treetows->hide;
    ?>
    <table class="table align-middle">
        <tbody>
        <?php for ($row = 1; $row < $rows; $row++) :
            $node = $this->node[$row - 1] ?? null;

            if (!$node || (int) $node->published === 0) {
                continue;
            }
        ?>
            <tr>
                <td style="height:18px"></td>
                <?php for ($column = 1; $column <= $visibleColumns; $column++) :
                    $nodeCell = false;

                    for ($level = 0; $level <= $depth; $level++) {
                        $power = (int) pow(2, $level);

                        if ($column === 1 + ($level * 2) && $row % (2 * $power) === $power) {
                            $nodeCell = true;
                            break;
                        }
                    }
                ?>
                    <td <?php echo $nodeCell ? $this->style : ''; ?>>
                        <?php for ($level = 0; $level <= $depth; $level++) :
                            $power = (int) pow(2, $level);

                            if ($column === 1 + ($level * 2) && $row % (2 * $power) === $power) :
                                if ((int) $this->treetows->leafed === 1) :
                                    echo (int) $node->node . ' ';

                                    if ((int) $node->team_id > 0) :
                                        echo $renderCheckbox($node, $row - 1, true);
                                        ?>
                                        <input type="hidden" id="team_id<?php echo (int) $node->id; ?>" name="team_id<?php echo (int) $node->id; ?>" value="<?php echo (int) $node->team_id; ?>">
                                        <input type="hidden" id="roundcode<?php echo (int) $node->id; ?>" name="roundcode<?php echo (int) $node->id; ?>" value="<?php echo (int) $node->roundcode; ?>">
                                        <?php
                                        $editUrl = Route::_(
                                            'index.php?option=com_sportsmanagement&task=treetonode.edit&id=' . (int) $node->id
                                            . '&tid=' . (int) $this->tree_id . '&pid=' . (int) $this->project_id
                                        );
                                        $matchesUrl = Route::_(
                                            'index.php?option=com_sportsmanagement&view=treetomatchs&layout=default&nid=' . (int) $node->id
                                            . '&tid=' . (int) $this->tree_id . '&pid=' . (int) $this->project_id
                                        );
                                        $assignUrl = Route::_(
                                            'index.php?option=com_sportsmanagement&view=treetomatchs&layout=editlist&nid=' . (int) $node->id
                                            . '&tid=' . (int) $this->tree_id . '&pid=' . (int) $this->project_id
                                        );
                                        ?>
                                        <a href="<?php echo $editUrl; ?>" class="ms-1" title="<?php echo Text::_('JACTION_EDIT'); ?>"><span class="icon-edit" aria-hidden="true"></span></a>
                                        <span class="mx-1"><?php echo htmlspecialchars((string) $node->team_name, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <a href="<?php echo $matchesUrl; ?>" class="me-1" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TITLE'); ?>"><span class="icon-list" aria-hidden="true"></span></a>
                                        <a href="<?php echo $assignUrl; ?>" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_ASSIGN'); ?>"><span class="icon-link" aria-hidden="true"></span></a>
                                    <?php else :
                                        echo $renderCheckbox($node, $row - 1);
                                        $selectAttributes = 'class="form-select form-select-sm select-hometeam d-inline-block w-auto" '
                                            . 'onchange="const cb=document.getElementById(\'cb' . ($row - 1) . '\');'
                                            . 'if(cb && !cb.checked){cb.checked=true;Joomla.isChecked(true);}"';
                                        echo HTMLHelper::_(
                                            'select.genericlist',
                                            $this->lists['team'],
                                            'team_id' . (int) $node->id,
                                            $selectAttributes,
                                            'value',
                                            'text',
                                            (int) $node->team_id
                                        );
                                    endif;
                                elseif ((int) $node->is_leaf === 1) : ?>
                                    <span class="icon-cog" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_SAVE_LEAF'); ?>"></span>
                                <?php else :
                                    echo $renderCheckbox($node, $row - 1);
                                endif;
                            elseif ($column === 2 + ($level * 2) && $row % (4 * $power) === $power) :
                                echo $dl;
                            elseif ($column === 2 + ($level * 2) && $row % (4 * $power) === 2 * $power) :
                                if ((int) $node->is_leaf !== 1) {
                                    echo $cl;
                                }
                            elseif ($column === 2 + ($level * 2) && $row % (4 * $power) === 3 * $power) :
                                echo $ul;
                            elseif (
                                $column === 2 + ($level * 2)
                                && $row % (4 * $power) > $power
                                && $row % (4 * $power) < 3 * $power
                            ) :
                                echo $p;
                            endif;
                        endfor; ?>
                    </td>
                <?php endfor; ?>
            </tr>
        <?php endfor; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('adminForm');
    const boxchecked = form ? form.querySelector('input[name="boxchecked"]') : null;

    if (boxchecked) {
        boxchecked.value = form.querySelectorAll('.treetonode-selector:checked').length;
    }
});
</script>
