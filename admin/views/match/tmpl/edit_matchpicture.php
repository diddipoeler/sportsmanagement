<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage match
 * @file       edit_matchpicture.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

?>
<div class="imglist">
	<?php
	for ($i = 0, $n = count($this->images); $i < $n; $i++)
	:
		$this->setImage($i);
		?>
	   <div class="item">
	  <div align="center" class="imgBorder">
	   <a onclick="window.parent.selectImage_<?php echo $this->type; ?>('<?php echo $this->_tmp_img->name; ?>', '<?php echo $this->_tmp_img->name; ?>', '<?php echo $this->field; ?>', '<?php echo $this->fieldid; ?>');">
		<div class="image">
		 <img src="<?php echo Uri::root(); ?>/images/com_sportsmanagement/database/<?php echo $this->folder; ?>/<?php echo $this->_tmp_img->name; ?>"  width="<?php echo $this->_tmp_img->width_60; ?>" height="<?php echo $this->_tmp_img->height_60; ?>" alt="<?php echo $this->_tmp_img->name; ?> - <?php echo $this->_tmp_img->size; ?>" />
		</div>
	   </a>
	  </div>
	   <div class="controls">
		<?php echo $this->_tmp_img->size; ?> -
	  <a class="delete-item" href="index.php?option=com_sportsmanagement&amp;task=imagehandler.delete&amp;&amp;tmpl=component&amp;type=<?php echo $this->type; ?>&amp;rm[]=<?php echo $this->_tmp_img->name; ?>">
	   <img src="<?php echo Uri::root(); ?>/media/com_sportsmanagement/jl_images/publish_x.png" width="16" height="16" border="0" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_DEL_IMG'); ?>" />
	  </a>
	   </div>
	   <div class="imageinfo">
		<?php echo $this->escape(substr($this->_tmp_img->name, 0, 10) . ( strlen($this->_tmp_img->name) > 10 ? '...' : '')); ?>
	   </div>
	  </div>
		<?PHP
	endfor;
	?>
</div>
