<?php 
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Router\Route;

jimport( 'joomla.application.component.view' );

class JoomleagueViewClubplan extends JLGView
{

	function display($tpl = null)
	{

		$document = JFactory::getDocument();
		$document->link = Route::_('index.php?option=com_sportsmanagement');
		$model = $this->getModel();
		$config = $model->getTemplateConfig($this->getName());
		$this->config = $config;
		$this->overallconfig = $model->getOverallConfig();
		$this->homematches = $model->getHomeMatches($config['HomeMatchesOrderBy']);
		$this->awaymatches = $model->getAwayMatches($config['AwayMatchesOrderBy']);
		$this->project = $model->getProject();

		$this->matches = (array_merge($this->homematches,$this->awaymatches));
		foreach ($this->matches as $game)
		{
				
			$item = new JFeedItem();
			$team1 = $game->tname1;
			$team2 = $game->tname2;
			$date = ( $game->match_date ? date( 'r', strtotime($game->match_date) ) : '' );
			$result = $game->cancel>0 ?$game->cancel_reason : $game->team1_result . "-" . $game->team2_result;
			if (!empty($game->team1_result))
			{
				$link = 'index.php?option=com_sportsmanagement&view=matchreport&p=';
			}
			else
			{
				$link = 'index.php?option=com_sportsmanagement&view=nextmatch&p=';
			}

			$item->title 		= $team1. " - ".$team2. " : ".$result;
			$item->link 		= Route::_( $link . $game->project_id . '&mid=' . $game->id);
			$item->description 	= $game->summary;
			$item->date			= $date;
			$item->category   	= "clubplan";
			// loads item info into rss array
			$document->addItem( $item );

		}
	}

}

?>