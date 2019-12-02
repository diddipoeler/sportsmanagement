<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      search_sportsmanagement.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage plugins
 */

/**
 * Search plugin
 * 1) onContentSearch($keyword,$match,$ordering,$areas)
 * 2) onContentSearchAreas()
 * These events are triggered in 'SearchModelSearch' class in file 'search.php' at location
 * 'Joomla_base\components\com_search\models'.
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if (! defined('JSM_PATH'))
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

// Import library dependencies
jimport( 'joomla.plugin.plugin' );
require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_sportsmanagement'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'countries.php' );

// prüft vor Benutzung ob die gewünschte Klasse definiert ist
if ( !class_exists('sportsmanagementHelper') ) 
{
//add the classes for handling
$classpath = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.JSM_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'sportsmanagement.php';
JLoader::register('sportsmanagementHelper', $classpath);
JModelLegacy::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

$paramscomponent = JComponentHelper::getParams( 'com_sportsmanagement' );

if (! defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE'))
{
DEFINE( 'COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE',$paramscomponent->get( 'cfg_which_database' ) );
}

if ( $paramscomponent->get('cfg_dbprefix') && !defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER') )
{
DEFINE( 'COM_SPORTSMANAGEMENT_PICTURE_SERVER',$paramscomponent->get( 'cfg_which_database_server' ) );    
}
else
{
if ( COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE || Factory::getApplication()->input->getInt( 'cfg_which_database', 0 ) )
{
if (! defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER'))
{    
DEFINE( 'COM_SPORTSMANAGEMENT_PICTURE_SERVER',$paramscomponent->get( 'cfg_which_database_server' ) );
}    
}
else
{
if (! defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER'))
{        
DEFINE( 'COM_SPORTSMANAGEMENT_PICTURE_SERVER',JURI::root() );
}    
}

}


/**
 * plgSearchsearch_sportsmanagement
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class plgSearchsearch_sportsmanagement extends JPlugin
{



	/**
	 * plgSearchsearch_sportsmanagement::plgSearchsearch_sportsmanagement()
	 * 
	 * @param mixed $subject
	 * @param mixed $params
	 * @return
	 */
	public function plgSearchsearch_sportsmanagement(&$subject, $params)
	{
		parent::__construct($subject, $params);
		// load language file for frontend
		//JPlugin::loadLanguage( 'plg_search_sportsmanagement', JPATH_ADMINISTRATOR );
        $this->loadLanguage();
	}

	/**
	 * plgSearchsearch_sportsmanagement::onContentSearchAreas()
	 * 
	 * @return
	 */
	function onContentSearchAreas()
	{
		static $areas = array();

	if (empty($areas))
	{
		$areas['search_sportsmanagement'] = JText::_('PLG_SEARCH_SPORTSMANAGEMENT_XML');
	}

	return $areas;
	}

	/**
	 * plgSearchsearch_sportsmanagement::onContentSearch()
	 * 
	 * @param mixed $text
	 * @param string $phrase
	 * @param string $ordering
	 * @param mixed $areas
	 * @return
	 */
	function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		// Reference global application object
        $app = Factory::getApplication();
        //$db 	= Factory::getDBO();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
		$user	= Factory::getUser();

		// load plugin params info
		$plugin			= JPluginHelper::getPlugin('search','search_sportsmanagement');
		$search_clubs 		= $this->params->def('search_clubs', 		1 );
		$search_teams 		= $this->params->def('search_teams', 		1 );
		$search_players 	= $this->params->def('search_players', 	1 );
		$search_playgrounds	= $this->params->def('search_playgrounds', 1 );
		$search_staffs	 	= $this->params->def('search_staffs',	 	1 );
		$search_referees	= $this->params->def('search_referees',	1 );
        $search_projects	= $this->params->def('search_projects',	1 );
		$text = trim( $text );
       
