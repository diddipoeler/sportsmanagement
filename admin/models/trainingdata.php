<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       trainingdata.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

/**
 * sportsmanagementModeltrainingdata
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModeltrainingdata extends JSMModelAdmin
{

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param  type    The table type to instantiate
     * @param  string    A prefix for the table class name. Optional.
     * @param  array    Configuration array for model. Optional.
     * @return JTable    A database object
     * @since  1.6
     */
    public function getTable($type = 'TeamTrainingData', $prefix = 'sportsmanagementTable', $config = array())
    {
        $config['dbo'] = sportsmanagementHelper::getDBConnection();
        return Table::getInstance($type, $prefix, $config);
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
          $date = Factory::getDate();
          $user = Factory::getUser();
          $post = Factory::getApplication()->input->post->getArray(array());
          // Set the values
          $data['modified'] = $date->toSql();
          $data['modified_by'] = $user->get('id');
      
          // zuerst sichern, damit wir bei einer neuanlage die id haben
        if (parent::save($data) ) {
            $id =  (int) $this->getState($this->getName().'.id');
            $isNew = $this->getState($this->getName() . '.new');
            $data['id'] = $id;
          
            if ($isNew ) {
                //Here you can do other tasks with your newly saved record...
                $this->jsmapp->enqueueMessage(Text::plural(strtoupper($this->jsmoption) . '_N_ITEMS_CREATED', $id), '');
            }
         
        }
    } 
  
}
