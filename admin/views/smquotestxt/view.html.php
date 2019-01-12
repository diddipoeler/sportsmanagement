<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage smquotestxt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * sportsmanagementViewsmquotestxt
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewsmquotestxt extends sportsmanagementView
{
	/**
	 * sportsmanagementViewsmquotestxt::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$model = $this->getModel();
		$this->assign('files',$model->getTXTFiles());
      
        $this->option = $option;

	}
    
    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
//		// Get a refrence of the page instance in joomla
//		$document	= Factory::getDocument();
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
//        
//        // Set toolbar items for the page
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TXT_EDITORS');
//		sportsmanagementHelper::ToolbarButtonOnlineHelp();
//        JToolbarHelper::preferences(Factory::getApplication()->input->getCmd('option'));
        
        parent::addToolbar();
    }    
    
    
    
}
?>
