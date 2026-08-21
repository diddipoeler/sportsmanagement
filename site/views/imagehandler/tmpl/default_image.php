<?php
/** One image in the SportsManagement frontend selector. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

$imageName = (string) ($this->_tmp_img->name ?? '');
$folder = trim((string) $this->folder, '/');
$type = (string) $this->type;
$imageUrl = Uri::root() . 'images/com_sportsmanagement/database/'
    . $folder . '/' . rawurlencode($imageName);
$selectFunction = 'selectImage_' . $type;
$selectJs = 'if (window.parent && typeof window.parent['
    . json_encode($selectFunction, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    . '] === "function") { window.parent['
    . json_encode($selectFunction, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    . ']('
    . json_encode($imageName, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ','
    . json_encode($imageName, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ','
    . json_encode((string) $this->field, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ','
    . json_encode((string) $this->fieldid, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    . '); } return false;';
$deleteUrl = 'index.php?' . http_build_query([
    'option' => 'com_sportsmanagement',
    'task' => 'imagehandler.delete',
    'tmpl' => 'component',
    'type' => $type,
    'rm' => [$imageName],
    Session::getFormToken() => 1,
]);
?>
<div class="item">
    <div class="imgBorder text-center">
        <a href="#" onclick="<?php echo $this->escape($selectJs); ?>">
            <div class="image">
                <img src="<?php echo $this->escape($imageUrl); ?>"
                     width="<?php echo (int) ($this->_tmp_img->width_60 ?? 60); ?>"
                     height="<?php echo (int) ($this->_tmp_img->height_60 ?? 60); ?>"
                     alt="<?php echo $this->escape($imageName . ' - ' . (string) ($this->_tmp_img->size ?? '')); ?>">
            </div>
        </a>
    </div>
    <div class="controls">
        <?php echo $this->escape((string) ($this->_tmp_img->size ?? '')); ?> -
        <a class="delete-item" href="<?php echo $this->escape($deleteUrl); ?>"
           title="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_DEL_IMG')); ?>">
            <img src="<?php echo $this->escape(Uri::root() . 'media/com_sportsmanagement/jl_images/publish_x.png'); ?>"
                 width="16" height="16"
                 alt="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_DEL_IMG')); ?>">
        </a>
    </div>
    <div class="imageinfo">
        <?php echo $this->escape(substr($imageName, 0, 10) . (strlen($imageName) > 10 ? '...' : '')); ?>
    </div>
</div>