if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
$escape = 'escape';
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
$escape = 'getEscaped';
} 

		if ($text == '') {
			return array();
		}

		$wheres = array();

		switch ($phrase)
		{

			case 'any':
			default:
				$words = explode( ' ', $text );
				$wheres = array();
				$wheresteam = array();
				$wheresperson = array();
				$wheresplayground = array();
                $wheresproject = array();

				if ( $search_clubs )
				{
					foreach ($words as $word)
					{
						$word		= $db->Quote( '%'.$db->$escape( $word, true ).'%', false );
						$wheres2 	= array();
						$wheres2[] 	= 'c.name LIKE '.$word;
						$wheres2[] 	= 'c.alias LIKE '.$word;
						$wheres2[] 	= 'c.location LIKE '.$word;
                        $wheres2[] 	= 'c.unique_id LIKE '.$word;

						$wheres[] 	= implode( ' OR ', $wheres2 );
					}
				}


				if ( $search_teams )
				{
					foreach ($words as $word)
					{
						$word		= $db->Quote( '%'.$db->$escape( $word, true ).'%', false );
						$wheres2 	= array();
						$wheres2[] 	= 't.name LIKE '.$word;
						$wheresteam[] 	= implode( ' OR ', $wheres2 );
					}
				}

				if ( $search_players || $search_referees || $search_staffs)
				{
					foreach ($words as $word)
					{
						$word		= $db->Quote( '%'.$db->$escape( $word, true ).'%', false );
						$wheres2 	= array();
						$wheres2[] 	= 'pe.firstname LIKE '.$word;
						$wheres2[] 	= 'pe.lastname LIKE '.$word;
						$wheres2[] 	= 'pe.nickname LIKE '.$word;
						$wheresperson[] 	= implode( ' OR ', $wheres2 );
					}
				}

				if ( $search_playgrounds )
				{
					foreach ($words as $word)
					{
						$word		= $db->Quote( '%'.$db->$escape( $word, true ).'%', false );
						$wheres2 	= array();
						$wheres2[] 	= 'pl.name LIKE '.$word;
						$wheres2[] 	= 'pl.city LIKE '.$word;


						$wheresplayground[] 	= implode( ' OR ', $wheres2 );
					}
				}
                
                if ( $search_projects )
				{
					foreach ($words as $word)
					{
						$word		= $db->Quote( '%'.$db->$escape( $word, true ).'%', false );
						$wheres2 	= array();
						$wheres2[] 	= 'pro.name LIKE '.$word;
						$wheres2[] 	= 'pro.staffel_id LIKE '.$word;


						$wheresproject[] 	= implode( ' OR ', $wheres2 );
					}
				}


				$where = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
				$whereteam = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheresteam ) . ')';
				$wheresperson = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheresperson ) . ')';
				$wheresplayground = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheresplayground ) . ')';
                $wheresproject = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheresproject ) . ')';
				break;


		}


		$rows = array();

/**
 * 		hier werden die vereine gesucht
 */
        if ( $search_clubs )
		{
			$query->clear();
            $query->select('\'Club\' as section, c.name AS title');
            $query->select('c.founded AS created,c.country,c.logo_big AS picture');
            $query->select('CONCAT( \'Address: \',c.address,\' \',c.zipcode,\' \',c.location,\' Phone: \',c.phone,\' Fax: \',c.fax,\' E-Mail: \',c.email,\' Vereinsnummer: \',c.unique_id ) AS text');
            $query->select('pt.project_id AS project');
            $query->select('CONCAT( \'index.php?option=com_sportsmanagement&view=clubinfo&cfg_which_database=0&s=0&p=\', CONCAT_WS(\':\',p.id,p.alias) ,\'&cid=\', CONCAT_WS(\':\',c.id,c.alias) ) AS href');
            $query->select('2 AS browsernav');
            
            $query->from('#__sportsmanagement_club AS c');
            $query->join('INNER', '#__sportsmanagement_team AS t on t.club_id = c.id');
            $query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
            $query->join('INNER',' #__sportsmanagement_project_team AS pt ON pt.team_id = st.id ');
            $query->join('INNER',' #__sportsmanagement_project AS p ON p.id = pt.project_id ');

			$query->where(" ( ".$where." ) ");
            $query->group('c.name');
            $query->order('c.name');
            
            $db->setQuery( $query );
			$list = $db->loadObjectList();
            
            /**
            * hier bereiten wir den text auf,
            * damit wir auch bilder und flaggen sehen
            */        

		      foreach ($list as $row)
                {
                //$row->title = JSMCountries::getCountryFlag($row->country).' '.$row->title;    
                $row->text = '<img src="'.COM_SPORTSMANAGEMENT_PICTURE_SERVER.DIRECTORY_SEPARATOR.$row->picture.'" alt="..." class="img-rounded" width="50">'.JSMCountries::getCountryFlag($row->country).' '.$row->text;
                }
			     $rows[] = $list;
		 }

