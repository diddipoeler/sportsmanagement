<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      treeto.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

//jimport( 'joomla.application.component.model' );
//require_once ( JPATH_COMPONENT . DS . 'models' . DS . 'item.php' );

// import Joomla modelform library
//jimport('joomla.application.component.modeladmin');



/**
 * sportsmanagementModelTreeto
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementModelTreeto extends JSMModelAdmin
{
    
   /**
    * sportsmanagementModelTreeto::__construct()
    * 
    * @param mixed $config
    * @return void
    */
   public function __construct($config = array())
        {   
 
        parent::__construct($config);
        $getDBConnection = sportsmanagementHelper::getDBConnection();
        parent::setDbo($getDBConnection);
        $this->jsmdb = sportsmanagementHelper::getDBConnection();
        parent::setDbo($this->jsmdb);
        $this->jsmquery = $this->jsmdb->getQuery(true);
        $this->jsmsubquery1 = $this->jsmdb->getQuery(true); 
        $this->jsmsubquery2 = $this->jsmdb->getQuery(true); 
        $this->jsmsubquery3 = $this->jsmdb->getQuery(true);  
        // Reference global application object
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmdocument = JFactory::getDocument();
        
        }    
    
    
    
	/**
	 * sportsmanagementModelTreeto::getTreeToData()
	 * 
	 * @param mixed $treeto_id
	 * @return
	 */
	function getTreeToData($treeto_id)
	{
		// Lets load the content if it doesn't already exist
    $this->jsmquery->clear();

		$this->jsmquery->select('*');
		$this->jsmquery->from('#__sportsmanagement_treeto');
        $this->jsmquery->where('id = ' . $treeto_id);
        
			$this->jsmdb->setQuery( $this->jsmquery );
			return $this->jsmdb->loadObject();

	}

	
    /**
     * sportsmanagementModelTreeto::setGenerateNode()
     * 
     * @return
     */
    function setGenerateNode()
	{
		//$post	= 			JFactory::getApplication()->input->get( 'post' );
		$treeto_id = (int) $this->jsmjinput->post->get('id');
        // Get the form data
        $formData = new JRegistry($this->jsmjinput->get('jform', '', 'array'));
		$tree_i = (int) $formData->get('tree_i', 0);;
		$global_bestof = (int) $this->jsmjinput->post->get('global_bestof');
		$global_matchday = (int) $this->jsmjinput->post->get('global_matchday');
		$global_known = (int) $this->jsmjinput->post->get('global_known');
		$global_fake = (int) $this->jsmjinput->post->get('global_fake');
        
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($this->jsmjinput->post->get('jform'),true).'</pre>'),'Notice');        
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' treeto_id<br><pre>'.print_r($treeto_id,true).'</pre>'),'Notice');
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' tree_i<br><pre>'.print_r($tree_i,true).'</pre>'),'Notice');
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' global_bestof<br><pre>'.print_r($global_bestof,true).'</pre>'),'Notice');
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' global_matchday<br><pre>'.print_r($global_matchday,true).'</pre>'),'Notice');
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' global_known<br><pre>'.print_r($global_known,true).'</pre>'),'Notice');
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' global_fake<br><pre>'.print_r($global_fake,true).'</pre>'),'Notice');

        
		if( $tree_i == 0 ) //nothing selected in dropdown
		{
			return false;
		}
		elseif( $tree_i > 0 )
		{

// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $treeto_id;
$object->global_bestof = $global_bestof ;
$object->global_matchday = $global_matchday ;
$object->global_known = $global_known ;
$object->global_fake = $global_fake ;
$object->leafed = 2 ;
$object->tree_i = $tree_i ;
// Update their details in the users table using id as the primary key.
$result = $this->jsmdb->updateObject('#__sportsmanagement_treeto', $object, 'id');

			// nodes to treeto_node
			for($nod=1;$nod<=((pow(2,$tree_i+1))-1);$nod++)
			{
				$i=$tree_i;
				$x=$nod;
				$ii=pow(2,$i);
				$row=$ii;

				while($x>1)
				{
					if($x>=(pow(2,$i)))
					{
						if(($x)%2==1)
						{
							$row+=$ii*(1/(pow(2,$i)));
							$i--;
						}
						else
						{
							$row-=$ii*(1/(pow(2,$i)));
							$i--;
						}
						$x=floor($x/2);
					}
					else
					{
						$i--;
					}
				}
// Create and populate an object.
$profile = new stdClass();
$profile->treeto_id = $treeto_id;
$profile->node = $nod;
$profile->row = $row;
$profile->bestof = $global_bestof;
 
// Insert the object into the user profile table.
$result = $this->jsmdb->insertObject('#__sportsmanagement_treeto_node', $profile);
                

			}
			return true;
		}
	}
}
?>