<?php
/**
 * @copyright    Copyright (C) 2013 fussballineuropa.de. All rights reserved.
 * @license        GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

use Joomla\CMS\MVC\Model\ListModel;

/**
 * Sportsmanagement Component TeamStaffs Model
 *
 * @author  diddipoeler <kurtnorgaz@web.de>
 * @package Sportsmanagement
 * @since   1.5.01a
 */
class sportsmanagementModelTeamStaffs extends ListModel
{
    var $_identifier = "teamstaffs";
    var $_project_id = 0;
    var $_season_id = 0;
    var $_team_id = 0;
    var $_project_team_id = 0;

    function getListQuery()
    {
        $option = Factory::getApplication()->input->getCmd('option');
        $app = Factory::getApplication();
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
      
        $this->_project_id    = $app->getUserState("$option.pid", '0');
        $this->_season_id    = $app->getUserState("$option.season_id", '0');
        $this->_team_id        = Factory::getApplication()->input->getVar('team_id');
        $this->_project_team_id        = Factory::getApplication()->input->getVar('project_team_id');
      
        if (!$this->_team_id ) {
            $this->_team_id    = $app->getUserState("$option.team_id", '0');
        }
        if (!$this->_project_team_id ) {
            $this->_project_team_id    = $app->getUserState("$option.project_team_id", '0');
        }
      
        // Get the WHERE and ORDER BY clauses for the query
        $where = self::_buildContentWhere();
        $orderby = self::_buildContentOrderBy();
      
        if (COM_SPORTSMANAGEMENT_USE_NEW_TABLE ) {
            $query->select(
                array('ppl.firstname',
                'ppl.lastname',
                'ppl.nickname',
                            'ppl.injury',
                            'ppl.suspension',
                            'ppl.away',
                'ts.*',
                'u.name AS editor')
            )
                ->from('#__sportsmanagement_person AS ppl')
                ->join('INNER', '#__sportsmanagement_season_team_person_id AS ts on ts.person_id = ppl.id')
                ->join('LEFT', '#__users AS u ON u.id = ts.checked_out');  
        }
        else
        {
            $query->select(
                array('ppl.firstname',
                'ppl.lastname',
                'ppl.nickname',
                'ts.*',
                'u.name AS editor')
            )
                ->from('#__sportsmanagement_person AS ppl')
                ->join('INNER', '#__sportsmanagement_team_staff AS ts on ts.person_id = ppl.id')
                ->join('LEFT', '#__users AS u ON u.id = ts.checked_out');  
        }
      

        if ($where) {
            $query->where($where);
        }
        if ($orderby) {
            $query->order($orderby);
        }
      
        return $query;
    }

    function _buildContentOrderBy()
    {
        $option = Factory::getApplication()->input->getCmd('option');
        $app = Factory::getApplication();
        //$filter_order		= $app->getUserStateFromRequest($option.'ts_filter_order',		'filter_order',		'ppl.ordering',	'cmd');
        $filter_order        = $app->getUserStateFromRequest($option.'ts_filter_order', 'filter_order', 'ts.ordering', 'cmd');
        $filter_order_Dir    = $app->getUserStateFromRequest($option.'ts_filter_order_Dir', 'filter_order_Dir', '', 'word');
        if ($filter_order=='ppl.lastname') {
            $orderby='ppl.lastname '.$filter_order_Dir;
        }
        else
        {
            $orderby=''.$filter_order.' '.$filter_order_Dir.', ppl.lastname ';
        }
        return $orderby;
    }

    function _buildContentWhere()
    {
        $option         = $option = Factory::getApplication()->input->getCmd('option');
        $app        = Factory::getApplication();
        //$project_id		= $app->getUserState($option.'project');
        //$team_id		= $app->getUserState($option.'project_team_id');
        $filter_state    = $app->getUserStateFromRequest($option . 'ts_filter_state', 'filter_state', '', 'word');
        $search            = $app->getUserStateFromRequest($option.'ts_search', 'search', '', 'string');
        $search_mode    = $app->getUserStateFromRequest($option.'ts_search_mode', 'search_mode', '', 'string');
        $search            = JString::strtolower($search);
        $where=array();
        if (COM_SPORTSMANAGEMENT_USE_NEW_TABLE ) {
            $where[]="ppl.published = '1'";
            $where[]='ts.team_id='.$this->_team_id;
            $where[]='ts.season_id='.$this->_season_id;
            $where[]='ts.persontype=2';
        }
        else
        {
            $where[]='ts.projectteam_id='.$this->_project_team_id;
            $where[]="ppl.published = '1'";
        }
      
        if ($search) {
            if ($search_mode) {
                $where[]='LOWER(lastname) LIKE '.$this->_db->Quote($search.'%');
            }
            else
            {
                $where[]='LOWER(lastname) LIKE '.$this->_db->Quote('%'.$search.'%');
            }
        }

        if ($filter_state ) {
            if ($filter_state == 'P' ) {
                $where[] = 'ts.published = 1';
            }
            elseif ($filter_state == 'U' ) {
                $where[] = 'ts.published = 0';
            }
        }

        $where=(count($where) ? ''.implode(' AND ', $where) : '');
        return $where;
    }

  


    /**
     * remove staffs from team
     *
     * @param  $cids staff ids
     * @return int count of staffs removed
     */
    function remove($cids)
    {
        $count=0;
        foreach ($cids as $cid)
        {
            $object=&$this->getTable('teamstaff');
            if ($object->canDelete($cid) && $object->delete($cid)) {
                $count++;
            }
            else
            {
                $this->setError(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFFS_MODEL_ERROR_REMOVE_STAFF', $object->getError()));
            }
        }
        return $count;
    }

}
?>
