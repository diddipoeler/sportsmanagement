<?php defined( '_JEXEC' ) or die( 'Restricted access' );


jimport( 'joomla.application.component.view' );

class sportsmanagementViewResults extends JView
{

	function display($tpl = null)
	{

		$document	= JFactory::getDocument();
		$option = JRequest::getCmd('option');
        $mainframe = JFactory::getApplication();
		$document->link = JRoute::_('index.php?option=com_sportsmanagement');
		$model = $this->getModel();
		$matches = $model->getMatches();
		sportsmanagementModelProject::setProjectID(JRequest::getInt('p',0));
		$config	= sportsmanagementModelProject::getTemplateConfig($this->getName());
		$project = sportsmanagementModelProject::getProject();
		
		$this->assignRef( 'config', 		$config);
		$this->assignRef( 'overallconfig',	sportsmanagementModelProject::getOverallConfig());
		$this->assignRef( 'matches',		$matches);
		$this->assignRef( 'project',		$project);
		$this->assignRef( 'teams',			sportsmanagementModelProject::getTeamsIndexedByPtid());
		$dates = $this->sortByDate();
		foreach( $dates as $date => $games )
		{
			foreach($games as $game)
			{
				$item = new JFeedItem();
				$team1 = $this->teams[$game->projectteam1_id];
				$team2 = $this->teams[$game->projectteam2_id];
				$date = ( $game->match_date ? date( 'r', strtotime($game->match_date) ) : '' );
				$result = $game->cancel>0 ?$game->cancel_reason : $game->team1_result . "-" . $game->team2_result;
				$item->title 		= $team1->name. " - ".$team2->name." : ".$result;
				$item->link 		= JRoute::_( 'index.php?option=com_sportsmanagement&view=matchreport&p=' .
				$game->project_id . '&mid=' . $game->id);
				$item->description 	= $game->summary;
				$item->date			= $date;
				$item->category   	= "results";
				// loads item info into rss array
				$document->addItem( $item );
			}
		}
	}

	/**
	 * return an array of matches indexed by date
	 *
	 * @return array
	 */
	public function sortByDate()
	{
		$dates=array();
		foreach ((array) $this->matches as $m)
		{
			$date=substr($m->match_date,0,10);
			if (!isset($dates[$date]))
			{
				$dates[$date]=array($m);
			}
			else
			{
				$dates[$date][]=$m;
			}
		}
		return $dates;
	}
}

?>