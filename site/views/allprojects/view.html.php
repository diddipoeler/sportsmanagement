<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage allprojects
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

if (! defined('JSM_PATH'))
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

// prüft vor Benutzung ob die gewünschte Klasse definiert ist
if ( !class_exists('sportsmanagementHelperHtml') ) 
{
//add the classes for handling
$classpath = JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'html.php';
JLoader::register('sportsmanagementHelperHtml', $classpath);
}

/**
 * sportsmanagementViewallprojects
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewallprojects extends sportsmanagementView
{
    protected $state = null;
	protected $item = null;
	protected $items = null;
	protected $pagination = null;
    
	
	/**
	 * sportsmanagementViewallprojects::init()
	 * 
	 * @return void
	 */
	function init()
	{
        
		$user		= JFactory::getUser();
        $starttime = microtime(); 
        $inputappend = '';
        $this->tableclass = $this->jinput->getVar('table_class', 'table','request','string');

		$this->state 		= $this->get('State');
		$this->items 		= $this->get('Items');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$this->pagination	= $this->get('Pagination');
		
        //build the html options for nation
		$temp[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ($res = JSMCountries::getCountryOptions())
        {
            $temp = array_merge($temp,$res);
            }
		
        //$lists['nation'] = $temp;
        $lists['nation2'] = JHtmlSelect::genericlist(	$temp,
																'filter_search_nation',
																$inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
																'value',
																'text',
																$this->state->get('filter.search_nation'));
                                                                
        unset($temp);
        
        $temp[] = JHtml::_('select.option','',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_LEAGUES'),'id','name' );
        $modeltemp = JModelLegacy::getInstance("Leagues", "sportsmanagementModel");
		if ($res = $modeltemp->getLeagues())
        {
            $temp = array_merge($temp,$res);
            }
		
        //$lists['nation'] = $temp;
        $lists['leagues'] = JHtmlSelect::genericlist(	$temp,
																'filter_search_leagues',
																$inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
																'id',
																'name',
																$this->state->get('filter.search_leagues'));
                                                                
        unset($temp);
        
        $temp[] = JHtml::_('select.option','',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_SEASONS'),'id','name' );
        $modeltemp = JModelLegacy::getInstance("Seasons", "sportsmanagementModel");
		if ($res = $modeltemp->getSeasons())
        {
            $temp = array_merge($temp,$res);
            }
		
        //$lists['nation'] = $temp;
        $lists['seasons'] = JHtmlSelect::genericlist(	$temp,
																'filter_search_seasons',
																$inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
																'id',
																'name',
																$this->state->get('filter.search_seasons'));
                                                                
        unset($temp);
        
        // Set page title
		$this->document->setTitle(JText::_('COM_SPORTSMANAGEMENT_ALLPROJECTS_PAGE_TITLE'));
        
        $form = new stdClass();
        $form->limitField = $this->pagination->getLimitBox();
        $this->filter = $this->state->get('filter.search');
		$this->form = $form;
		$this->user = $user;
        $this->sortDirection = $this->state->get('filter_order_Dir');
        $this->sortColumn = $this->state->get('filter_order');
        $this->lists = $lists;

	}

}
?>