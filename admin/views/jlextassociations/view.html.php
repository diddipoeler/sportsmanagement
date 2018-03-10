<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jlextassociastions
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewjlextassociations
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewjlextassociations extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewjlextassociations::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
   
$starttime = microtime(); 
		
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
		
        
        $this->table = JTable::getInstance('jlextassociation', 'sportsmanagementTable');

        
        //build the html options for nation
		$nation[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ($res = JSMCountries::getCountryOptions())
        {
            $nation = array_merge($nation, $res);
            $this->search_nation = $res;
            }
		
        $lists['nation'] = $nation;
        $lists['nation2'] = JHtmlSelect::genericlist($nation,
						'filter_search_nation',
						'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
						'value',
						'text',
						$this->state->get('filter.search_nation'));

$mdlassociation = JModelLegacy::getInstance('jlextassociations', 'sportsmanagementModel');
        
        if ( $res = $mdlassociation->getAssociations() )
        {
            $nation = array_merge($nation, $res);
            $this->federation = $res;
        }

		$this->lists = $lists;
  

	}
    
    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
	// Set toolbar items for the page
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_TITLE');

		JToolbarHelper::addNew('jlextassociation.add');
		JToolbarHelper::editList('jlextassociation.edit');
		JToolbarHelper::custom('jlextassociation.import', 'upload', 'upload', JText::_('JTOOLBAR_UPLOAD'), false);
		JToolbarHelper::archiveList('jlextassociation.export', JText::_('JTOOLBAR_EXPORT'));
        		
        parent::addToolbar();
	}
    
    

}
?>
