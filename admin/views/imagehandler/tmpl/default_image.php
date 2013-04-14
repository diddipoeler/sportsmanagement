<?php
/**
* @version    $Id: default_image.php 4905 2010-01-30 08:51:33Z and_one $ 
* @package    JoomlaTracks
* @copyright  Copyright (C) 2008 Julien Vonthron. All rights reserved.
* @license    GNU/GPL, see LICENSE.php
* Joomla Tracks is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
// no direct access

defined('_JEXEC') or die('Restricted access');
?>
		<div class="item">
				<div align="center" class="imgBorder">
					<a onclick="window.parent.selectImage_<?php echo $this->type; ?>('<?php echo $this->_tmp_img->name; ?>', '<?php echo $this->_tmp_img->name; ?>', '<?php echo $this->field; ?>', '<?php echo $this->fieldid; ?>');">
						<div class="image">
							<img src="<?php echo JURI::root(); ?>/images/com_sportsmanagement/database/<?php echo $this->folder; ?>/<?php echo $this->_tmp_img->name; ?>"  width="<?php echo $this->_tmp_img->width_60; ?>" height="<?php echo $this->_tmp_img->height_60; ?>" alt="<?php echo $this->_tmp_img->name; ?> - <?php echo $this->_tmp_img->size; ?>" />
						</div>
					</a>
				</div>
			<div class="controls">
				<?php echo $this->_tmp_img->size; ?> -
				<a class="delete-item" href="index.php?option=com_sportsmanagement&amp;task=imagehandler.delete&amp;&amp;tmpl=component&amp;type=<?php echo $this->type; ?>&amp;rm[]=<?php echo $this->_tmp_img->name; ?>">
					<img src="<?php echo JURI::root(); ?>/media/com_sportsmanagement/jl_images/publish_x.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_DEL_IMG' ); ?>" />
				</a>
			</div>
			<div class="imageinfo">
				<?php echo $this->escape( substr( $this->_tmp_img->name, 0, 10 ) . ( strlen( $this->_tmp_img->name ) > 10 ? '...' : '')); ?>
			</div>
		</div>