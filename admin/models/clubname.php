<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      clubname.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementModelclubname
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementModelclubname extends JSMModelAdmin
{
    
    /**
     * sportsmanagementModelclubname::import()
     * 
     * @return void
     */
    public function import()
    {
    // Reference global application object
        $app = JFactory::getApplication();
        // Create a new query object.		 
	$db = sportsmanagementHelper::getDBConnection(); 
$query = $db->getQuery(true);

        $option = JFactory::getApplication()->input->getCmd('option');
        // JInput object
        $jinput = $app->input;    

$xml = JFactory::getXML(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/xml_files/clubnames.xml',true);
        
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' xml <br><pre>'.print_r($xml ,true).'</pre>'),'');    
     
     foreach( $xml->children() as $quote )  
             { 
              
             $country = (string)$quote->clubname->attributes()->country; 
             $name = (string)$quote->clubname->attributes()->name; 
             $clubname = (string)$quote->clubname;
             
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' country <br><pre>'.print_r($country ,true).'</pre>'),'Notice');             
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' name <br><pre>'.print_r($name ,true).'</pre>'),'Notice');             
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' clubname<br><pre>'.print_r($clubname,true).'</pre>'),'Notice');                          
  
$query->clear();  
$query->select('id');  
$query->from('#__sportsmanagement_club_names');
$query->where('country LIKE '.$db->Quote(''.$country.'') );
$query->where('name LIKE '.$db->Quote(''.$name.'') );
$db->setQuery($query);  
  
$result = $db->loadResult();  
  
if ( !$result )
{
$insertquery = $db->getQuery(true); 
// Insert columns. 
$columns = array('country','name','name_long'); 
// Insert values. 
$values = array('\''.$country.'\'','\''.$name.'\'','\''.$clubname.'\''); 
// Prepare the insert query. 
$insertquery 
->insert($db->quoteName('#__sportsmanagement_club_names')) 
->columns($db->quoteName($columns)) 
->values(implode(',', $values)); 
// Set the query using our newly populated query object and execute it. 
$db->setQuery($insertquery); 

sportsmanagementModeldatabasetool::runJoomlaQuery();

} 
		}
        
        
    } 
    
}
