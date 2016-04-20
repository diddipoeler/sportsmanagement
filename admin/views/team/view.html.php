<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
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
        //$show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        

		$this->change_training_date	= $this->app->getUserState( "$this->option.change_training_date", '0' );
        //$this->club_id = $this->jinput->get('club_id');
        //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_id<br><pre>'.print_r($this->club_id,true).'</pre>'),'');
        //$this->app->enqueueMessage(__METHOD__.' '.__LINE__.'jinput <br><pre>'.print_r($this->jinput, true).'</pre><br>','Notice');
        
//        $post = $this->jinput->post->getArray();
//        $this->app->enqueueMessage(__METHOD__.' '.__LINE__.'post <br><pre>'.print_r($post, true).'</pre><br>','Notice');

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' change_training_date<br><pre>'.print_r($this->change_training_date,true).'</pre>'),'');
        
        
        
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
	
        
        //$this->item->club_id = $this->app->getUserState( "$option.club_id", '0' );
        
		if ( empty($this->item->id) )
		{
			//$foo = $this->jinput->get->get('club_id');
            //$this->form->setValue('club_id', null, $this->jinput->get->get('club_id'));
            $this->form->setValue('club_id', null, $this->app->getUserState( "$this->option.club_id", '0' ));
            $this->item->club_id = $this->app->getUserState( "$this->option.club_id", '0' );
		}
        
        //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' item club_id<br><pre>'.print_r($this->item->club_id,true).'</pre>'),'');
        
		//$this->item->season_ids = explode(",", $this->item->season_ids);
        //$this->app->enqueueMessage(JText::_('sportsmanagementViewTeam display season_ids<br><pre>'.print_r($this->item->season_ids,true).'</pre>'),'Notice');
        
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
