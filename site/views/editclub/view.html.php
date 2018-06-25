<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage editclub
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');


/**
 * sportsmanagementViewEditClub
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewEditClub extends JViewLegacy
{

	/**
	 * sportsmanagementViewEditClub::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display($tpl=null)
	{
		$option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
		$uri 	= JFactory::getURI();
		$user 	= JFactory::getUser();
        $document = JFactory::getDocument();
		$model	= $this->getModel();
        $this->club = $model->getClub();

		$lists = array();

    $this->club->merge_teams = explode(",", $this->club->merge_teams);
    

		$this->form = $this->get('Form');	
		$extended = sportsmanagementHelper::getExtended($this->club->extended, 'club');
		$this->extended = $extended;
        $this->lists = $lists;

        $this->cfg_which_media_tool = JComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_media_tool',0);

		
		parent::display($tpl);	
	}

	
}
?>
