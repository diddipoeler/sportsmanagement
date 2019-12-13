<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage treetonode
 */


defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementViewTreetonode
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class sportsmanagementViewTreetonode extends sportsmanagementView
{
	
    /**
     * sportsmanagementViewTreetonode::init()
     * 
     * @return
     */
    function init(  )
	{
		if ( $this->getLayout() == 'edit' || $this->getLayout() == 'edit_3' || $this->getLayout() == 'edit_4' )
		{
			$this->_displayForm(  );
			return;
		}

		//parent::display( $tpl );
	}

	/**
	 * sportsmanagementViewTreetonode::_displayForm()
	 * 
	 * @return void
	 */
	function _displayForm(  )
	{
        $pid = $this->app->getUserState( $this->option . '.pid' );
        $tid = $this->app->getUserState( $this->option . '.tid' );

		$lists = array();
		
	//	$node = $this->get('data');
		$match = $this->model->getNodeMatch();
		
        //$total = $this->get('Total');
		//$pagination = $this->get('Pagination');
		//$projectws = $this->get( 'Data', 'project' );
        $mdlProject = BaseDatabaseModel::getInstance('Project', 'sportsmanagementModel');
		$projectws = $mdlProject->getProject($pid);
		
		$model = $this->getModel('project');
		$mdlTreetonodes = BaseDatabaseModel::getInstance("Treetonodes", "sportsmanagementModel");
		$team_id[] = HTMLHelper::_('select.option','0',Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM'));
		if( $projectteams = $mdlTreetonodes->getProjectTeamsOptions($pid) )
		{
			$team_id = array_merge($team_id,$projectteams);
		}
		$lists['team'] = $team_id;
		unset($team_id);

		$this->projectws = $projectws;
		$this->lists = $lists;
		$this->node = $this->item;
		$this->match = $match;

	}

}
?>