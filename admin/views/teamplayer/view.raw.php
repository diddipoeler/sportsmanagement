<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

/**
 * AJAX View class for the Joomleague component
 *
 * @static
 * @package		Joomleague
 * @since 0.1
 */
class JoomleagueViewTeamPlayer extends JLGView
{
	/**
	 * view AJAX display method
	 * @return void
	 **/
	function display( $tpl = null )
	{
		// Get some data from the model
		$db	= JFactory::getDBO();

		$db->setQuery( "	SELECT	pl.id AS value,
									concat(pl.firstname, ' \'', pl.nickname, '\' ', pl.lastname, ' (', pl.birthday, ')') AS pid
							FROM #__joomleague_team_player AS plt
							INNER JOIN #__joomleague_project_team AS pt ON pt.id = plt.projectteam_id
							INNER JOIN #__joomleague_person AS pl ON pl.id=plt.person_id
							WHERE pt.project_id='" . JRequest::getVar( 'p' ) . "' AND pl.published = '1' ORDER BY pl.lastname");

		$dropdrowlistoptions =  JHTML::_( 'select.options', $db->loadObjectList(), 'value', 'pid' );

		echo $dropdrowlistoptions;
	}
}
?>