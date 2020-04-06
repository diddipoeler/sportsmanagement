<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       predictiongames.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * sportsmanagementModelPredictionGames
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelPredictionGames extends JSMModelList
{
    var $_identifier = "predgames";
    
    
    /**
     * sportsmanagementModelPredictionGames::__construct()
     * 
     * @param  mixed $config
     * @return void
     */
    public function __construct($config = array())
    {   
                $config['filter_fields'] = array(
                        'pre.name',
                        'pre.published',
                        'pre.id',
                        'pre.ordering',
                        'pre.modified',
                        'pre.modified_by'
                        );
                parent::__construct($config);
                parent::setDbo($this->jsmdb);
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
        $prediction_id = $this->jsmjinput->getInt('prediction_id', 0);
        
        // Load the filter state.
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
        $this->setState('filter.state', $published);
        
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.prediction_id', 'filter_prediction_id', '');
       
        if ($temp_user_request == '' ) {
            $temp_user_request = $prediction_id;
        }
        $this->setState('filter.prediction_id', $temp_user_request);

        // List state information.
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
        $this->setState('list.start', $value);       
        // Filter.order
        $orderCol = $this->getUserStateFromRequest($this->context. '.filter_order', 'filter_order', '', 'string');
        if (!in_array($orderCol, $this->filter_fields)) {
            $orderCol = 'pre.name';
        }
        $this->setState('list.ordering', $orderCol);
        $listOrder = $this->getUserStateFromRequest($this->context. '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
        if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
            $listOrder = 'ASC';
        }
        $this->setState('list.direction', $listOrder);
    }
    
    /**
     * sportsmanagementModelPredictionGames::getListQuery()
     * 
     * @return
     */
    function getListQuery()
    {
        $prediction_id = $this->jsmapp->getUserState("$this->jsmoption.prediction_id", '0');
        // Create a new query object.		
        $this->jsmquery->clear();
        $this->jsmquery->select(array('pre.*', 'u.name AS editor,u1.username'))
            ->from('#__sportsmanagement_prediction_game AS pre')
            ->join('LEFT', '#__users AS u ON u.id = pre.checked_out')
            ->join('LEFT', '#__users AS u1 ON u1.id = pre.modified_by');

        if ($prediction_id > 0 ) {
            $this->jsmquery->where('pre.id = ' . $prediction_id);
        }
        if ($this->getState('filter.search') ) {
            $this->jsmquery->where("LOWER(pre.name) LIKE " . $this->jsmdb->Quote('%'.$this->getState('filter.search').'%'));
        }
        if (is_numeric($this->getState('filter.state'))) {
            $this->jsmquery->where('pre.published = '.$this->getState('filter.state'));
        }
        
        $this->jsmquery->order(
            $this->jsmdb->escape($this->getState('list.ordering', 'pre.name')).' '.
            $this->jsmdb->escape($this->getState('list.direction', 'ASC'))
        );
 
        return $this->jsmquery;
    }


    /**
     * sportsmanagementModelPredictionGames::getChilds()
     * 
     * @param  mixed $pred_id
     * @param  bool  $all
     * @return
     */
    function getChilds( $pred_id, $all = false )
    {
          // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
        $what = 'pro.*';
        if ($all ) {
            $what = 'pro.project_id';
        }
        
        if (!is_array($pred_id) ) {
            // Select some fields
            $query->select($what);
            $query->select('joo.name as project_name');
            $query->from('#__sportsmanagement_prediction_project AS pro ');
            $query->join('LEFT', '#__sportsmanagement_project AS joo ON joo.id = pro.project_id');
            $query->where('pro.prediction_id = ' . $pred_id);
            $query->where('pro.project_id != 0');
        
            $db->setQuery($query);
       
            if ($all ) {
                if(version_compare(JVERSION, '3.0.0', 'ge')) {
                    // Joomla! 3.0 code here
                    $records = $db->loadColumn();
                }
                elseif(version_compare(JVERSION, '2.5.0', 'ge')) {
                      // Joomla! 2.5 code here
                        $records = $db->loadResultArray();
                }
                       return $records;
            }
            return $db->loadAssocList('id');
        }
        else
        {
            return false;
        }
    }

    /**
     * sportsmanagementModelPredictionGames::getAdmins()
     * 
     * @param  mixed $pred_id
     * @param  bool  $list
     * @return
     */
    static function getAdmins( $pred_id = 0, $list = false )
    {
          // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
        
        $as_what = '';
        if ($list ) {
            $as_what = ' AS value';
        }
        
        if ($pred_id ) {
            // Select some fields
            $query->select('user_id' . $as_what);
            $query->from('#__sportsmanagement_prediction_admin ');
            $query->where('prediction_id = ' . $pred_id);

            $db->setQuery($query);
            if ($list ) {
                       return $db->loadObjectList();
            }
            else
            {
                if(version_compare(JVERSION, '3.0.0', 'ge')) {
                    // Joomla! 3.0 code here
                    return $db->loadColumn();
                }
                elseif(version_compare(JVERSION, '2.5.0', 'ge')) {
                      // Joomla! 2.5 code here
                        return $db->loadResultArray();
                }
            }
        
        }
        else
        {
            return false;
        }
        
    }
    
    
    /**
     * sportsmanagementModelPredictionGames::getPredictionGamesMatches()
     * 
     * @param  mixed $predictionGameID
     * @param  mixed $predictionProjectID
     * @param  mixed $userID
     * @return
     */
    function getPredictionGamesMatches($predictionGameID,$predictionProjectID,$userID)
    {
        $this->jsmquery->clear();
        $this->jsmquery->select('m.id,m.round_id,m.match_date,m.projectteam1_id,m.projectteam2_id,m.team1_result,m.team2_result,m.team1_result_decision,m.team2_result_decision');
        $this->jsmquery->select('r.id AS roundcode,r.round_date_first,r.round_date_last');
        $this->jsmquery->select('pr.tipp,pr.tipp_home,pr.tipp_away,pr.joker,pr.id AS prid');
        $this->jsmquery->select('p.id AS project_id,p.name AS project_name');
        
        $this->jsmquery->select('t1.name AS home_name');
        $this->jsmquery->select('t2.name AS away_name');
        
        $this->jsmquery->select('c1.logo_big AS home_logo_big,c1.country AS home_country');
        $this->jsmquery->select('c2.logo_big AS away_logo_big,c2.country AS away_country');
        
        $this->jsmquery->from('#__sportsmanagement_match AS m');
        $this->jsmquery->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id');
        $this->jsmquery->join('INNER', '#__sportsmanagement_project AS p ON p.current_round = r.id');
        $this->jsmquery->join(
            'LEFT', '#__sportsmanagement_prediction_result AS pr ON pr.match_id = m.id
                     AND pr.prediction_id = '.(int)$predictionGameID.' AND pr.user_id = '.$userID.' AND pr.project_id = '. $predictionProjectID . '' 
        );
        $this->jsmquery->join('LEFT', '#__sportsmanagement_prediction_game AS pg ON pg.id = '.(int)$predictionGameID);
        
        $this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id');
        
        $this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
        
        $this->jsmquery->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_club AS c1 ON c1.id = t1.club_id');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_club AS c2 ON c2.id = t2.club_id');
        $this->jsmquery->where('r.project_id = ' . $predictionProjectID . '');
       
        $this->jsmquery->where('m.published = 1');
        $this->jsmquery->where('m.match_date <> \'0000-00-00 00:00:00\'');
        $this->jsmquery->where('(m.cancel IS NULL OR m.cancel = 0)');      
        $this->jsmquery->order('m.match_date ASC');
        try{            
            $this->jsmdb->setQuery($this->jsmquery);
            $results = $this->jsmdb->loadObjectList();
             return $results;
        }
        catch (Exception $e)
        {
            $this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');
            return false;
        }
    }   

    /**
    * Method to return a prediction games array
    *
    * @access public
    * @return array
    * @since  0.1
    */
    function getPredictionGames()
    {
          $this->jsmquery->clear();
        // Select some fields
        $this->jsmquery->select('id AS value, name AS text');
        $this->jsmquery->from('#__sportsmanagement_prediction_game ');
        $this->jsmquery->order('name');

        try{
              $this->jsmdb->setQuery($this->jsmquery);
            $result = $this->jsmdb->loadObjectList();
            return $result;
        }
        catch (Exception $e)
        {
            $this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');
            return false;
        }

    }

}
?>
