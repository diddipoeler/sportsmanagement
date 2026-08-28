<?php
/** Native Joomla 5/6 SportsManagement XML export layout. */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<form action="<?php echo $escape($this->request_url); ?>" method="post" id="adminForm" name="adminForm">
    <div class="row-fluid">
        <?php echo $escape('exportSystem ' . $this->exportSystem); ?>
    </div>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token') . "\n"; ?>
    <?php echo $this->table_data_div; ?>
</form>