/**
 * 		hier werden die mannschaften gesucht
 */
        if ( $search_teams )
		{
			$query->clear();
            $query->select('\'Team\' as section, t.name AS title');
            $query->select('t.checked_out_time AS created,st.picture,c.country');
            $query->select('CONCAT( \'Teamart:\',t.info , \' Notes:\', t.notes ) AS text');
            $query->select('pt.project_id AS project');
            $query->select('CONCAT( \'index.php?option=com_sportsmanagement&view=teaminfo&cfg_which_database=0&s=0&p=\', CONCAT_WS(\':\',p.id,p.alias) ,\'&tid=\', CONCAT_WS(\':\',t.id,t.alias) ) AS href');
            $query->select('2 AS browsernav');
            
            $query->from('#__sportsmanagement_team AS t');
            $query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
            $query->join('INNER',' #__sportsmanagement_project_team AS pt ON pt.team_id = st.id ');
            $query->join('INNER',' #__sportsmanagement_project AS p ON p.id = pt.project_id ');
            $query->join('INNER', '#__sportsmanagement_club AS c on c.id = t.club_id');

			$query->where(" ( ".$whereteam." ) ");
            $query->group('t.name');
            $query->order('t.name');
            
            $db->setQuery( $query );
			$list = $db->loadObjectList();
			$rows[] = $list;
		}


/**
 * 		hier werden die spieler gesucht
 */
        if ( $search_players )
		{

			$query->clear();
            $query->select("'Person' as section, REPLACE(CONCAT(pe.firstname, ' \'', pe.nickname, '\' ' , pe.lastname ),'\'\'','') AS title");
            $query->select('pe.birthday AS created');
            $query->select('pe.country');
            $query->select('pe.picture AS picture');
            $query->select('CONCAT( \'Birthday:\',pe.birthday , \' Notes:\', pe.notes ) AS text');
            $query->select('pt.project_id AS project');
            $query->select('CONCAT( \'index.php?option=com_sportsmanagement&view=player&cfg_which_database=0&s=0&p=\', CONCAT_WS(\':\',p.id,p.alias) ,\'&tid=\', CONCAT_WS(\':\',t.id,t.alias) , \'&pid=\', CONCAT_WS(\':\',pe.id,pe.alias) ) AS href');
            $query->select('2 AS browsernav');
			
            $query->from('#__sportsmanagement_person AS pe');
            $query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp on tp.person_id = pe.id');
            $query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id and st.season_id = tp.season_id');
            $query->join('INNER',' #__sportsmanagement_project_team AS pt ON pt.team_id = st.id ');
            $query->join('INNER',' #__sportsmanagement_team AS t ON t.id = st.team_id ');
            $query->join('INNER',' #__sportsmanagement_project AS p ON p.id = pt.project_id ');
        
            $query->where(" ( ".$wheresperson." ) ");
            $query->where('pe.published = 1');
            $query->where('tp.persontype = 1');
            $query->group('pe.lastname, pe.firstname, pe.nickname');
            $query->order('pe.lastname,pe.firstname,pe.nickname');

            $db->setQuery( $query );
			$list = $db->loadObjectList();
			$rows[] = $list;

		}

/**
 * 		hier werden die mitarbeiter gesucht
 */
        if ( $search_staffs )
		{

			$query->clear();
            $query->select("'Person' as section, REPLACE(CONCAT(pe.firstname, ' \'', pe.nickname, '\' ' , pe.lastname ),'\'\'','') AS title");
            $query->select('pe.birthday AS created');
            $query->select('pe.country');
            $query->select('pe.picture AS picture');
            $query->select('CONCAT( \'Birthday:\',pe.birthday , \' Notes:\', pe.notes ) AS text');
            $query->select('pt.project_id AS project');
            $query->select('CONCAT( \'index.php?option=com_sportsmanagement&view=player&cfg_which_database=0&s=0&p=\', CONCAT_WS(\':\',p.id,p.alias) ,\'&tid=\', CONCAT_WS(\':\',t.id,t.alias) , \'&pid=\', CONCAT_WS(\':\',pe.id,pe.alias) ) AS href');
            $query->select('2 AS browsernav');
			
            $query->from('#__sportsmanagement_person AS pe');
            $query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp on tp.person_id = pe.id');
            $query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id and st.season_id = tp.season_id');
            $query->join('INNER',' #__sportsmanagement_project_team AS pt ON pt.team_id = st.id ');
            $query->join('INNER',' #__sportsmanagement_team AS t ON t.id = st.team_id ');
            $query->join('INNER',' #__sportsmanagement_project AS p ON p.id = pt.project_id ');
        
            $query->where(" ( ".$wheresperson." ) ");
            $query->where('pe.published = 1');
            $query->where('tp.persontype = 2');
            $query->group('pe.lastname, pe.firstname, pe.nickname');
            $query->order('pe.lastname,pe.firstname,pe.nickname');

			$db->setQuery( $query );
			$list = $db->loadObjectList();
			$rows[] = $list;

		}

