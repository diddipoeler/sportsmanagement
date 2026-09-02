<?php
/** Shared Joomla 5/6 frontend debug output without a JavaScript accordion dependency. */
\defined('_JEXEC') or die;

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
?>
<div id="sportsmanagement-debug" class="vstack gap-2">
    <?php foreach ($debugEntries as $index => $value) : ?>
        <?php
        $label = is_string($index) ? Text::_($index) : Text::sprintf('JGLOBAL_FIELD_ID_LABEL', (int) $index + 1);
        ?>
        <details class="border rounded p-2"<?php echo $index === array_key_first($debugEntries) ? ' open' : ''; ?>>
            <summary class="fw-semibold"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></summary>
            <pre class="mb-0 mt-2 overflow-auto"><?php echo htmlspecialchars(print_r($value, true), ENT_QUOTES, 'UTF-8'); ?></pre>
        </details>
    <?php endforeach; ?>
</div>
