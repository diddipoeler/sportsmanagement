<?php
/**
 * Shared Joomla 5/6 Bootstrap 5 accordion layout.
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$view = $this->input->getCmd('view', (string) ($this->view ?? $this->getName()));
$output = (array) ($this->output ?? []);
$slides = [];

foreach ($output as $key => $templateData) {
    if ($view === 'player' && is_array($templateData)) {
        $template = (string) ($templateData['template'] ?? '');
        $text = (string) ($templateData['text'] ?? $key);
    } else {
        $template = (string) $templateData;
        $text = (string) $key;
    }

    if ($template === '') {
        continue;
    }

    $slides[] = [
        'template' => $template,
        'text' => $text,
        'id' => 'jsm-slide-' . substr(sha1($text . '|' . $template), 0, 12),
    ];
}
?>
<div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?>" id="show_slider">
    <?php if ($slides) : ?>
        <div class="accordion" id="jsm-view-accordion">
            <?php foreach ($slides as $index => $slide) : ?>
                <?php $collapseId = $slide['id'] . '-collapse'; ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="<?php echo htmlspecialchars($slide['id'], ENT_QUOTES, 'UTF-8'); ?>-heading">
                        <button class="accordion-button<?php echo $index === 0 ? '' : ' collapsed'; ?>"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>"
                                aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                aria-controls="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars(Text::_($slide['text']), ENT_QUOTES, 'UTF-8'); ?>
                        </button>
                    </h2>
                    <div id="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>"
                         class="accordion-collapse collapse<?php echo $index === 0 ? ' show' : ''; ?>"
                         aria-labelledby="<?php echo htmlspecialchars($slide['id'], ENT_QUOTES, 'UTF-8'); ?>-heading"
                         data-bs-parent="#jsm-view-accordion">
                        <div class="accordion-body">
                            <?php echo $this->loadTemplate($slide['template']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
