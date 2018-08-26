<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictiongroups
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementViewpredictiongroups
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewpredictiongroups extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewpredictiongroups::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
       
		$table = JTable::getInstance('predictiongroup', 'sportsmanagementTable');
		$this->table = $table;
        
        if ( !$this->items )
        {
        $this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_GROUPS'),'Error');    
        }
       		
	}
	
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{ 
        // Set toolbar items for the page
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONGROUPS_TITLE');
		$this->icon = 'predgroups';

		JToolbarHelper::addNew('predictiongroup.add');
		JToolbarHelper::editList('predictiongroup.edit');
		JToolbarHelper::custom('predictiongroup.import','upload','upload',Text::_('JTOOLBAR_UPLOAD'),false);
		JToolbarHelper::archiveList('predictiongroup.export',Text::_('JTOOLBAR_EXPORT'));
		JToolbarHelper::deleteList('','predictiongroups.delete', 'JTOOLBAR_DELETE');
		JToolbarHelper::checkin('predictiongroups.checkin');
	parent::addToolbar();
	
	}
}
?>
