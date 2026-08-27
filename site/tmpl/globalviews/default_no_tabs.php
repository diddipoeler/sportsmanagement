<?php
/**
 * Shared Joomla 5/6 layout for extended output without tabs.
 */
\defined('_JEXEC') or die;

$columns = max(1, min(12, (int) ($this->config['extended_cols'] ?? 12)));
$view = $this->input->getCmd('view', (string) ($this->view ?? $this->getName()));
?>
<div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?>" id="no_tabs">
    <div class="col-xs-<?php echo $columns; ?> col-sm-<?php echo $columns; ?> col-md-<?php echo $columns; ?> col-lg-<?php echo $columns; ?>">
        <?php
        foreach ((array) ($this->output ?? []) as $key => $templateData) {
            if ($view === 'player' && is_array($templateData)) {
                $template = (string) ($templateData['template'] ?? '');
            } else {
                $template = (string) $templateData;
            }

            if ($template !== '') {
                echo $this->loadTemplate($template);
            }
        }
        ?>
    </div>
</div>
