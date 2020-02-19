<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage allprojects
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
if (! defined('JSM_PATH'))
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

// prüft vor Benutzung ob die gewünschte Klasse definiert ist
if ( !class_exists('sportsmanagementHelperHtml') ) 
{
//add the classes for handling
$classpath = JPATH_SITE.DIRECTORY_SEPARATOR.JSM_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'html.php';
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
        
		$user		= Factory::getUser();
        $starttime = microtime(); 
        $inputappend = '';
        $this->tableclass = $this->jinput->getVar('table_class', 'table','request','string');

		$this->state 		= $this->get('State');
		$this->items 		= $this->get('Items');
       
		$this->pagination	= $this->get('Pagination');
		
        //build the html options for nation
		$temp[] = HTMLHelper::_('select.option','0',Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ($res = JSMCountries::getCountryOptions())
        {
            $temp = array_merge($temp,$res);
            }
		
        $lists['nation2'] = JHtmlSelect::genericlist(	$temp,
																'filter_search_nation',
																$inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
																'value',
																'text',
																$this->state->get('filter.search_nation'));
                                                                
        unset($temp);
        
        $temp[] = HTMLHelper::_('select.option','',Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_LEAGUES'),'id','name' );
        $modeltemp = BaseDatabaseModel::getInstance("Leagues", "sportsmanagementModel");
		if ($res = $modeltemp->getLeagues())
        {
            $temp = array_merge($temp,$res);
            }
		
        $lists['leagues'] = JHtmlSelect::genericlist(	$temp,
																'filter_search_leagues',
																$inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
																'id',
																'name',
																$this->state->get('filter.search_leagues'));
                                                                
        unset($temp);
        
        $temp[] = HTMLHelper::_('select.option','',Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_SEASONS'),'id','name' );
        $modeltemp = BaseDatabaseModel::getInstance("Seasons", "sportsmanagementModel");
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
		$this->document->setTitle(Text::_('COM_SPORTSMANAGEMENT_ALLPROJECTS_PAGE_TITLE'));
        
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