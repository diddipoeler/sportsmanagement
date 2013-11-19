<?php
/**
 * @copyright	Copyright (C) 2006-2011 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


jimport('joomla.application.component.modellist');

/**
 * Joomleague Component Seasons Model
 *
 * @package	JoomLeague
 * @since	0.1
 */
class sportsmanagementModelrosterpositions extends JModelList
{
	var $_identifier = "rosterpositions";
	
	protected function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		// Select some fields
		$query->select('obj.*');
		// From the hello table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_rosterposition as obj');
        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');
        if ($search)
		{
        $query->where(self::_buildContentWhere());
        }
		$query->order(self::_buildContentOrderBy());
 
		//$mainframe->enqueueMessage(JText::_('leagues query<br><pre>'.print_r($query,true).'</pre>'   ),'');
        return $query;
        
        
        
	}

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'l_filter_order','filter_order','obj.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'l_filter_order_Dir','filter_order_Dir','','word');
		if ($filter_order == 'obj.ordering')
		{
			$orderby=' obj.ordering '.$filter_order_Dir;
		}
		else
		{
			$orderby=' '.$filter_order.' '.$filter_order_Dir.',obj.ordering ';
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'l_filter_order','filter_order','obj.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'l_filter_order_Dir','filter_order_Dir','','word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'l_search','search','','string');
		$search=JString::strtolower($search);
		$where=array();
		if ($search)
		{
			$where[]='LOWER(obj.name) LIKE '.$this->_db->Quote('%'.$search.'%');
		}
		$where=(count($where) ? ' '.implode(' AND ',$where) : '');
		return $where;
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