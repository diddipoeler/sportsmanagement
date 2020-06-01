<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage imagelist
 * @file       default_imagelist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
$params     = new Registry;
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_img, &$params));

//echo '<pre>'.print_r($this->_tmp_img,true).'</pre>';





echo sportsmanagementHelper::getBootstrapModalImage(
							$this->_tmp_img->name,
							Uri::root() . $this->_tmp_img->path_relative.'/'.$this->_tmp_img->file,
							Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_EDIT_DETAILS'),
							$this->_tmp_img->width_60,
							'',
							$this->modalwidth,
							$this->modalheight
						);
?>








<li class="imgOutline thumbnail height-80 width-80 center">
	<a class="img-preview" href="" >
		<div class="height-50">
			<?php echo JHtml::_('image', $this->_tmp_img->path_relative.'/'.$this->_tmp_img->file, Text::sprintf('COM_MEDIA_IMAGE_TITLE', $this->_tmp_img->name, JHtml::_('number.bytes', $this->_tmp_img->size)), array('width' => $this->_tmp_img->width_60, 'height' => $this->_tmp_img->height_60)); ?>
		</div>
		<div class="small">
			<?php echo JHtml::_('string.truncate', $this->_tmp_img->name, 10, false); ?>
		</div>
	</a>
</li>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_img, &$params));
