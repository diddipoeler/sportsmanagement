<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage team
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewTeam
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewTeam extends sportsmanagementView
{
	

	/**
	 * sportsmanagementViewTeam::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		
		$starttime = microtime();
        $lists = array();

		$this->change_training_date	= $this->app->getUserState( "$this->option.change_training_date", '0' );
        
		if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
		{
		$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
        
		if ( empty($this->item->id) )
		{
            $this->form->setValue('club_id', null, $this->app->getUserState( "$this->option.club_id", '0' ));
            $this->item->club_id = $this->app->getUserState( "$this->option.club_id", '0' );
		}
        

		$extended = sportsmanagementHelper::getExtended($this->item->extended, 'team');
		$this->extended = $extended;
        $extendeduser = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'team');		
		$this->extendeduser = $extendeduser;
        
        $this->assign( 'checkextrafields', sportsmanagementHelper::checkUserExtraFields() );
		if ( $this->checkextrafields )
		{
			if ( $this->item->id )
			{
			$lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($this->item->id);
			}
		}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_id<br><pre>'.print_r($this->item->club_id,true).'</pre>'),'');
        }
        
        //build the html select list for days of week
		if ($trainingData = $this->model->getTrainigData($this->item->id))
		{
			$daysOfWeek = array( 0 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'), 
			1 => JText::_('MONDAY'), 
			2 => JText::_('TUESDAY'), 
			3 => JText::_('WEDNESDAY'), 
			4 => JText::_('THURSDAY'), 
			5 => JText::_('FRIDAY'), 
			6 => JText::_('SATURDAY'), 
			7 => JText::_('SUNDAY') );
			$dwOptions = array();
			foreach($daysOfWeek AS $key => $value)
			{
				$dwOptions[]=JHtml::_('select.option',$key,$value);
			}
			foreach ($trainingData AS $td)
			{
				$lists['dayOfWeek'][$td->id]=JHtml::_('select.genericlist',$dwOptions,'dayofweek['.$td->id.']','class="inputbox"','value','text',$td->dayofweek);
			}
			unset($daysOfWeek);
			unset($dwOptions);
		}
        $this->trainingData = $trainingData;
        $this->lists = $lists;
        
	}
 
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{

		JFactory::getApplication()->input->set('hidemainmenu', true);
        $isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAM_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAM_ADD_NEW');
        $this->icon = 'team';

parent::addToolbar();

	}
    

}
