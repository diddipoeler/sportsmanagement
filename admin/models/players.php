<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       players.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage players
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;



class sportsmanagementModelplayers extends JSMModelList
{
    var $_identifier = "players";
  
  
 
  
    /**
     * sportsmanagementModelplayers::__construct()
     *
     * @param  mixed $config
     * @return
     */
    public function __construct($config = array())
    { 
                $config['filter_fields'] = array(
                        'pl.lastname',
                        'pl.firstname',
                        'pl.nickname',
                        'pl.birthday',
                        'pl.country',
                        'pl.position_id',
                        'pl.id',
                        'pl.picture',
                        'pl.ordering',
                        'pl.published',
                        'pl.modified',
                        'pl.modified_by',
                        'pl.checked_out',
                        'pl.checked_out_time',
                        'pl.agegroup_id',
                        'ag.name'
                        );
                 
                parent::__construct($config);
                parent::setDbo($this->jsmdb);
    }
      
  
  
  
    /**
     * sportsmanagementModelplayers::populateState()
     *
     * @param  mixed $ordering
     * @param  mixed $direction
     * @return
     */
    protected function populateState($ordering = null, $direction = null)
    {
        if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend') ) {
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' context -> '.$this->context.''), '');
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' identifier -> '.$this->_identifier.''), '');
        }
      
           /**
*
 * Load the filter state.
*/
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $published);
           $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
        $this->setState('filter.search_nation', $temp_user_request);
           $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_agegroup', 'filter_search_agegroup', '');
        $this->setState('filter.search_agegroup', $temp_user_request);
           $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
        $this->setState('list.limit', $value);  

        /**
*
 * List state information.
*/
           $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
        $this->setState('list.start', $value);     
        /**
*
 * Filter.order
*/
        $orderCol = $this->getUserStateFromRequest($this->context. '.filter_order', 'filter_order', '', 'string');
        if (!in_array($orderCol, $this->filter_fields)) {
            $orderCol = 'pl.lastname';
        }
        $this->setState('list.ordering', $orderCol);
        $listOrder = $this->getUserStateFromRequest($this->context. '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
        if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
            $listOrder = 'ASC';
        }
        $this->setState('list.direction', $listOrder);
      
    }
  

  
  
  
    /**
     * sportsmanagementModelplayers::getListQuery()
     *
     * @return
     */
    function getListQuery()
    {
  
        $this->_type = $this->jsmapp->getUserState("$this->jsmoption.persontype", '0');
        $this->_project_id = $this->jsmapp->getUserState("$this->jsmoption.pid", '0');
        $this->_team_id = $this->jsmapp->getUserState("$this->jsmoption.team_id", '0');
        $this->_season_id = $this->jsmapp->getUserState("$this->jsmoption.season_id", '0');
        $this->_project_team_id = $this->jsmapp->getUserState("$this->jsmoption.project_team_id", '0');

        $this->jsmquery->clear();
        $this->jsmsubquery1->clear();
        $this->jsmquery->select('pl.*');
        $this->jsmquery->select('pl.id as id2');
        $this->jsmquery->from('#__sportsmanagement_person as pl');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_agegroup AS ag ON ag.id = pl.agegroup_id');
        $this->jsmquery->select('uc.name AS editor');
        $this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = pl.checked_out');
      
        // neue struktur wird genutzt
        if (COM_SPORTSMANAGEMENT_USE_NEW_TABLE && $this->jsmjinput->getVar('layout') == 'assignplayers' ) {
            $this->jsmquery->join('INNER', '#__sportsmanagement_season_person_id AS sp ON sp.person_id = pl.id');
            $this->jsmquery->where('sp.season_id = '.$this->_season_id);
        }
      
        if ($this->getState('filter.search')) {
            $this->jsmquery->where(
                '(LOWER(pl.lastname) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%').
                'OR LOWER(pl.firstname) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') .
                'OR LOWER(pl.nickname) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') .
                           'OR LOWER(pl.info) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') .
                ')'
            );
        }
      
        if ($this->getState('filter.search_nation')) {
            $this->jsmquery->where('pl.country LIKE '.$this->jsmdb->Quote(''.$this->getState('filter.search_nation').''));
        }
      
        if ($this->getState('filter.search_agegroup')) {
            $this->jsmquery->where('pl.agegroup_id = ' . $this->getState('filter.search_agegroup'));
        }
      
        if (is_numeric($this->getState('filter.state')) ) {
            $this->jsmquery->where('pl.published = '.$this->getState('filter.state'));  
        }
      
        if ($this->jsmapp->input->getVar('layout') == 'assignpersons') {
            $this->_season_id = $this->jsmapp->input->get('season_id');
            switch ($this->_type)
            {
            case 1:
                /**
                 * spieler
                 */
                $this->jsmsubquery1->select('stp.person_id');
                $this->jsmsubquery1->from('#__sportsmanagement_season_team_person_id AS stp  ');
                $this->jsmsubquery1->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = stp.team_id');
                $this->jsmsubquery1->where('st.team_id = '.$this->_team_id);
                $this->jsmsubquery1->where('stp.season_id = '.$this->_season_id);
                $this->jsmsubquery1->where('stp.persontype = 1');
                $this->jsmquery->where('pl.id NOT IN ('.$this->jsmsubquery1.')');
                break;
            case 2:
                /**
                 * trainer
                 */
                $this->jsmsubquery1->select('stp.person_id');
                $this->jsmsubquery1->from('#__sportsmanagement_season_team_person_id AS stp  ');
                $this->jsmsubquery1->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = stp.team_id');
                $this->jsmsubquery1->where('st.team_id = '.$this->_team_id);
                $this->jsmsubquery1->where('stp.season_id = '.$this->_season_id);
                $this->jsmsubquery1->where('stp.persontype = 2');
                $this->jsmquery->where('pl.id NOT IN ('.$this->jsmsubquery1.')');
                break;
            case 3:
                /**
                 * schiedsrichter
                 */
                $this->jsmsubquery1->select('stp.person_id');
                $this->jsmsubquery1->from('#__sportsmanagement_season_person_id AS stp ');
                $this->jsmsubquery1->join('INNER', '#__sportsmanagement_project_referee AS prof ON prof.person_id = stp.id');
                $this->jsmsubquery1->where('stp.season_id = '.$this->_season_id);
                $this->jsmsubquery1->where('stp.persontype = 3');
                $this->jsmsubquery1->where('prof.project_id = '.$this->_project_id);
                $this->jsmquery->where('pl.id NOT IN ('.$this->jsmsubquery1.')');
                $this->jsmquery->group('pl.id');
                break;
            default:
                $this->jsmsubquery1->select('stp.person_id');
                $this->jsmsubquery1->from('#__sportsmanagement_season_person_id AS stp ');
                $this->jsmsubquery1->where('stp.season_id = '.$this->_season_id);
                $this->jsmquery->where('pl.id NOT IN ('.$this->jsmsubquery1.')');
                $this->jsmquery->group('pl.id');      
              
                break;
            }
          
          
        }
      
      
        $this->jsmquery->order(
            $this->jsmdb->escape($this->getState('list.ordering', 'pl.lastname')).' '.
            $this->jsmdb->escape($this->getState('list.direction', 'ASC'))
        );
              
        return $this->jsmquery;
      
      
      
    }
  
  
  
  
    /**
     * sportsmanagementModelplayers::getPersonsToAssign()
     *
     * @return
     */
    function getPersonsToAssign()
    {
        $cid = $this->jsmapp->input->getVar('cid');

        if (!count($cid) ) {
            return array();
        }
        $this->jsmquery->clear();
        $this->jsmquery->select('pl.id,pl.firstname, pl.nickname,pl.lastname');
        $this->jsmquery->from('#__sportsmanagement_person AS pl');
        $this->jsmquery->where('pl.id IN (' . implode(', ', $cid) . ')');
        $this->jsmquery->where('pl.published = 1');
      
        try{
            $this->jsmdb->setQuery($this->jsmquery);
            return $this->jsmdb->loadObjectList();
        }
        catch (Exception $e)
        {
            $this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');
            return false;
        }
    }


  
  
  
    /**
     * sportsmanagementModelplayers::getProjectTeamList()
     *
     * @return
     */
    function getProjectTeamList()
    {
        $this->jsmquery->clear();
        $this->jsmquery->select('t.id AS value,t.name AS text');
        $this->jsmquery->from('#__sportsmanagement_team AS t');
        $this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = t.id');
        $this->jsmquery->join('INNER', '#__sportsmanagement_project_team AS pt ON st.id = pt.team_id');
        $this->jsmquery->where('tt.project_id = ' . $this->_project_id);
        $this->jsmquery->order('text ASC');

        try{
              $this->jsmdb->setQuery($this->jsmquery);
            return $this->jsmdb->loadObjectList();
        }
        catch (Exception $e)
        {
            $this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');
            return false;
        }
    }

  
  
  
  
    /**
     * sportsmanagementModelplayers::getTeamName()
     *
     * @param  mixed $team_id
     * @return
     */
    function getTeamName( $team_id )
    {
        if (!$team_id ) {
            return '';
        }
        $this->jsmquery->clear();
        $this->jsmquery->select('name');
        $this->jsmquery->from('#__sportsmanagement_team');
        $this->jsmquery->where('id = '. $team_id);
      
        try{
            $this->jsmdb->setQuery($this->jsmquery);
            return $this->jsmdb->loadResult();
        }
        catch (Exception $e)
        {
            $this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');
            return false;
        }
    }
  
  
  
    /**
     * sportsmanagementModelplayers::getProjectTeamName()
     *
     * @param  mixed $project_team_id
     * @return
     */
    function getProjectTeamName( $project_team_id )
    {
        if (!$project_team_id ) {
            return '';
        }
        $this->jsmquery->clear();
        $this->jsmquery->select('t.name');
        $this->jsmquery->from('#__sportsmanagement_team AS t');
        $this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = t.id');
        $this->jsmquery->join('INNER', '#__sportsmanagement_project_team AS pt ON st.id = pt.team_id');
        $this->jsmquery->where('pt.id = '. $this->jsmdb->Quote($project_team_id));

        try{
              $this->jsmdb->setQuery($this->jsmquery);
            return $this->jsmdb->loadResult();
        }
        catch (Exception $e)
        {
            $this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');
            return false;
        }
      
    }



    /**
     * sportsmanagementModelplayers::getPersons()
     *
     * @return
     */
    function getPersons()
    {
        $this->jsmquery->clear();
        $this->jsmquery->select('id AS value,lastname,firstname,info,weight,height,picture,birthday,notes,nickname,knvbnr,country,phone,mobile,email');
        $this->jsmquery->from('#__sportsmanagement_person');
        $this->jsmquery->where('published = 1');
        $this->jsmquery->order('lastname ASC');
      
        try{
            $this->jsmdb->setQuery($this->jsmquery);
            return $this->jsmdb->loadObjectList();
        }
        catch (Exception $e)
        {
            $this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');
            return false;
        }
      
    }
  
  
  
    /**
     * sportsmanagementModelplayers::getPersonListSelect()
     *
     * @return
     */
    public function getPersonListSelect()
    {
        $this->jsmquery->clear();
        $this->jsmquery->select('id,id AS value,firstname,lastname,nickname,birthday,info');
        $this->jsmquery->select('LOWER(lastname) AS low_lastname');
        $this->jsmquery->select('LOWER(firstname) AS low_firstname');
        $this->jsmquery->select('LOWER(nickname) AS low_nickname');
        $this->jsmquery->from('#__sportsmanagement_person');
        $this->jsmquery->where("firstname <> '!Unknown' AND lastname <> '!Player' AND nickname <> '!Ghost'");
        $this->jsmquery->order('lastname,firstname');
      
        try{
            $this->jsmdb->setQuery($this->jsmquery);
            $results = $this->jsmdb->loadObjectList();
            if ($results ) {
                foreach ($results AS $person)
                {
                     $textString=$person->lastname.','.$person->firstname;
                    if (!empty($person->nickname)) {
                        $textString .= " '".$person->nickname."'";
                    }
                    if ($person->birthday!='0000-00-00') {
                        $textString .= " (".$person->birthday.")";
                    }
                     $person->text=$textString;
                }
                return $results;
            }
            return $results;
        }
        catch (Exception $e)
        {
            $this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');
            return false;
        }
    }
  

}
?>
