<?php
/**
 * Shared Joomla 5/6 Bootstrap 5 tab layout.
 */
\defined('_JEXEC') or die;

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
        <ul class="nav nav-tabs" id="jsm-view-tabs" role="tablist">
            <?php foreach ($tabs as $index => $tab) : ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link<?php echo $index === 0 ? ' active' : ''; ?>"
                            id="<?php echo htmlspecialchars($tab['id'], ENT_QUOTES, 'UTF-8'); ?>-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#<?php echo htmlspecialchars($tab['id'], ENT_QUOTES, 'UTF-8'); ?>"
                            type="button"
                            role="tab"
                            aria-controls="<?php echo htmlspecialchars($tab['id'], ENT_QUOTES, 'UTF-8'); ?>"
                            aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                        <?php echo htmlspecialchars(Text::_($tab['text']), ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content" id="jsm-view-tab-content">
            <?php foreach ($tabs as $index => $tab) : ?>
                <div class="tab-pane fade<?php echo $index === 0 ? ' show active' : ''; ?>"
                     id="<?php echo htmlspecialchars($tab['id'], ENT_QUOTES, 'UTF-8'); ?>"
                     role="tabpanel"
                     aria-labelledby="<?php echo htmlspecialchars($tab['id'], ENT_QUOTES, 'UTF-8'); ?>-tab"
                     tabindex="0">
                    <div class="<?php echo htmlspecialchars((string) $this->divclasscontainer, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo $this->loadTemplate($tab['template']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
