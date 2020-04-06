<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       projectposition.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;

/**
 * Sportsmanagement Component Positionstool Model
 *
 * @package Sportsmanagement
 * @since   0.1
 */
class sportsmanagementModelProjectposition extends JSMModelAdmin
{
    var $_identifier = "pposition";
    var $_project_id = 0;
    
    /**
     * Method override to check if you can edit an existing record.
     *
     * @param array  $data An array of input data.
     * @param string $key  The name of the key for the primary key.
     *
     * @return boolean
     * @since  1.6
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        // Check specific edit permission then general edit permission.
        return Factory::getUser()->authorise('core.edit', 'com_sportsmanagement.message.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
    }
    
    /**
     * Method to update project positions list
     *
     * @access public
     * @return boolean    True on success
     */
    function store($data)
    {
        $app = Factory::getApplication();
        $result = true;
        $peid = (isset($data['project_positionslist']));
        if ($peid==null) {
            $query="DELETE FROM #__sportsmanagement_project_position WHERE project_id=".$data['project_id'];
        }
        else
        {
            $pidArray=$data['project_positionslist'];
            ArrayHelper::toInteger($pidArray);
            $peids=implode(",", $pidArray);
            $query="DELETE FROM #__sportsmanagement_project_position WHERE project_id=".$data['project_id']." AND position_id NOT IN ($peids)";
        }
        $this->_db->setQuery($query);
        if (!$this->_db->execute()) {
            sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
            $result=false;
        }
        for ($x=0; $x < count($data['project_positionslist']); $x++)
        {
            $query="INSERT IGNORE INTO #__sportsmanagement_project_position (project_id,position_id) VALUES ('".$data['project_id']."','".$data['project_positionslist'][$x]."')";
            $this->_db->setQuery($query);
            if(!$this->_db->execute()) {
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
                $result=false;
            }
        }
        return $result;
    }

    
    
    
}
?>
