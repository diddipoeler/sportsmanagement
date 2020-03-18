<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;

jimport('joomla.html.parameter.element.timezones');

require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'sportstypes.php');
require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'leagues.php');

/**
 * sportsmanagementViewProject
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewProject extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewProject::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		
		$tpl = '';
		$starttime = microtime(); 
        $lists = array();
        
		if ( $this->getLayout() == 'panel' || $this->getLayout() == 'panel_3' || $this->getLayout() == 'panel_4' )
		{
			$this->_displayPanel($tpl);
			return;
		}
        
        Factory::getApplication()->input->setVar('hidemainmenu', true);
       
        $this->form->setValue('sports_type_id', 'request', $this->item->sports_type_id);
        $this->form->setValue('agegroup_id', 'request', $this->item->agegroup_id);
        
        $extended = sportsmanagementHelper::getExtended($this->item->extended, 'project');		
		$this->extended	= $extended;
        
        $extendeduser = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'project');		
		$this->extendeduser	= $extendeduser;

        $isNew = $this->item->id == 0;
        if ( $isNew )
        {
            $this->form->setValue('start_date', null, date("Y-m-d"));
            $this->form->setValue('start_time', null, '18:00');
            $this->form->setValue('admin', null, $this->user->id);
            $this->form->setValue('editor', null, $this->user->id);
        }
        
        $this->checkextrafields	= sportsmanagementHelper::checkUserExtraFields();
		if ( $this->checkextrafields )
		{
			if ( !$isNew )
			{
				$lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($this->item->id);
            }
        }
        
        $this->form->setValue('fav_team', null, explode(',',$this->item->fav_team) );
        
        $this->lists	= $lists;
 

	}
	
	
    
	/**
	 * sportsmanagementViewProject::_displayPanel()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function _displayPanel($tpl)
	{
    $starttime = microtime();
           
	$this->item = $this->get('Item');
   
	$iProjectDivisionsCount = 0;
	$mdlProjectDivisions = BaseDatabaseModel::getInstance("divisions", "sportsmanagementModel");
	$iProjectDivisionsCount = $mdlProjectDivisions->getProjectDivisionsCount($this->item->id);
	
	if ( $this->item->project_art_id != 3 )
	{
		$iProjectPositionsCount = 0;
		$mdlProjectPositions = BaseDatabaseModel::getInstance('Projectpositions', 'sportsmanagementModel');
/**
 *     sind im projekt keine positionen vorhanden, dann
 *     bitte einmal die standard positionen, torwart, abwehr,
 *     mittelfeld und stürmer einfügen
 */
    $iProjectPositionsCount = $mdlProjectPositions->getProjectPositionsCount($this->item->id);
    if ( !$iProjectPositionsCount )
	{
		$mdlProjectPositions->insertStandardProjectPositions($this->item->id,$this->item->sports_type_id); 
	}

	}
    	
	$iProjectRefereesCount = 0;
	$mdlProjectReferees = BaseDatabaseModel::getInstance('Projectreferees', 'sportsmanagementModel');
	$iProjectRefereesCount = $mdlProjectReferees->getProjectRefereesCount($this->item->id);
		
	$iProjectTeamsCount = 0;
	$mdlProjecteams = BaseDatabaseModel::getInstance('Projectteams', 'sportsmanagementModel');
	$iProjectTeamsCount = $mdlProjecteams->getProjectTeamsCount($this->item->id);
		
	$iMatchDaysCount = 0;
	$mdlRounds = BaseDatabaseModel::getInstance("Rounds", "sportsmanagementModel");
	$iMatchDaysCount = $mdlRounds->getRoundsCount($this->item->id);
		
	$this->project	= $this->item;
	$this->count_projectdivisions	= $iProjectDivisionsCount;
	$this->count_projectpositions	= $iProjectPositionsCount;
	$this->count_projectreferees	= $iProjectRefereesCount;
	$this->count_projectteams	= $iProjectTeamsCount;
	$this->count_matchdays	= $iMatchDaysCount;
    
    // store the variable that we would like to keep for next time
    // function syntax is setUserState( $key, $value );
    $this->app->setUserState( "$this->option.pid", $this->item->id);
    $this->app->setUserState( "$this->option.season_id", $this->item->season_id);
    $this->app->setUserState( "$this->option.project_art_id", $this->item->project_art_id);
    $this->app->setUserState( "$this->option.sports_type_id", $this->item->sports_type_id);

    }
       
    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
    
        $isNew = $this->item->id ? $this->title = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_EDIT',$this->project->name) : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_ADD_NEW');
        $this->icon = 'project';
   
        $bar = Toolbar::getInstance('toolbar');
        switch ( ComponentHelper::getParams($this->option)->get('which_article_component') )
    {
        case 'com_content':
        $bar->appendButton('Link', 'featured', 'Kategorie', 'index.php?option=com_categories&extension=com_content');
        break;
        case 'com_k2':
        $bar->appendButton('Link', 'featured', 'Kategorie', 'index.php?option=com_k2&view=categories');
        break;
    }
        
        parent::addToolbar();
	}
    
    
    
}
?>
