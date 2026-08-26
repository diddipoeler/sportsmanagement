<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<div class="alert alert-success">
    <p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_WELCOME_INFO_01'); ?></p>
    <p><?php echo Text::sprintf(
        'COM_SPORTSMANAGEMENT_PRED_ENTRY_WELCOME_INFO_02',
        htmlspecialchars((string) ($this->config['ownername'] ?? ''), ENT_QUOTES, 'UTF-8'),
        '<b>' . htmlspecialchars($this->websiteName, ENT_QUOTES, 'UTF-8') . '</b>'
    ); ?></p>
</div>
