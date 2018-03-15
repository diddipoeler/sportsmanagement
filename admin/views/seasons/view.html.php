<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage seasons
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewSeasons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewSeasons extends sportsmanagementView
{

	/**
	 * sportsmanagementViewSeasons::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
		
        $season_id = $this->jinput->getVar('id');
		
$starttime = microtime(); 

        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        

        

		$this->table = JTable::getInstance('season', 'sportsmanagementTable');
        
        //build the html options for nation
		$nation[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ( $res = JSMCountries::getCountryOptions() )
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



		
		$this->lists = $lists;
        $this->season_id = $season_id;
        
        if ( $this->getLayout() == 'assignteams' || $this->getLayout() == 'assignteams_3' || $this->getLayout() == 'assignteams_4')
		{
		$this->setLayout('assignteams');  
        }  
        
        if ( $this->getLayout() == 'assignpersons' || $this->getLayout() == 'assignpersons_3' || $this->getLayout() == 'assignpersons_4')
		{
		$season_teams[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM'));
        $res = $this->model->getSeasonTeams($season_id); 
        $season_teams = array_merge($season_teams,$res); 
        $lists['season_teams'] = $season_teams;
        $this->lists	= $lists;
		$this->setLayout('assignpersons');  
        }
        
		
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
               
        $canDo = sportsmanagementHelper::getActions();
    // Set toolbar items for the page
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_SEASONS_TITLE');
		if ($canDo->get('core.create')) 
		{
			JToolbarHelper::addNew('season.add', 'JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit')) 
		{
			JToolbarHelper::editList('season.edit', 'JTOOLBAR_EDIT');
		}
//		if ($canDo->get('core.delete')) 
//		{
//			if ( COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE )
//            {
//		    JToolbarHelper::trash('seasons.trash');
//            }
//            else
//            {
//            JToolbarHelper::trash('seasons.trash');
//            JToolbarHelper::deleteList('', 'seasons.delete', 'JTOOLBAR_DELETE');    
//            }
//            
//		}

        parent::addToolbar();
        
		
	}
}
?>
