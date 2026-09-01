<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<div class="options-form">
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_TITLE_GENERATENODE'); ?></legend>

    <?php foreach ($this->form->getFieldset('generate') as $field) : ?>
        <?php if (strtolower((string) $field->type) === 'hidden') : ?>
            <?php echo $field->input; ?>
        <?php else : ?>
            <div class="control-group mb-3">
                <div class="control-label"><?php echo $field->label; ?></div>
                <div class="controls"><?php echo $field->input; ?></div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-primary"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_GENERATE'); ?></button>
</div>
