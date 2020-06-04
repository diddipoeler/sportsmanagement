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
use Joomla\CMS\Factory;
$params     = new Registry;

// Get the base version
$baseVersion = substr(JVERSION, 0, 3);
if (version_compare($baseVersion, '4.0', 'ge'))
{
//$dispatcher = Factory::getApplication()->triggerEvent();
}
elseif (version_compare($baseVersion, '3.0', 'ge'))
{
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_img, &$params));
}
//$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_img, &$params));

//echo '<pre>'.print_r($this->_tmp_img,true).'</pre>';

?>
<li class="imgOutline thumbnail height-80 width-80 center">
  <div class="height-50">
<?php

echo sportsmanagementHelper::getBootstrapModalImage(
							$this->_tmp_img->name,
							Uri::root() . 'images/com_sportsmanagement/database/'.$this->_tmp_img->path_relative.'/'.$this->_tmp_img->file,
							Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_EDIT_DETAILS'),
							$this->_tmp_img->width_60,
							'',
							$this->modalwidth,
							$this->modalheight
						);
?>
  </div>
<div class="small">
<?php
echo Text::sprintf('COM_MEDIA_IMAGE_TITLE', JHtml::_('string.truncate', $this->_tmp_img->name, 10, false), JHtml::_('number.bytes', $this->_tmp_img->size));
?>
</div>  
</li>
          
<?php
if (version_compare($baseVersion, '4.0', 'ge'))
{
//$dispatcher = Factory::getApplication()->triggerEvent();
}
elseif (version_compare($baseVersion, '3.0', 'ge'))
{
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_img, &$params));
}
