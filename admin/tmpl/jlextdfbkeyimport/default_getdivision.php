<?php
/**
 * Native Joomla 5/6 DFB-key division selection layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/** SportsManagement DFB-key division selection template. */
\\defined('_JEXEC') or die;

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
