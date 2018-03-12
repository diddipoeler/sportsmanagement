<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      agegroup.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementModelagegroup
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementModelagegroup extends JSMModelAdmin
{

	/**
	 * Override parent constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   3.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	
		if ( JComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend') )
        {
    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($config,true).'</pre>'),'');
    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getName<br><pre>'.print_r($this->getName(),true).'</pre>'),'');
	}
		
	}
    
    
    
    /**
     * sportsmanagementModelagegroup::importAgeGroupFile()
     * 
     * @return void
     */
    public function importAgeGroupFile()
    {
    $databasetool = JModelLegacy::getInstance("databasetool", "sportsmanagementModel");    
    $cpaneltool = JModelLegacy::getInstance("cpanel", "sportsmanagementModel");
    $params = JComponentHelper::getParams( $this->jsmoption );
    $sporttypes = $params->get( 'cfg_sport_types' );
    $country = $params->get( 'cfg_country_associations' );    
    
    if ( JComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend') )
        {
		$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' sporttypes<br><pre>'.print_r($sporttypes,true).'</pre>'),'Notice');
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' country<br><pre>'.print_r($country,true).'</pre>'),'Notice');

        }
    
    foreach ( $sporttypes as $key => $type )
        {
        $checksporttype = $cpaneltool->checksporttype($type);   
        $insert_sport_type = $databasetool->insertSportType($type);  
	    if ( JComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend') )
        {
	$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' insert_sport_type<br><pre>'.print_r($insert_sport_type,true).'</pre>'),'Notice');	    
	    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' type<br><pre>'.print_r($type ,true).'</pre>'),'Notice');	    
	$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' checksporttype <br><pre>'.print_r($checksporttype ,true).'</pre>'),'Notice');
	    }
        foreach ( $country as $keyc => $typec )
        {    
        $insert_agegroup = $databasetool->insertAgegroup($typec,$insert_sport_type);  
		if ( JComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend') )
        {
	$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' insert_agegroup<br><pre>'.print_r($insert_agegroup,true).'</pre>'),'Notice');		
		}
        }
        }
    
        
    }
        
    	/**
    	 * sportsmanagementModelagegroup::saveshort()
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
        $pks = JFactory::getApplication()->input->getVar('cid', null, 'post', 'array');
        if ( !$pks )
        {
            return JText::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_SAVE_NO_SELECT');
        }
        $post = JFactory::getApplication()->input->post->getArray(array());
        
//        $app->enqueueMessage(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
//        $app->enqueueMessage(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($post, true).'</pre><br>','Notice');
        
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
		return JText::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_SAVE');
	}
        
    
}
