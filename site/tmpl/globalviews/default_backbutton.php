<?php
/**
 * Shared Joomla 5/6 back-button layout.
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$showBackButton = (string) ($this->overallconfig['show_back_button'] ?? '0');

if ($showBackButton === '' || $showBackButton === '0') {
    return;
}

$alignClass = $showBackButton === '1' ? 'text-start' : 'text-end';
$this->getDocument()->getWebAssetManager()->registerAndUseScript(
    'com_sportsmanagement.site.backbutton',
    'components/com_sportsmanagement/assets/js/backbutton.js',
    ['version' => 'auto'],
    ['defer' => true]
);
?>
<div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?> <?php echo $alignClass; ?> mt-3">
    <button type="button" class="btn back_button" data-jsm-back-button>
        <?php echo Text::_('COM_SPORTSMANAGEMENT_BACKBUTTON_BACK'); ?>
    </button>
</div>
