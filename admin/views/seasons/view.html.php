<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage seasons
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewSeasons
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewSeasons extends sportsmanagementView
{
	
	
	/**
	 * sportsmanagementViewSeasons::init()
	 *
	 * @return void
	 */
	public function init()
	{

		$this->season_id = $this->jinput->getVar('id');
		$this->table = Table::getInstance('season', 'sportsmanagementTable');
		$lists       = array();

		/**
		 * build the html options for nation
		 */
		$nation[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));

		if ($res = JSMCountries::getCountryOptions())
		{
			$nation              = array_merge($nation, $res);
			$this->search_nation = $res;
		}

		$lists['nation']  = $nation;
		$lists['nation2'] = JHtmlSelect::genericlist(
			$nation,
			'filter_search_nation',
			'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
			'value',
			'text',
			$this->state->get('filter.search_nation')
		);

		$this->lists     = $lists;
//		$this->season_id = $season_id;

/** welche joomla version ? */
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
$this->document->addScriptDeclaration(
						"
$('.js-stools-btn-clear').addClass('disabled');                        
$(document).on('click','.js-stools-btn-filter', function(){
console.log('hallo filter options');
    //your code here

$('.js-stools-container-filters').toggleClass('js-stools-container-filters-visible');




 });



 $(document).on('click','.js-stools-btn-clear', function(){
console.log('hallo zurücksetzen');
    //your code here

//$('.js-stools-container-filters').removeClass('js-stools-container-filters-visible');
//this.form.submit();
Joomla.resetFilters(this);
 });



"
					);
                    
                    
if ( $this->activeFilters )
{
$this->document->addScriptDeclaration(
						"
$('.js-stools-btn-clear').removeClass('disabled');						
						");

}                    
                    
                    
                    
}                    
                    
                    
		switch ($this->getLayout())
		{
			case 'assignteams':
			case 'assignteams_3':
			case 'assignteams_4':
			$this->setLayout('assignteams');
			break;
			case 'assignpersons':
			case 'assignpersons_3':
			case 'assignpersons_4':
			$season_teams[]        = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM'));
			$res                   = $this->model->getSeasonTeams($season_id);
			$season_teams          = array_merge($season_teams, $res);
			$lists['season_teams'] = $season_teams;
			$this->lists           = $lists;
			$this->setLayout('assignpersons');
			break;
		}
//try
//{		
//$this->filterForm    = $this->model->getFilterForm();
//$this->activeFilters = $this->model->getActiveFilters();
//}
//catch (Exception $e)
//{
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');	
//}


//Factory::getApplication()->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' filterForm <pre>'.print_r($this->filterForm ,true).'</pre>'  ), ''); 
//Factory::getApplication()->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' activeFilters <pre>'.print_r($this->activeFilters ,true).'</pre>'  ), ''); 
//Factory::getApplication()->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' state <pre>'.print_r($this->state ,true).'</pre>'  ), ''); 

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		$canDo = sportsmanagementHelper::getActions();
		/** Set toolbar items for the page */
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_SEASONS_TITLE');
		if ($canDo->get('core.create'))
		{
			ToolbarHelper::addNew('season.add', 'JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit'))
		{
			ToolbarHelper::editList('season.edit', 'JTOOLBAR_EDIT');
		}
		parent::addToolbar();
	}
}
