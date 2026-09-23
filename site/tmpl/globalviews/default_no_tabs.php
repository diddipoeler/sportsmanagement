<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Shared Joomla 5/6 layout for extended output without tabs.
 */
\defined('_JEXEC') or die;

$columns = max(1, min(12, (int) ($this->config['extended_cols'] ?? 12)));
$view = $this->input->getCmd('view', (string) ($this->view ?? $this->getName()));
?>
<div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?>" id="no_tabs">
    <div class="col-<?php echo $columns; ?>">
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
