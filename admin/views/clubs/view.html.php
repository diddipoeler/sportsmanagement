<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage clubs
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');

/**
 * sportsmanagementViewClubs
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewClubs extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewClubs::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
	
        $my_text = '';
        
              
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text .= ' <br><pre>'.print_r($this->state,true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->state,true).'</pre>'),'');
        }
        
        $starttime = microtime(); 
        $inputappend = '';


        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
	
        
		$this->table = JTable::getInstance('club', 'sportsmanagementTable');
        
        //build the html select list for seasons
		$seasons[]	= JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'), 'id', 'name');
        $mdlSeasons = JModelLegacy::getInstance('Seasons', 'sportsmanagementModel');
		$allSeasons = $mdlSeasons->getSeasons();
		$seasons = array_merge($seasons, $allSeasons);
        $this->season = $allSeasons;
		$lists['seasons'] = JHtml::_( 'select.genericList',
									$seasons,
									'filter_season',
									'class="inputbox" onChange="this.form.submit();" style="width:120px"',
									'id',
									'name',
									$this->state->get('filter.season'));

		unset($seasons);

//		// state filter
//		$lists['state'] = JHtml::_('grid.state',$filter_state);
        
        //build the html options for nation
		$nation[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ($res = JSMCountries::getCountryOptions())
        {
            $nation = array_merge($nation, $res);
            $this->search_nation = $res;
            }
		
		$lists['nation']	= $nation;
		$lists['nation2']	= JHtmlSelect::genericlist(	$nation,
									'filter_search_nation',
									$inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
									'value',
									'text',
									$this->state->get('filter.search_nation'));

		$this->lists		= $lists;

        
	}
	
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
        // Set toolbar items for the page
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_TITLE');
        JToolbarHelper::apply('clubs.saveshort');
        
        JToolbarHelper::divider();
		JToolbarHelper::addNew('club.add');
		JToolbarHelper::editList('club.edit');
		JToolbarHelper::custom('club.import', 'upload', 'upload', JText::_('JTOOLBAR_UPLOAD'), false);
		JToolbarHelper::archiveList('club.export',JText::_('JTOOLBAR_EXPORT'));
        parent::addToolbar();
		
	}
}
?>
