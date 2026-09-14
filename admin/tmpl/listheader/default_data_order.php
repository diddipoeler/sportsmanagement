<?php
/**
 * Native Joomla 5/6 administrator ordering controls for listheader rows.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$saveOrder = !empty($this->saveOrder);
$sortDirection = strtolower((string) ($this->sortDirection ?? 'asc'));
$ordering = (int) ($this->item->ordering ?? 0);
?>
<?php if ($saveOrder) : ?>
    <?php if ($sortDirection === 'asc') : ?>
        <span><?php echo $this->pagination->orderUpIcon($this->count_i, $ordering - 1, $this->view . '.orderup', 'JLIB_HTML_MOVE_UP', $this->ordering); ?></span>
        <span><?php echo $this->pagination->orderDownIcon($this->count_i, $this->pagination->total, $ordering + 1, $this->view . '.orderdown', 'JLIB_HTML_MOVE_DOWN', $this->ordering); ?></span>
    <?php elseif ($sortDirection === 'desc') : ?>
        <span><?php echo $this->pagination->orderUpIcon($this->count_i, $ordering - 1, $this->view . '.orderdown', 'JLIB_HTML_MOVE_UP', $this->ordering); ?></span>
        <span><?php echo $this->pagination->orderDownIcon($this->count_i, $this->pagination->total, $ordering + 1, $this->view . '.orderup', 'JLIB_HTML_MOVE_DOWN', $this->ordering); ?></span>
    <?php endif; ?>
<?php endif; ?>

<input type="text"
       name="order[]"
       size="5"
       value="<?php echo $ordering; ?>"
       <?php echo $saveOrder ? '' : 'disabled="disabled"'; ?>
       class="form-control form-control-inline text-center">

<?php $iconClass = $saveOrder ? '' : ' inactive'; ?>
<span class="sortable-handler<?php echo $iconClass; ?>"<?php echo $saveOrder ? '' : ' title="' . htmlspecialchars(Text::_('JORDERINGDISABLED'), ENT_QUOTES, 'UTF-8') . '"'; ?>>
    <span class="fas fa-ellipsis-v" aria-hidden="true"></span>
</span>
