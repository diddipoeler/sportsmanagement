<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jlextfederations
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewjlextfederations
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewjlextfederations extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewjlextfederations::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' state<br><pre>'.print_r($this->state,true).'</pre>'),'Notice');	
$starttime = microtime(); 
		
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$this->table = JTable::getInstance('jlextfederation', 'sportsmanagementTable');
        
        //build the html options for nation
		$nation[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ($res = JSMCountries::getCountryOptions())
        {
            $nation = array_merge($nation,$res);
            $this->search_nation = $res;
            }
		
        $lists['nation'] = $nation;
        $lists['nation2'] = JHtmlSelect::genericlist($nation,
						'filter_search_nation',
						'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
						'value',
						'text',
						$this->state->get('filter.search_nation'));

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
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_FEDERATIONS_TITLE');
        $this->icon = 'federations';

		JToolbarHelper::addNew('jlextfederation.add');
		JToolbarHelper::editList('jlextfederation.edit');
		JToolbarHelper::custom('jlextfederation.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
		JToolbarHelper::archiveList('jlextfederation.export',JText::_('JTOOLBAR_EXPORT'));
        
        parent::addToolbar();
	}
    
    

}
?>