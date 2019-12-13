<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage smimageimports
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewsmimageimports
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewsmimageimports extends sportsmanagementView
{
	/**
	 * sportsmanagementViewsmimageimports::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	public function init ()
	{
		$checkimages = $this->model->getimagesxml();
		$this->files = $this->model->getXMLFiles();
        
        //build the html select list
		$folders[] = HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_FOLDER'),'id','name');
        $allfolders = $this->model->getXMLFolder();
		$folders = array_merge($folders,$allfolders);
		$lists['folders'] = HTMLHelper::_( 'select.genericList', 
										$folders, 
										'filter_image_folder', 
										'class="inputbox" onChange="this.form.submit();" style="width:220px"', 
										'id', 
										'name', 
										$this->state->get('filter.image_folder'));
                                       
		//$items = $this->get('Items');
		//$total = $this->get('Total');
		//$pagination = $this->get('Pagination');

		//$this->option	= $option;
        
		$this->lists	= $lists;
		//$this->items	= $items;
		//$this->pagination	= $pagination;
		//$this->request_url	= $uri->toString();
        
//        $this->addToolbar();
//		parent::display($tpl);
	}
    
    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{

		$jinput = Factory::getApplication()->input;
        // Set toolbar items for the page
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGES_IMPORT');
		$this->icon = 'images-import';
		ToolbarHelper::custom('smimageimports.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
		ToolbarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
		ToolbarHelper::preferences($jinput->getCmd('option'));
        
    }    
    
    
    
}
?>
