<?php
/** One image in the SportsManagement administrator image browser. */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$file = (string) ($this->_tmp_img->file ?? '');
$name = (string) ($this->_tmp_img->name ?? $file);
$imageUrl = Uri::root() . 'images/com_sportsmanagement/database/'
    . trim((string) ($this->_tmp_img->path_relative ?? ''), '/') . '/' . rawurlencode($file);
$onclick = 'exportToForm(' . json_encode($file, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '); return false;';
?>
<div class="media-browser-item card p-2 text-center">
    <a href="<?php echo $this->escape($imageUrl); ?>" target="_blank" rel="noopener">
        <img src="<?php echo $this->escape($imageUrl); ?>" alt="<?php echo $this->escape($name); ?>"
             width="60" height="60" loading="lazy">
    </a>
    <div class="small text-break mt-1">
        <?php echo Text::sprintf(
            'COM_MEDIA_IMAGE_TITLE',
            HTMLHelper::_('string.truncate', $name, 24, false),
            HTMLHelper::_('number.bytes', (int) ($this->_tmp_img->size ?? 0))
        ); ?>
    </div>

    <?php if ($this->folder !== 'rosterground') : ?>
        <button type="button" class="btn btn-sm btn-primary mt-2" onclick="<?php echo $this->escape($onclick); ?>">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_ADD'); ?>
        </button>
    <?php endif; ?>
</div>