/**
 * 		hier werden die schiedsrichter gesucht
 */
        if ( $search_referees )
		{
		  $query->clear();
          $query->select("'Referee' as section, REPLACE(CONCAT(pe.firstname, ' \'', pe.nickname, '\' ' , pe.lastname ),'\'\'','') AS title");
          $query->select('pe.birthday AS created,pe.country,pe.picture AS picture,CONCAT( \'Birthday:\', pe.birthday, \' Notes:\', pe.notes ) AS text');
          $query->select('pr.project_id AS project');
          $query->select('CONCAT( \'index.php?option=com_sportsmanagement&view=referee&cfg_which_database=0&s=0&p=\', CONCAT_WS(\':\',pro.id,pro.alias) ,\'&pid=\',CONCAT_WS(\':\',pe.id,pe.alias) ) AS href');
          $query->select('2 AS browsernav');
          
          $query->from('#__sportsmanagement_person AS pe');
          $query->join('LEFT','#__sportsmanagement_season_person_id AS o ON o.person_id = pe.id');
          $query->join('LEFT','#__sportsmanagement_project_referee AS pr ON pr.person_id = o.id');
          $query->join('LEFT','#__sportsmanagement_project AS pro ON pr.project_id = pro.id');
			
			$query->where(" ( ".$wheresperson." ) ");
            $query->where(" pe.published = 1 ");
            $query->group('pe.lastname, pe.firstname, pe.nickname');
            $query->order('pe.lastname,pe.firstname,pe.nickname');
            
            $db->setQuery( $query );
			$list = $db->loadObjectList();
			$rows[] = $list;

		}

/**
 * 		hier werden die sportstätten gesucht
 */
        if ( $search_playgrounds )
		{
		  $query->clear();
          $query->select("'Playground' as section, pl.name AS title");
            $query->select('pl.checked_out_time AS created,pl.country,pl.picture AS picture,pl.notes AS text');
            $query->select('r.project_id AS project');
            $query->select('CONCAT( \'index.php?option=com_sportsmanagement&view=playground&cfg_which_database=0&s=0&p=\', CONCAT_WS(\':\',pro.id,pro.alias) ,\'&pgid=\',CONCAT_WS(\':\',pl.id,pl.alias) ) AS href');
            $query->select('2 AS browsernav');
			
            $query->from('#__sportsmanagement_playground AS pl');
            $query->join('LEFT', '#__sportsmanagement_club AS c ON c.id = pl.club_id');
            $query->join('LEFT', '#__sportsmanagement_match AS m ON m.playground_id = pl.id');
            $query->join('LEFT', '#__sportsmanagement_round AS r ON m.round_id = r.id');
            $query->join('LEFT', '#__sportsmanagement_project AS pro ON r.project_id = pro.id');


			$query->where(" ( ".$wheresplayground." ) ");
            $query->group('pl.name');
            $query->order('pl.name');
            
            $db->setQuery( $query );
			$list = $db->loadObjectList();
			$rows[] = $list;
		}
        
/**
 *         hier werden die projekte gesucht
 */
        if ( $search_projects )
		{
		    $query->clear();
            $query->select("'Project' as section, pro.name AS title");
            $query->select('pro.checked_out_time AS created');
            $query->select('l.country');
            $query->select('pro.picture AS picture');
            $query->select('CONCAT( pro.name, \' Staffel-ID (\', pro.staffel_id, \')\' ) AS text');
            $query->select('pro.id AS project');
            $query->select('CONCAT( \'index.php?option=com_sportsmanagement&view=ranking&cfg_which_database=0&s=0&p=\', CONCAT_WS(\':\',pro.id,pro.alias) ,\'&type=0\' ) AS href');
            $query->select('2 AS browsernav');
			
            $query->from('#__sportsmanagement_project AS pro');
            $query->join('INNER', '#__sportsmanagement_league AS l ON l.id = pro.league_id');


			$query->where(" ( ".$wheresproject." ) ");
            $query->group('pro.name');
            $query->order('pro.name');
            
            $db->setQuery( $query );
			$list = $db->loadObjectList();
			$rows[] = $list;
        }
        
        $results = array();

		if(count($rows))
		{
		  foreach($rows as $row)
			{
			if ( $row )
			{
			foreach($row as $output )
			{
            $output->href = Route::_($output->href);
			if ( $output->country)
			{
			$flag = JSMCountries::getCountryFlag($output->country);
			$output->flag = $flag;
			$output->text = $flag.' '.$output->text ;
			}
      if ( $output->picture )
			{
			$output->text = '<p><img style="float: left;" src="'.$output->picture.'" alt="" width="50" height="" >'.$output->text.'</p>';
			}
			}
			}
			}
			
			foreach($rows as $row)
			{
				$results = array_merge($results, (array) $row);
			}
		}
		return $results;
	}
}
?>
