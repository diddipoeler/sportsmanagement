<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      club.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 

/**
 * sportsmanagementModelclub
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelclub extends JSMModelAdmin
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
	
//    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($config,true).'</pre>'),'');
//    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getName<br><pre>'.print_r($this->getName(),true).'</pre>'),'');
    
	}	


   
   
    /**
	 * Method to update checked clubs
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function saveshort()
	{
		$app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        //$show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        // Get the input
        $pks = JFactory::getApplication()->input->getVar('cid', null, 'post', 'array');
        $post = JFactory::getApplication()->input->post->getArray(array());
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = 'pks <pre>'.print_r($pks,true).'</pre>';    
        $my_text .= 'post <pre>'.print_r($post,true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
        
        $result=true;
		for ($x=0; $x < count($pks); $x++)
		{
		  $address_parts = array();
          $address_parts2 = array();
			$tblClub = & $this->getTable();
            
            
			$tblClub->id	= $pks[$x];
            $tblClub->zipcode = $post['zipcode' . $pks[$x]];
			$tblClub->location = $post['location' .$pks[$x]];
            $tblClub->address = $post['address' .$pks[$x]];
            $tblClub->country = $post['country' .$pks[$x]];
            
            $tblClub->unique_id = $post['unique_id' .$pks[$x]];
            $tblClub->new_club_id = $post['new_club_id' .$pks[$x]];
            
            if (!empty($tblClub->address))
		{
			$address_parts[] = $tblClub->address;
		}
		
		if (!empty($tblClub->location))
		{
			if (!empty($tblClub->zipcode))
			{
				$address_parts[] = $tblClub->zipcode. ' ' .$tblClub->location;
                $address_parts2[] = $tblClub->zipcode. ' ' .$tblClub->location;
			}
			else
			{
				$address_parts[] = $tblClub->location;
                $address_parts2[] = $tblClub->location;
			}
		}
		if (!empty($tblClub->country))
		{
			$address_parts[] = JSMCountries::getShortCountryName($tblClub->country);
            $address_parts2[] = JSMCountries::getShortCountryName($tblClub->country);
		}
		$address = implode(', ', $address_parts);
		$coords = sportsmanagementHelper::resolveLocation($address);
        
//        $app->enqueueMessage(__METHOD__.' '.__LINE__.'coords <pre>'.print_r($coords, true).'</pre><br>','');
//        $app->enqueueMessage(__METHOD__.' '.__LINE__.'address_parts <pre>'.print_r($address_parts, true).'</pre><br>','');
//        $app->enqueueMessage(__METHOD__.' '.__LINE__.'address_parts2 <pre>'.print_r($address_parts2, true).'</pre><br>','');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = 'coords <pre>'.print_r($coords,true).'</pre>';    
        $my_text .= 'address_parts <pre>'.print_r($address_parts,true).'</pre>';
        $my_text .= 'address_parts2 <pre>'.print_r($address_parts2,true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.'_success_text <pre>'.print_r(sportsmanagementHelper::$_success_text, true).'</pre><br>','');
        }
        
        if ( $coords )
        {
		$tblClub->latitude = $coords['latitude'];
		$tblClub->longitude = $coords['longitude'];
        }
        else
        {
            
        }

			if(!$tblClub->store()) 
            {
				//$this->setError($this->_db->getErrorMsg());
                $app->enqueueMessage(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->_db->getErrorMsg(), true).'</pre><br>','Error');
				$result = false;
			}
		}
		return $result;
	}
    
    
    /**
     * sportsmanagementModelclub::teamsofclub()
     * 
     * @param mixed $club_id
     * @return void
     */
    function teamsofclub($club_id)
    {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db	= sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
        
        $query->clear();
$query->select('t.id,t.name');
$query->from('#__sportsmanagement_team AS t');
$query->where('t.club_id = '.$club_id);
$db->setQuery( $query );
$teamsofclub = $db->loadObjectList();
return $teamsofclub; 
        
    }
    
    
}
