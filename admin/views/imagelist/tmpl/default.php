<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage imagelist
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Log\Log;
$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
//$modalheight = ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('modal_popup_height', 600);
//$modalwidth  = ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('modal_popup_width', 900);
$link = 'index.php?option=com_sportsmanagement&view=imagehandler&layout=uploaddraganddrop&type='.$this->folder.'&field=&fieldid=&tmpl=component&pid='.$this->pid.'&imagelist='.$this->imagelist;
//JHtml::_('script', 'media/popup-imagemanager.min.js', array('version' => 'auto', 'relative' => true));
//JHtml::_('stylesheet', 'media/popup-imagemanager.css', array('version' => 'auto', 'relative' => true));

//echo '<pre>'.print_r($this->uri,true).'</pre>';


//echo '<pre>'.print_r($this->images,true).'</pre>';
?>
<div class="container-fluid" id="allleagues">  
<div class="button2-left"><div class="blank">
<?php
echo sportsmanagementHelper::getBootstrapModalImage('upload'.$this->project_id, '', Text::_('JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE'), '20', Uri::base() . $link, $this->modalwidth , $this->modalheight );
?>
</div></div>
<form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars($this->uri->toString()); ?>"
<?php if ( count($this->images) > 0 ) : ?>
	<ul class="manager thumbnails">
		<?php for ($i = 0, $n = count($this->images); $i < $n; $i++) :
			$this->setImage($i);
			include( dirname(__FILE__) . '/default_image.php');
		endfor; ?>
	</ul>
<?php else : ?>
	<div id="media-noimages">
		<div class="alert alert-info"><?php echo JText::_('COM_MEDIA_NO_IMAGES_FOUND'); ?></div>
	</div>
<?php endif; ?>
<div class="pagination">
    <p class="counter">
		<?php echo $this->pagination->getPagesCounter(); ?>
    </p>
    <p class="counter">
		<?php echo $this->pagination->getResultsCounter(); ?>
    </p>
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>
   <input type="hidden" name="limitstart" value=""/>
  <input type="hidden" name="option" value="com_sportsmanagement"/>
  
  
            <input type="hidden" name="view" value="imagelist"/>
            <input type="hidden" name="imagelist" value="1"/>
            <input type="hidden" name="asset" value="com_sportsmanagement"/>
            <input type="hidden" name="folder" value="clubs/large"/>
            <input type="hidden" name="author" value=""/>
            <input type="hidden" name="fieldid" value="jform_logo_big"/>
            <input type="hidden" name="tmpl" value="component"/>
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
</form>
  </div>
