<?php
/**
 * Shared Joomla 5/6 accordion layout.
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$view = $this->input->getCmd('view', (string) ($this->view ?? $this->getName()));
$output = (array) ($this->output ?? []);
?>
<div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?>" id="show_slider">
    <?php if ($output) : ?>
        <?php echo HTMLHelper::_('bootstrap.startAccordion', 'collapseTypes', ['active' => 'collapse1', 'parent' => 'collapseTypes']); ?>
        <?php
        $index = 1;
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

            $slideId = 'collapse' . $index++;
            echo HTMLHelper::_('bootstrap.addSlide', 'collapseTypes', Text::_($text), $slideId);
            echo $this->loadTemplate($template);
            echo HTMLHelper::_('bootstrap.endSlide');
        }
        echo HTMLHelper::_('bootstrap.endAccordion');
        ?>
    <?php endif; ?>
</div>
