<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage smextxmleditors
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;



/**
 * sportsmanagementViewsmextxmleditors
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementViewsmextxmleditors extends sportsmanagementView
{
	/**
	 * sportsmanagementViewsmextxmleditors::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
        $this->files = $this->model->getXMLFiles();
       
       
        $this->option = $option;

	}
    
    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
        // Set toolbar items for the page
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_EDITORS');
        $this->icon = 'xml-edits';
       
        parent::addToolbar();
        
    }    
    
    
    
}
?>
