<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       sportsmanagement.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\ItemModel; 
use Joomla\Registry\Registry;

/**
 * sportsmanagementModelsportsmanagement
 * 
 * @package 
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelsportsmanagement extends ItemModel
{
    /**
     * @var object item
     */
    protected $item;
 
    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return void
     * @since  1.6
     */
    protected function populateState() 
    {
        $app = Factory::getApplication();
        // Get the message id
        $id = Factory::getApplication()->input->getInt('id');
        $this->setState('message.id', $id);
 
        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);
        parent::populateState();
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
    public function getTable($type = 'sportsmanagement', $prefix = 'sportsmanagementTable', $config = array()) 
    {
        return Table::getInstance($type, $prefix, $config);
    }
 
    /**
     * Get the message
     *
     * @return object The message to be displayed to the user
     */
    public function getItem() 
    {
        if (!isset($this->item)) {
            $id = $this->getState('message.id');
            $this->_db->setQuery(
                $this->_db->getQuery(true)
                    ->from('#__sportsmanagement as h')
                    ->leftJoin('#__categories as c ON h.catid=c.id')
                    ->select('h.greeting, h.params, c.title as category')
                    ->where('h.id=' . (int)$id)
            );
            if (!$this->item = $this->_db->loadObject()) {
                   $this->setError($this->_db->getError());
            }
            else
            {
                     // Load the JSON string
                     $params = new Registry;
                if(version_compare(JVERSION, '3.0.0', 'ge')) {
                       $params->loadString($this->item->params);
                }
                else
                         {
                    $params->loadJSON($this->item->params);
                }
                
                     $this->item->params = $params;
 
                     // Merge global params with item params
                     $params = clone $this->getState('params');
                     $params->merge($this->item->params);
                     $this->item->params = $params;
            }
        }
        return $this->item;
    }
}
