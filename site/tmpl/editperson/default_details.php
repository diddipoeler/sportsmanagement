<?php
/** Joomla 5/6 frontend person details template. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
?>
<fieldset class="adminform">
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'); ?></legend>
    <table class="admintable">
        <?php foreach ($this->form->getFieldset('details') as $field) : ?>
            <tr>
                <td class="key"><?php echo $field->label; ?></td>
                <td><?php echo $field->input; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</fieldset>
