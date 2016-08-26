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
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
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
 
// import Joomla modelform library
//jimport('joomla.application.component.modeladmin');
 

/**
 * sportsmanagementModeldivision
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModeldivision extends JSMModelAdmin
{
    
    	/**
    	 * sportsmanagementModeldivision::saveshort()
    	 * 
    	 * @return
    	 */
    	public function saveshort()
	{
		// Reference global application object
        $app = JFactory::getApplication();
        $date = JFactory::getDate();
	   $user = JFactory::getUser();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        
        //$show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        
        // Get the input
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        if ( !$pks )
        {
            return JText::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_SAVE_NO_SELECT');
        }
        $post = JRequest::get('post');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $app->enqueueMessage(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
        $app->enqueueMessage(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($post, true).'</pre><br>','Notice');
        }
        
        //$result=true;
		for ($x=0; $x < count($pks); $x++)
		{
			$tblRound = & $this->getTable();
			$tblRound->id = $pks[$x];
			$tblRound->name	= $post['name'.$pks[$x]];
            
            $tblRound->alias = JFilterOutput::stringURLSafe( $post['name'.$pks[$x]] );
            // Set the values
		    $tblRound->modified = $date->toSql();
		    $tblRound->modified_by = $user->get('id');
        
            

			if(!$tblRound->store()) 
            {
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				return false;
			}
		}
		return JText::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_SAVE');
	}
    
    /**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
	   $app = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       $date = JFactory::getDate();
	   $user = JFactory::getUser();
       $post = JRequest::get('post');
       $project_id = $app->getUserState( "$option.pid", '0' );
       
       //$app->enqueueMessage(JText::_('sportsmanagementModeldivision save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
       //$app->enqueueMessage(JText::_('sportsmanagementModeldivision project_id<br><pre>'.print_r($project_id,true).'</pre>'),'Notice');
       //$app->enqueueMessage(JText::_('sportsmanagementModeldivision post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       if ( !$data['id'] )
       {
       $data['project_id'] = $project_id;
       }
       
       if (isset($post['extended']) && is_array($post['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string)$parameter;
		}
        
        //$app->enqueueMessage(JText::_('sportsmanagementModeldivision save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        // Set the values
	   $data['modified'] = $date->toSql();
	   $data['modified_by'] = $user->get('id');

// Alter the title for Save as Copy
		if ($this->jsmjinput->get('task') == 'save2copy')
		{
			$orig_table = $this->getTable();
			$orig_table->load((int) $this->jsmjinput->getInt('id'));
            $data['id'] = 0;

			if ($data['name'] == $orig_table->name)
			{
				$data['name'] .= ' ' . JText::_('JGLOBAL_COPY');
				$data['alias'] = JFilterOutput::stringURLSafe( $data['name'] );
			}
		}
       
        // zuerst sichern, damit wir bei einer neuanlage die id haben
       if ( parent::save($data) )
       {
			$id =  (int) $this->getState($this->getName().'.id');
            $isNew = $this->getState($this->getName() . '.new');
            $data['id'] = $id;
            
            if ( $isNew )
            {
                //Here you can do other tasks with your newly saved record...
                $app->enqueueMessage(JText::plural(strtoupper($option) . '_N_ITEMS_CREATED', $id),'');
            }
           
		}
        
        return true;  
    }
    
    /**
	 * Method to remove division
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	public function delete(&$pks)
	{
	$app = JFactory::getApplication();
    //$app->enqueueMessage(JText::_('delete pks<br><pre>'.print_r($pks,true).'</pre>'),'');
    
    return parent::delete($pks);
    
         
   } 
   
    
}
