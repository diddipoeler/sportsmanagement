<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       teamstaff.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\MVC\Model\AdminModel;

/**
 * SportsManagement Model
 */
class sportsmanagementModelteamstaff extends AdminModel
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
     * @return Table    A database object
     * @since  1.6
     */
    public function getTable($type = 'teamstaff', $prefix = 'sportsmanagementTable', $config = array())
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
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        $cfg_which_media_tool = ComponentHelper::getParams($option)->get('cfg_which_media_tool', 0);
        // Get the form.
        $form = $this->loadForm('com_sportsmanagement.teamstaff', 'teamstaff', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }
      
        $form->setFieldAttribute('picture', 'default', ComponentHelper::getParams($option)->get('ph_player', ''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/teamstaffs');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
      
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
        $data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.teamstaff.data', array());
        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }
  
  
    /**
     * Method to update checked teamstaff
     *
     * @access public
     * @return boolean    True on success
     */
    function saveshort()
    {
        $app =& Factory::getApplication();
        // Get the input
        $pks = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
        $post = Factory::getApplication()->input->post->getArray(array());
     
        $result=true;
        for ($x=0; $x < count($pks); $x++)
        {
            $tblPerson = & $this->getTable();
            $tblPerson->id = $pks[$x];
            $tblPerson->project_position_id    = $post['project_position_id'.$pks[$x]];

            if(!$tblPerson->store()) {
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
                $result=false;
            }
        }
        return $result;
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
        $row =& $this->getTable();
      
        // update ordering values
        for ($i = 0; $i < count($pks); $i++)
        {
            $row->load((int) $pks[$i]);
            if ($row->ordering != $order[$i]) {
                $row->ordering=$order[$i];
                if (!$row->store()) {
                    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
                    return false;
                }
            }
        }
        return true;
    }
  
    /**
     * Method to remove teamstaff
     *
     * @access public
     * @return boolean    True on success
     * @since  0.1
     */
    public function delete(&$pks)
    {
        $app = Factory::getApplication();
          /* Ein Datenbankobjekt beziehen */
          $db = Factory::getDbo();
          /* Ein JDatabaseQuery Objekt beziehen */
          $query = $db->getQuery(true);
          //$pks	= (array) $pks;
  
        $result = false;
        if (count($pks)) {
            $cids = implode(',', $pks);

            // wir löschen mit join
            $query = 'DELETE mp,ms
            FROM #__sportsmanagement_team_staff as m  
            LEFT JOIN #__sportsmanagement_match_staff as mp
            ON mp.team_staff_id = m.id
            LEFT JOIN #__sportsmanagement_match_staff_statistic as ms
            ON ms.team_staff_id = m.id
            WHERE m.id IN ('.$cids.')';
            $db->setQuery($query);
            $db->execute();
            if (!$db->execute()) {
                return false;
            }
          
        }
          return parent::delete($pks);

    }
 
    /**
     * Method to save the form data.
     *
     * @param  array    The form data.
     * @return boolean    True on success.
     * @since  1.6
     */
    public function save($data)
    {
          $app = Factory::getApplication();
          $post=Factory::getApplication()->input->post->getArray(array());
    
        if (isset($post['extended']) && is_array($post['extended'])) {
            // Convert the extended field to a string.
            $parameter = new Registry;
            $parameter->loadArray($post['extended']);
            $data['extended'] = (string)$parameter;
        }
      
        // Proceed with the save
        return parent::save($data); 
    }
  
 
}
