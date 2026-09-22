<?php
/**
 * Native Joomla 5/6 project referees administrator layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<form action="<?php echo $escape($this->request_url); ?>" method="post" id="adminForm" name="adminForm">
    <?php if (!empty($this->items)) : ?>
        <?php require __DIR__ . '/default_data.php'; ?>
    <?php endif; ?>

    <input type="hidden" name="search_mode" value="<?php echo $escape($this->lists['search_mode'] ?? ''); ?>" id="search_mode">
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="filter_order" value="<?php echo $escape($this->sortColumn); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo $escape($this->sortDirection); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
