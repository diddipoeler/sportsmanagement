<?php

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

$params     = new Registry;
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_img, &$params));

echo '<pre>'.print_r($this->_tmp_img,true).'</pre>';

?>

<li class="imgOutline thumbnail height-80 width-80 center">
	<a class="img-preview" href="javascript:ImageManager.populateFields('<?php echo $this->_tmp_img->['path_relative']; ?>')" title="<?php echo $this->_tmp_img->['name']; ?>" >
		<div class="height-50">
			<?php echo JHtml::_('image', $this->baseURL . '/' . $this->_tmp_img->['path_relative'], JText::sprintf('COM_MEDIA_IMAGE_TITLE', $this->_tmp_img->['name'], JHtml::_('number.bytes', $this->_tmp_img->['size'])), array('width' => $this->_tmp_img->['width_60'], 'height' => $this->_tmp_img->['height_60'])); ?>
		</div>
		<div class="small">
			<?php echo JHtml::_('string.truncate', $this->_tmp_img->['name'], 10, false); ?>
		</div>
	</a>
</li>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_img, &$params));
