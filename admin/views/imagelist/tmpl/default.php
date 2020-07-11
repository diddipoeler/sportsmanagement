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
$link = 'index.php?option=com_sportsmanagement&view=imagehandler&layout=uploaddraganddrop&type='.$this->folder.'&field=&fieldid=&tmpl=component&pid='.$this->pid.'&imagelist='.$this->imagelist;

//echo 'name: '.$this->app->getUserState("com_sportsmanagement.itemname", '0');
?>
<div class="container-fluid" id="imageslist">  
<div class="button2-left">
<div class="blank">
<?php
echo $this->app->getUserState("com_sportsmanagement.itemname", '0');
?>
</div>
<div class="blank">
<?php
echo sportsmanagementHelper::getBootstrapModalImage('upload'.$this->project_id, '', Text::_('JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE'), '20', Uri::base() . $link, $this->modalwidth , $this->modalheight );
?>
</div>

</div>
<form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars($this->uri->toString()); ?>"
<div class="display-limit">
<?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
<?php echo $this->pagination->getLimitBox(); ?>
<div class="filter-search btn-group pull-left">	
<label for="filter_search" class="element-invisible"><?php echo Text::_('JSEARCH_FILTER_LABEL'); ?></label>
                            <input type="text" name="filter_search" id="filter_search"
                                 placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>"
                                   value="<?php echo $this->escape($this->filter_search); ?>"
                                   class="hasTooltip"
                                   title="<?php echo HTMLHelper::tooltipText('JGLOBAL_LOOKING_FOR'); ?>"/>
                        </div>
                        <div class="btn-group pull-left">
                            <button type="submit" class="btn hasTooltip"
                                    title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><i
                                        class="icon-search"></i></button>
                            <button type="button" class="btn hasTooltip"
                                    title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>"
                                    onclick="document.id('filter_search').value='';this.form.submit();"><i
                                        class="icon-remove"></i></button>

                        </div>

</div>	
<?php
// Get the base version
$baseVersion = substr(JVERSION, 0, 3);
if (version_compare($baseVersion, '4.0', 'ge'))
{
?>
<div>
<div class="media-browser">	
<div class="media-browser-grid">
<div class="media-browser-items media-browser-items-md">
<?php if ( count($this->images) > 0 ) : ?>
	
		<?php for ($i = 0, $n = count($this->images); $i < $n; $i++) :
			$this->setImage($i);

include( dirname(__FILE__) . '/default_image_4.php');

		endfor; ?>
	
<?php else : ?>
	<div id="media-noimages">
		<div class="alert alert-info"><?php echo JText::_('COM_MEDIA_NO_IMAGES_FOUND'); ?></div>
	</div>
<?php endif; ?>
</div>
</div>
</div>
</div>
<?php
}
elseif (version_compare($baseVersion, '3.0', 'ge'))
{

?>
<div class="row-fluid" id="showimages">   
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
</div>
<?php
}

?>
<div class="row-fluid" id="imageslistpagination">   
<div class="pagination">
    <p class="counter">
		<?php echo $this->pagination->getPagesCounter(); ?>
    </p>
    <p class="counter">
		<?php echo $this->pagination->getResultsCounter(); ?>
    </p>
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>
  </div>
   <input type="hidden" name="limitstart" value=""/>
  <input type="hidden" name="option" value="com_sportsmanagement"/>
  
  
            <input type="hidden" name="view" value="imagelist"/>
            <input type="hidden" name="imagelist" value="1"/>
            <input type="hidden" name="asset" value="com_sportsmanagement"/>
            <input type="hidden" name="folder" value="<?php echo $this->folder;?>"/>
<input type="hidden" name="pid" value="<?php echo $this->pid ;?>"/>
<input type="hidden" name="mid" value="<?php echo $this->match_id ;?>"/>
            <input type="hidden" name="author" value=""/>
            <input type="hidden" name="fieldid" value="<?php echo $this->fieldid;?>"/>
<input type="hidden" name="type" value="<?php echo $this->type;?>"/>
<input type="hidden" name="fieldname" value="<?php echo $this->fieldname;?>"/>
            <input type="hidden" name="tmpl" value="component"/>
  
  
</form>
  </div>
