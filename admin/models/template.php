<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      template.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
//jimport('joomla.application.component.modeladmin');
 

/**
 * sportsmanagementModeltemplate
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModeltemplate extends JSMModelAdmin
{
  
    /**
     * sportsmanagementModeltemplate::getAllTemplatesList()
     * 
     * @param mixed $project_id
     * @param mixed $master_id
     * @return
     */
    function getAllTemplatesList($project_id,$master_id)
	{
		
        $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        // Create a new query object.
		$db	= $this->getDbo();
		$query1	= $db->getQuery(true);
        $query2	= $db->getQuery(true);
        $query3	= $db->getQuery(true);
        $result1 = array();
        $result2 = array();
                
        // Select some fields
		$query1->select('template');
        // From table
		$query1->from('#__sportsmanagement_template_config');
        $query1->where('project_id = '.$project_id);
        $db->setQuery($query1);

        if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
		$current = $db->loadColumn();
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
		$current = $db->loadResultArray();
}

//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' current<br><pre>'.print_r($current,true).'</pre>'),'Notice');
        if ( $current )
        {
        $current = implode("','",$current);
        }
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' current<br><pre>'.print_r($current,true).'</pre>'),'Notice');
        
        
        // Select some fields
		$query2->select('id as value, title as text');
        // From table
		$query2->from('#__sportsmanagement_template_config');
        $query2->where('project_id = '.$master_id);
        if ( $current )
        {
        $query2->where('template NOT IN (\''.$current.'\') ');    
        }
        $db->setQuery($query2);
        $result1 = $db->loadObjectList();
        
        foreach ($result1 as $template)
        {
			$template->text = JText::_($template->text);
		}
        
        
        // Select some fields
		$query3->select('id as value, title as text');
        // From table
		$query3->from('#__sportsmanagement_template_config');
        $query3->where('project_id = '.$project_id);
        $query3->order('title');
        $db->setQuery($query3);
		$result2 = $db->loadObjectList();
        
        foreach ($result2 as $template)
        {
			$template->text = JText::_($template->text);
		}
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result1<br><pre>'.print_r($result1,true).'</pre>'),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result2<br><pre>'.print_r($result2,true).'</pre>'),'Notice');
        
        if ( $result1 )
        {
        return array_merge($result2,$result1);    
        }
        else
        {
        return ($result2);    
        }
		
	}
    
    
	/**
	 * sportsmanagementModeltemplate::import()
	 * 
	 * @param mixed $templateid
	 * @param mixed $projectid
	 * @return
	 */
	function import($templateid,$projectid)
{
$row =& $this->getTable();

// load record to copy
if (!$row->load($templateid))
{
$this->setError($this->_db->getErrorMsg());
return false;
}

//copy to new element
$row->id=null;
$row->project_id=(int) $projectid;

// Make sure the item is valid
if (!$row->check())
{
$this->setError($this->_db->getErrorMsg());
return false;
}

// Store the item to the database
if (!$row->store())
{
$this->setError($this->_db->getErrorMsg());
return false;
}
return true;
}    


	/**
	 * sportsmanagementModeltemplate::delete()
	 * 
	 * @param mixed $pks
	 * @return
	 */
	public function delete(&$pks)
{
$app = JFactory::getApplication();
    //$app->enqueueMessage(JText::_('delete pks<br><pre>'.print_r($pks,true).'</pre>'),'');
    
    return parent::delete($pks);
    
         
   } 



    
}
