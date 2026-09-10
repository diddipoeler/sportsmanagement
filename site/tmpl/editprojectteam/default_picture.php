<?php
/** Joomla 5/6 frontend project-team picture template. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
?>
<fieldset class="mb-4">
    <legend class="h5"><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_PICTURE'); ?></legend>

    <?php foreach ($this->form->getFieldset('picture') as $field) : ?>
        <?php if (strtolower((string) $field->type) === 'hidden') : ?>
            <?php echo $field->input; ?>
            <?php continue; ?>
        <?php endif; ?>
        <div class="mb-3">
            <div class="form-label"><?php echo $field->label; ?></div>
            <?php echo $field->input; ?>
        </div>
    <?php endforeach; ?>
</fieldset>
