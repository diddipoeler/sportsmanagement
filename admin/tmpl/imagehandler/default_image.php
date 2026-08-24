<?php
/** One image in the SportsManagement administrator selector. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$imageName = (string) ($this->_tmp_img->name ?? '');
$imageUrl  = Uri::root() . 'images/com_sportsmanagement/database/'
    . trim((string) $this->folder, '/') . '/' . rawurlencode($imageName);
$selectFunction = 'selectImage_' . (string) $this->type;
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
?>
<div class="card p-2 text-center" style="width: 9rem;">
    <a href="#" onclick="<?php echo $this->escape($selectJs); ?>">
        <img src="<?php echo $this->escape($imageUrl); ?>"
             width="<?php echo (int) ($this->_tmp_img->width_60 ?? 60); ?>"
             height="<?php echo (int) ($this->_tmp_img->height_60 ?? 60); ?>"
             alt="<?php echo $this->escape($imageName); ?>">
    </a>
    <div class="small mt-2"><?php echo $this->escape((string) ($this->_tmp_img->size ?? '')); ?></div>
    <div class="small text-break"><?php echo $this->escape($imageName); ?></div>
    <button type="submit" class="btn btn-sm btn-danger mt-2" name="rm[]"
            value="<?php echo $this->escape($imageName); ?>"
            onclick="this.form.task.value='imagehandler.delete';">
        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_DEL_IMG'); ?>
    </button>
</div>
