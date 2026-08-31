<?php
/** Joomla 5/6 native administrator footer layout. */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$logo = Uri::base(true) . '/components/com_sportsmanagement/assets/icons/logo_transparent.png';
$endTime = microtime(true);
$startTime = isset($this->jsmstartzeit) && (float) $this->jsmstartzeit > 0
    ? (float) $this->jsmstartzeit
    : $endTime;
$pageTime = round($endTime - $startTime, 6);
?>
<div class="container text-center d-flex align-items-center justify-content-center">
    <div>
        <div>
            <a title="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_SITE_LINK')); ?>"
               target="_blank" rel="noopener noreferrer"
               href="https://www.fussballineuropa.de">
                <img src="<?php echo $this->escape($logo); ?>"
                     width="180"
                     alt="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_SITE_LINK')); ?>">
            </a>
        </div>
        <div><?php echo Text::_('COM_SPORTSMANAGEMENT_DESC'); ?></div>
        <div>
            <?php echo Text::_('COM_SPORTSMANAGEMENT_COPYRIGHT'); ?>: &copy;
            <a href="https://www.fussballineuropa.de" target="_blank" rel="noopener noreferrer">Fussball in Europa</a>
        </div>
        <div><?php echo Text::_('COM_SPORTSMANAGEMENT_VERSION'); ?>:</div>
        <div><?php echo Text::sprintf('%1$s', sportsmanagementHelper::getVersion()); ?></div>
        <div class="center">
            <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_FOOTER_TIME', $pageTime); ?>
        </div>
    </div>
</div>
