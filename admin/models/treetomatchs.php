<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       treetomatchs.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */
 
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
use Joomla\Utilities\ArrayHelper;

/**
 * sportsmanagementModelTreetomatchs
 * 
 * @package 
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelTreetomatchs extends JSMModelList
{
    var $_identifier = "treetomatchs";



    /**
 * sportsmanagementModelTreetomatchs::getListQuery()
 * 
 * @return
 */
    protected function getListQuery()
    {
         // Select the required fields from the table.
        $this->jsmquery->clear();
          $this->jsmquery->select('mc.id AS mid,mc.match_number AS match_number,t1.name AS projectteam1,mc.team1_result AS projectteam1result,mc.team2_result AS projectteam2result');
          $this->jsmquery->select('t2.name AS projectteam2,mc.round_id AS rid,mc.published AS published,ttm.node_id AS node_id,r.roundcode AS roundcode, mc.checked_out');
        
          $this->jsmquery->from('#__sportsmanagement_match AS mc');   
           $this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt1 ON pt1.id = mc.projectteam1_id');
           $this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt2 ON pt2.id = mc.projectteam2_id');
       
           $this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st1 on pt1.team_id = st1.id');  
           $this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st2 on pt2.team_id = st2.id');  
       
           $this->jsmquery->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
           $this->jsmquery->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
           $this->jsmquery->join('LEFT', '#__sportsmanagement_round AS r ON r.id = mc.round_id');
           $this->jsmquery->join('LEFT', '#__sportsmanagement_treeto_match AS ttm ON mc.id = ttm.match_id ');
       
           $this->jsmquery->where('ttm.node_id = ' . (int) $this->jsmjinput->get('nid'));
       
        $this->jsmquery->order('r.roundcode');   
       
          return $this->jsmquery;
    }   
    


    /**
     * sportsmanagementModelTreetomatchs::store()
     * 
     * @param  mixed $data
     * @return
     */
    function store( $data )
    {
        $result = true;
        $peid = $data['node_matcheslist'];
        $this->jsmquery->clear();
        if ($peid == null ) {
            $conditions = array(
             $this->jsmdb->quoteName('node_id') . ' = ' . $this->jsmdb->quote($data['id'])
            );
        }
        else
        {
            ArrayHelper::toInteger($peid);
            $peids = implode(',', $peid);
            $conditions = array(
             $this->jsmdb->quoteName('node_id') . ' = ' . $this->jsmdb->quote($data['id']),
             $this->jsmdb->quoteName('match_id') . ' NOT IN  ( ' . $this->jsmdb->quote($peids) .')'
            );        
        }

        $this->jsmquery->delete($this->jsmdb->quoteName('#__sportsmanagement_treeto_match'));
        $this->jsmquery->where($conditions);

        $this->jsmdb->setQuery($this->jsmquery);
        if (!sportsmanagementModeldatabasetool::runJoomlaQuery() ) {
            $this->setError($this->jsmdb->getErrorMsg());
            $result = false;
        }

        for ( $x = 0; $x < count($data['node_matcheslist']); $x++ )
        {
            $profile = new stdClass();
            $profile->node_id = $data['id'];
            $profile->match_id = $data['node_matcheslist'][$x];
            $result = $this->jsmdb->insertObject('#__sportsmanagement_treeto_match', $profile);
            
            if (!$result ) {
                $this->setError($this->jsmdb->getErrorMsg());
                $result = false;
            }
        }
        return $result;
    }

    

    /**
     * sportsmanagementModelTreetomatchs::getMatches()
     * 
     * @return
     */
    function getMatches()
    {
        $node_id = $this->jsmjinput->get('nid');
        $treeto_id = $this->jsmjinput->get('tid');
        $project_id = $this->jsmjinput->get('pid');


        $this->jsmquery->clear();
        $this->jsmsubquery1->clear();

        $this->jsmquery->select('mc.id AS value,CONCAT(t1.name, \'_vs_\', t2.name, \' [round:\',r.roundcode,\']\') AS text');
        $this->jsmquery->select('mc.id AS info');
        
        $this->jsmquery->from('#__sportsmanagement_match AS mc');   
          $this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt1 ON pt1.id = mc.projectteam1_id');
          $this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt2 ON pt2.id = mc.projectteam2_id');
       
          $this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st1 on pt1.team_id = st1.id');  
          $this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st2 on pt2.team_id = st2.id');  
       
          $this->jsmquery->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
          $this->jsmquery->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
          $this->jsmquery->join('LEFT', '#__sportsmanagement_round AS r ON r.id = mc.round_id');
       
          $this->jsmquery->where('r.project_id = ' . $project_id);
       
           $this->jsmsubquery1->select('ttn.team_id');
          $this->jsmsubquery1->from('#__sportsmanagement_treeto_node AS ttn');
          $this->jsmsubquery1->join('LEFT', '#__sportsmanagement_treeto_node AS ttn2 ON (ttn.node = 2*ttn2.node OR ttn.node = 2*ttn2.node + 1) ');   
          $this->jsmsubquery1->where('ttn2.id = ' . $node_id);
          $this->jsmsubquery1->where('ttn.treeto_id = ' . $treeto_id);

          $this->jsmquery->where('NOT mc.projectteam1_id IN ( ' . $this->jsmsubquery1 .' )');
        $this->jsmquery->where('NOT mc.projectteam2_id IN ( ' . $this->jsmsubquery1 .' )');
          $this->jsmquery->order('r.id');   
        
        $this->jsmdb->setQuery($this->jsmquery);
        if (!$result = $this->jsmdb->loadObjectList() ) {
            $this->setError($this->jsmdb->getErrorMsg());
            return false;
        }
        else
        {
            return $result;
        }
    }


    /**
     * sportsmanagementModelTreetomatchs::getNodeMatches()
     * 
     * @param  integer $node_id
     * @return
     */
    function getNodeMatches($node_id=0)
    {
            
        
        $this->jsmquery->clear();
        $this->jsmquery->select('mc.id AS value,CONCAT(t1.name, \'_vs_\', t2.name, \' [round:\',r.roundcode,\']\') AS text');
        $this->jsmquery->select('mc.id AS notes,mc.id AS info');
        
        $this->jsmquery->from('#__sportsmanagement_match AS mc');   
          $this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt1 ON pt1.id = mc.projectteam1_id');
          $this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt2 ON pt2.id = mc.projectteam2_id');
       
          $this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st1 on pt1.team_id = st1.id');  
          $this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st2 on pt2.team_id = st2.id');  
       
          $this->jsmquery->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
          $this->jsmquery->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
          $this->jsmquery->join('LEFT', '#__sportsmanagement_round AS r ON r.id = mc.round_id');
          $this->jsmquery->join('LEFT', '#__sportsmanagement_treeto_match AS ttm ON mc.id = ttm.match_id ');
       
          $this->jsmquery->where('ttm.node_id = ' . $this->jsmjinput->get('nid'));
       
          $this->jsmquery->order('mc.id');           
        
       
        $this->jsmdb->setQuery($this->jsmquery);
        if (!$result = $this->jsmdb->loadObjectList() ) {
            $this->setError($this->jsmdb->getErrorMsg());
            return false;
        }
        else
        {
            return $result;
        }
        
        
    }
}
