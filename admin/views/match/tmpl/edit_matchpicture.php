<?php
/**
 * SportsManagement match image selector for Joomla 5/6.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage match
 * @file       edit_matchpicture.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2026 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$imageBase = rtrim(Uri::root(), '/') . '/images/com_sportsmanagement/database/';
$deleteIcon = rtrim(Uri::root(), '/') . '/media/com_sportsmanagement/jl_images/publish_x.png';

$this->getDocument()->getWebAssetManager()->registerAndUseScript(
    'com_sportsmanagement.match-image-selector',
    'administrator/components/com_sportsmanagement/assets/js/match-image-selector.js'
);
?>
<div class="imglist">
    <?php for ($i = 0, $n = count($this->images); $i < $n; $i++) : ?>
        <?php
        $this->setImage($i);
        $imageName = (string) ($this->_tmp_img->name ?? '');
        $imageSize = (string) ($this->_tmp_img->size ?? '');
        $folder = trim((string) $this->folder, '/');
        $encodedFolder = implode(
            '/',
            array_map('rawurlencode', array_values(array_filter(explode('/', $folder), static fn ($part): bool => $part !== '')))
        );
        $imageUrl = $imageBase . ($encodedFolder !== '' ? $encodedFolder . '/' : '') . rawurlencode($imageName);
        $deleteUrl = Route::_(
            'index.php?option=com_sportsmanagement&task=imagehandler.delete&tmpl=component&type=' . rawurlencode((string) $this->type)
            . '&rm[]=' . rawurlencode($imageName)
        );
        ?>
        <div class="item">
            <div class="imgBorder text-center">
                <a
                    href="#"
                    data-jsm-select-image
                    data-image-type="<?php echo $escape($this->type); ?>"
                    data-image-name="<?php echo $escape($imageName); ?>"
                    data-image-field="<?php echo $escape($this->field); ?>"
                    data-image-field-id="<?php echo $escape($this->fieldid); ?>"
                >
                    <div class="image">
                        <img
                            src="<?php echo $escape($imageUrl); ?>"
                            width="<?php echo (int) ($this->_tmp_img->width_60 ?? 0); ?>"
                            height="<?php echo (int) ($this->_tmp_img->height_60 ?? 0); ?>"
                            alt="<?php echo $escape($imageName . ' - ' . $imageSize); ?>"
                            loading="lazy"
                        >
                    </div>
                </a>
            </div>
            <div class="controls">
                <?php echo $escape($imageSize); ?> -
                <a class="delete-item" href="<?php echo $escape($deleteUrl); ?>">
                    <img
                        src="<?php echo $escape($deleteIcon); ?>"
                        width="16"
                        height="16"
                        alt="<?php echo $escape(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_DEL_IMG')); ?>"
                    >
                </a>
            </div>
            <div class="imageinfo">
                <?php echo $escape(substr($imageName, 0, 10) . (strlen($imageName) > 10 ? '...' : '')); ?>
            </div>
        </div>
    <?php endfor; ?>
</div>
