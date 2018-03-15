<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage division
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );




/**
 * sportsmanagementViewDivision
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewDivision extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewDivision::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		
        $starttime = microtime(); 
		$lists = array();

        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

        //$this->project_id = $this->app->getUserState( "$this->option.pid", '0' );
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        $this->project = $project;
$count_teams = $this->model->count_teams_division($this->item->id);
$extended = sportsmanagementHelper::getExtended($this->item->rankingparams, 'division');
$this->extended = $extended;
$this->extended->setFieldAttribute('rankingparams', 'rankingteams' , $count_teams);
		
		
/*		
        if ( !$this->item->rankingparams )
        {
        $count_teams = $this->model->count_teams_division($this->item->id);
        //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($count_teams,true).'</pre>'),'Notice');
        
for($a=0; $a < $count_teams; $a++ )
{
$colors_ranking[$a]['von'] = '';
$colors_ranking[$a]['bis'] = '';
$colors_ranking[$a]['color'] = '';
$colors_ranking[$a]['text'] = ''; 
}
$this->form->setValue('rankingparams', null, $colors_ranking);
*/
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->form,true).'</pre>'),'Notice');
        
        
        
        //}
	
	}

	
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{	
	$app	= JFactory::getApplication();
	$jinput	= $app->input;
	$jinput->set('hidemainmenu', true);
	$jinput->set('pid', $this->project_id);

    $isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_DIVISIONS_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_DIVISIONS_NEW');
    $this->icon = 'division';
    
    parent::addToolbar();
	}	
    
    

}
?>
