<?php
/** Shared Joomla 5/6 frontend debug output. */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$debugEntries = [];

if (isset($this->debug) && is_array($this->debug)) {
    $debugEntries = $this->debug;
} elseif (class_exists('sportsmanagementHelper', false)) {
    $debugEntries = (array) (sportsmanagementHelper::$_success_text ?? []);
}

if (!$debugEntries) {
    return;
}

HTMLHelper::_('bootstrap.framework');
?>
<div id="sportsmanagement-debug">
    <?php
    echo HTMLHelper::_(
        'bootstrap.startAccordion',
        'sportsmanagement-debug-accordion',
        ['active' => 'sportsmanagement-debug-0']
    );

    $index = 0;
    foreach ($debugEntries as $label => $value) {
        $slideId = 'sportsmanagement-debug-' . $index++;
        echo HTMLHelper::_(
            'bootstrap.addSlide',
            'sportsmanagement-debug-accordion',
            Text::_((string) $label),
            $slideId
        );
        ?>
        <pre class="mb-0"><?php echo htmlspecialchars(print_r($value, true), ENT_QUOTES, 'UTF-8'); ?></pre>
        <?php
        echo HTMLHelper::_('bootstrap.endSlide');
    }

    echo HTMLHelper::_('bootstrap.endAccordion');
    ?>
</div>
