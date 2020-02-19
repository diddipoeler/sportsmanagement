<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      treeto.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage models
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

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
        $this->jsmapp = Factory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmdocument = Factory::getDocument();
        
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
		//$post	= 			Factory::getApplication()->input->get( 'post' );
		$treeto_id = (int) $this->jsmjinput->post->get('id');
        // Get the form data
        $formData = new Registry($this->jsmjinput->get('jform', '', 'array'));
		$tree_i = (int) $formData->get('tree_i', 0);;
		$global_bestof = (int) $this->jsmjinput->post->get('global_bestof');
		$global_matchday = (int) $this->jsmjinput->post->get('global_matchday');
		$global_known = (int) $this->jsmjinput->post->get('global_known');
		$global_fake = (int) $this->jsmjinput->post->get('global_fake');
        
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