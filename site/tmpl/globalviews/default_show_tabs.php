<?php
/**
 * Shared Joomla 5/6 tab layout.
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$output = (array) ($this->output ?? []);
$tabs = [];

foreach ($output as $key => $templateData) {
    if (($this->view ?? '') === 'player' && is_array($templateData)) {
        $template = (string) ($templateData['template'] ?? '');
        $text = (string) ($templateData['text'] ?? $key);
    } else {
        $template = (string) $templateData;
        $text = (string) $key;
    }

    if ($template === '') {
        continue;
    }

    $tabs[] = [
        'template' => $template,
        'text' => $text,
        'id' => 'jsm-tab-' . substr(sha1($text . '|' . $template), 0, 12),
    ];
}
?>
<div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?>" id="show_tabs">
    <?php if ($tabs) : ?>
        <?php echo HTMLHelper::_('bootstrap.startTabSet', 'jsm-view-tabs', ['active' => $tabs[0]['id']]); ?>
        <?php foreach ($tabs as $tab) : ?>
            <?php echo HTMLHelper::_('bootstrap.addTab', 'jsm-view-tabs', $tab['id'], Text::_($tab['text'])); ?>
            <div class="<?php echo htmlspecialchars((string) $this->divclasscontainer, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo $this->loadTemplate($tab['template']); ?>
                </div>
            </div>
            <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        <?php endforeach; ?>
        <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
    <?php endif; ?>
</div>
