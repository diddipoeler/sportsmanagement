<?php
/**
 * Shared Joomla 5/6 extended-data layout.
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (!empty($this->config['show_extended_text'])) {
    $this->notes = [Text::_('COM_SPORTSMANAGEMENT_EXT_EXTENDED_PREFERENCES')];
    echo $this->loadTemplate('jsm_notes');
}

if (!empty($this->extended)) {
    foreach ($this->extended as $key => $value) {
        ?>
        <div class="row">
            <div class="col-sm-3">
                <label><?php echo Text::_((string) $key); ?></label>
            </div>
            <div class="col-sm-9"><?php echo $value; ?></div>
        </div>
        <?php
    }
}

if (!empty($this->extended2) && method_exists($this->extended2, 'getFieldset')) {
    foreach ($this->extended2->getFieldset('COM_SPORTSMANAGEMENT_EXT_EXTENDED_PREFERENCES') as $field) {
        ?>
        <div class="row">
            <div class="col-sm-3">
                <label for="<?php echo htmlspecialchars((string) $field->name, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo $field->label; ?>
                </label>
            </div>
            <div class="col-sm-9">
                <?php
                if ((string) $field->type === 'Url' && filter_var((string) $field->value, FILTER_VALIDATE_URL)) {
                    echo HTMLHelper::link(
                        (string) $field->value,
                        (string) $field->value,
                        ['target' => '_blank', 'rel' => 'noopener']
                    );
                } else {
                    echo $field->value;
                }
                ?>
            </div>
        </div>
        <?php
    }
}
