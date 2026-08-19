<?php
/** SportsManagement DFB-key division selection template. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
?>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
    <?php echo '<br>' . HTMLHelper::_(
        'select.genericlist',
        $this->lists['divisions'],
        'divisionid',
        'class="inputbox" size="1"',
        'value',
        'text',
        $this->division
    ); ?>
    <input type="hidden" name="sent" value="1" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="projectid" value="<?php echo (int) $this->project_id; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
