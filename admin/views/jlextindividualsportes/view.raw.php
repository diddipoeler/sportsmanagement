<?php defined('_JEXEC') or die('Restricted access'); // Check to ensure this file is included in Joomla!

jimport('joomla.application.component.view');

/**
 * AJAX View class for the Joomleague component
 *
 * @static
 * @author	Kurt Norgaz
 * @package	JoomLeague
 * @since	1.5.03a
 */

class JoomleagueViewMatches extends JLGView
{
	/**
	* view AJAX display method
	* @return void
	**/
	function display($tpl=null)
	{
		// Get some data from the model
		$db =& JFactory::getDBO();
		$db->setQuery("	SELECT	m.match_id AS value,
							CONCAT('(',m.match_date,') - ',t1.middle_name,' - ',t2.middle_name) AS mid
							FROM #__joomleague_match m
							JOIN #__joomleague_team AS t1 ON m.team1=t1.id
							JOIN #__joomleague_team AS t2 ON m.team2=t2.id
							WHERE m.project_id='".JRequest::getVar('p')."'
							ORDER BY t1.short_name");

		$dropdrowlistoptions=JHTML::_('select.options',$db->loadObjectList(),'value','mid');
		echo $dropdrowlistoptions;
	}

}
?>