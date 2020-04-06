<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       positionstatistic.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table; 
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Utilities\ArrayHelper; 

/**
 * sportsmanagementModelpositionstatistic
 * 
 * @package 
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelpositionstatistic extends AdminModel
{
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
     * Returns a reference to the a Table object, always creating it.
     *
     * @param  type    The table type to instantiate
     * @param  string    A prefix for the table class name. Optional.
     * @param  array    Configuration array for model. Optional.
     * @return JTable    A database object
     * @since  1.6
     */
    public function getTable($type = 'positionstatistic', $prefix = 'sportsmanagementTable', $config = array()) 
    {
        $config['dbo'] = sportsmanagementHelper::getDBConnection(); 
        return Table::getInstance($type, $prefix, $config);
    }
    
    /**
     * Method to get the record form.
     *
     * @param  array   $data     Data for the form.
     * @param  boolean $loadData True if the form is to load its own data (default case), false if not.
     * @return mixed    A JForm object on success, false on failure
     * @since  1.6
     */
    public function getForm($data = array(), $loadData = true) 
    {
        // Get the form.
        $form = $this->loadForm('com_sportsmanagement.positionstatistic', 'positionstatistic', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }
        return $form;
    }
    
    /**
     * Method to get the script that have to be included on the form
     *
     * @return string    Script files
     */
    public function getScript() 
    {
        return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
    }
    
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return mixed    The data for the form.
     * @since  1.6
     */
    protected function loadFormData() 
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.positionstatistic.data', array());
        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }
    
    /**
     * Method to save item order
     *
     * @access public
     * @return boolean    True on success
     * @since  1.5
     */
    function saveorder($pks = null, $order = null)
    {
        $row = $this->getTable();
        
        // update ordering values
        for ($i=0; $i < count($pks); $i++)
        {
            $row->load((int) $pks[$i]);
            if ($row->ordering != $order[$i]) {
                $row->ordering=$order[$i];
                if (!$row->store()) {
                    $this->setError($this->_db->getErrorMsg());
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * sportsmanagementModelpositionstatistic::store()
     * 
     * @param  mixed $data
     * @param  mixed $position_id
     * @return
     */
    function store($data,$position_id)
    {
        $result    = true;
        $peid    = (isset($data['position_statistic']) ? $data['position_statistic'] : array());
        ArrayHelper::toInteger($peid);
        $peids = implode(',', $peid);
        
        $query = ' DELETE	FROM #__sportsmanagement_position_statistic '
               . ' WHERE position_id = ' . $position_id
               ;
        if (count($peid)) {
               $query .= '   AND statistic_id NOT IN  (' . $peids . ')';
        }

        $this->_db->setQuery($query);
        if(!$this->_db->execute() ) {
               $this->setError($this->_db->getErrorMsg());
               $result = false;
        }

        for ( $x = 0; $x < count($peid); $x++ )
        {
               $query = "UPDATE #__sportsmanagement_position_statistic SET ordering='$x' WHERE position_id = '" . $position_id . "' AND statistic_id = '" . $peid[$x] . "'";
                $this->_db->setQuery($query);
            if(!$this->_db->execute() ) {
                $this->setError($this->_db->getErrorMsg());
                $result= false;
            }
        }
        for ( $x = 0; $x < count($peid); $x++ )
        {
               $query = "INSERT IGNORE INTO #__sportsmanagement_position_statistic (position_id, statistic_id, ordering) VALUES ( '" . $position_id . "', '" . $peid[$x] . "','" . $x . "')";
               $this->_db->setQuery($query);
            if (!$this->_db->execute() ) {
                $this->setError($this->_db->getErrorMsg());
                $result= false;
            }
        }
        return $result;
    }
    
    
}
