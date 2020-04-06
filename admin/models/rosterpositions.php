<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       rosterpositions.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * sportsmanagementModelrosterpositions
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelrosterpositions extends ListModel
{
    var $_identifier = "rosterpositions";
  
    /**
     * sportsmanagementModelrosterpositions::__construct()
     *
     * @param  mixed $config
     * @return void
     */
    public function __construct($config = array())
    { 
                $config['filter_fields'] = array(
                        'obj.name',
                        'obj.country',
                        'obj.alias',
                        'obj.id',
                        'obj.ordering'
                        );
                parent::__construct($config);
                $getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($getDBConnection);
    }
      
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since 1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        // Initialise variables.
        $app = Factory::getApplication('administrator');
      


        // Load the filter state.
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
        $this->setState('filter.state', $published);

        // List state information.
        parent::populateState('obj.name', 'asc');
    }
  
      
    protected function getListQuery()
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        $search    = $this->getState('filter.search');
      
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
        // Select some fields
        $query->select('obj.*');
        // From the hello table
        $query->from('#__sportsmanagement_rosterposition as obj');
        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');
        if ($search) {
            $query->where('LOWER(obj.name) LIKE '.$db->Quote('%'.$search.'%'));
        }

        $query->order(
            $db->escape($this->getState('list.ordering', 'obj.name')).' '.
            $db->escape($this->getState('list.direction', 'ASC'))
        );



        return $query;
      
      
      
    }




  
    function getRosterHome()
    {
        $bildpositionenhome = array();
        $bildpositionenhome['HOME_POS'][0]['heim']['oben'] = 5;
        $bildpositionenhome['HOME_POS'][0]['heim']['links'] = 233;
        $bildpositionenhome['HOME_POS'][1]['heim']['oben'] = 113;
        $bildpositionenhome['HOME_POS'][1]['heim']['links'] = 69;
        $bildpositionenhome['HOME_POS'][2]['heim']['oben'] = 113;
        $bildpositionenhome['HOME_POS'][2]['heim']['links'] = 179;
        $bildpositionenhome['HOME_POS'][3]['heim']['oben'] = 113;
        $bildpositionenhome['HOME_POS'][3]['heim']['links'] = 288;
        $bildpositionenhome['HOME_POS'][4]['heim']['oben'] = 113;
        $bildpositionenhome['HOME_POS'][4]['heim']['links'] = 397;
        $bildpositionenhome['HOME_POS'][5]['heim']['oben'] = 236;
        $bildpositionenhome['HOME_POS'][5]['heim']['links'] = 179;
        $bildpositionenhome['HOME_POS'][6]['heim']['oben'] = 236;
        $bildpositionenhome['HOME_POS'][6]['heim']['links'] = 288;
        $bildpositionenhome['HOME_POS'][7]['heim']['oben'] = 318;
        $bildpositionenhome['HOME_POS'][7]['heim']['links'] = 69;
        $bildpositionenhome['HOME_POS'][8]['heim']['oben'] = 318;
        $bildpositionenhome['HOME_POS'][8]['heim']['links'] = 233;
        $bildpositionenhome['HOME_POS'][9]['heim']['oben'] = 318;
        $bildpositionenhome['HOME_POS'][9]['heim']['links'] = 397;
        $bildpositionenhome['HOME_POS'][10]['heim']['oben'] = 400;
        $bildpositionenhome['HOME_POS'][10]['heim']['links'] = 233;
        return $bildpositionenhome;
    }
  
    function getRosterAway()
    {
        $bildpositionenaway = array();
        $bildpositionenaway['AWAY_POS'][0]['heim']['oben'] = 970;
        $bildpositionenaway['AWAY_POS'][0]['heim']['links'] = 233;
        $bildpositionenaway['AWAY_POS'][1]['heim']['oben'] = 828;
        $bildpositionenaway['AWAY_POS'][1]['heim']['links'] = 69;
        $bildpositionenaway['AWAY_POS'][2]['heim']['oben'] = 828;
        $bildpositionenaway['AWAY_POS'][2]['heim']['links'] = 179;
        $bildpositionenaway['AWAY_POS'][3]['heim']['oben'] = 828;
        $bildpositionenaway['AWAY_POS'][3]['heim']['links'] = 288;
        $bildpositionenaway['AWAY_POS'][4]['heim']['oben'] = 828;
        $bildpositionenaway['AWAY_POS'][4]['heim']['links'] = 397;
        $bildpositionenaway['AWAY_POS'][5]['heim']['oben'] = 746;
        $bildpositionenaway['AWAY_POS'][5]['heim']['links'] = 179;
        $bildpositionenaway['AWAY_POS'][6]['heim']['oben'] = 746;
        $bildpositionenaway['AWAY_POS'][6]['heim']['links'] = 288;
        $bildpositionenaway['AWAY_POS'][7]['heim']['oben'] = 664;
        $bildpositionenaway['AWAY_POS'][7]['heim']['links'] = 69;
        $bildpositionenaway['AWAY_POS'][8]['heim']['oben'] = 664;
        $bildpositionenaway['AWAY_POS'][8]['heim']['links'] = 397;
        $bildpositionenaway['AWAY_POS'][9]['heim']['oben'] = 587;
        $bildpositionenaway['AWAY_POS'][9]['heim']['links'] = 179;
        $bildpositionenaway['AWAY_POS'][10]['heim']['oben'] = 587;
        $bildpositionenaway['AWAY_POS'][10]['heim']['links'] = 288;
        return $bildpositionenaway;
    }
  

  
}
?>
