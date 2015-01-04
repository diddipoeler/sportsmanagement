<?php



// no direct access
defined('_JEXEC') or die('Restricted access');

//require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
require_once (dirname(__FILE__).DS.'calendarClass.php');


class modJSMCalendarHelper
{



	function showCal(&$params,$year,$month,$ajax=0,$modid) //this function returns the html of the calendar for a given month
	{
		//global $mainframe;
		$offset = 0; //$mainframe->getCfg('offset');
		$language= JFactory::getLanguage(); //get the current language
		$language->load( 'mod_joomleague_calendar' ); //load the language ini file of the module
		$article= $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_VALUEMATCH');
		$articles= $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_VALUEMATCHES'); //this strings are used for the titles of the links
		$article2= $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_MATCHTHISDAY');

		$cal = new JSMCalendar; //this object creates the html for the calendar
		$dayNamLen= $params->get('cal_length_days');

		$cal->dayNames = array(
		substr(JText::_( 'SUN' ),0,$dayNamLen),
		substr(JText::_( 'MON' ),0,$dayNamLen),
		substr(JText::_( 'TUE' ),0,$dayNamLen),
		substr(JText::_( 'WED' ),0,$dayNamLen),
		substr(JText::_( 'THU' ),0,$dayNamLen),
		substr(JText::_( 'FRI' ),0,$dayNamLen),
		substr(JText::_( 'SAT' ),0,$dayNamLen)
		);

		$cal->monthNames = array(
		JText::_( 'JANUARY' ),
		JText::_( 'FEBRUARY' ),
		JText::_( 'MARCH' ),
		JText::_( 'APRIL' ),
		JText::_( 'MAY' ),
		JText::_( 'JUNE' ),
		JText::_( 'JULY' ),
		JText::_( 'AUGUST' ),
		JText::_( 'SEPTEMBER' ),
		JText::_( 'OCTOBER' ),
		JText::_( 'NOVEMBER' ),
		JText::_( 'DECEMBER' )
		);

		$cal->startDay = $params->get('cal_start_day'); //set the startday (this is the day that appears in the first column). Sunday = 0
		//it is loaded from the language ini because it may vary from one country to another, in Spain
		//for example, the startday is Monday (1)
		$cal->lightbox = $params->get('lightbox');
		$cal->lightbox_on_pageload = $params->get('lightbox_on_pageload');
		$cal->prefix = $params->get('custom_prefix');
		$cal->usedteams = $params->get('usedteams');
		$cal->usedclubs = $params->get('usedclubs');
		$cal->params = $params;
		//set the link for the month, this will be the link for the calendar header (ex. December 2007)
		$cal->monthLink=JRoute::_('index.php?option=com_joomleague_calendar' . '&year=' . $year .
					'&month=' . $month . '&modid=' . $modid);
		$cal->modid= $modid;
		$cal->ajax = $ajax;
		$cal->getMatches($month,$year);
		$counter= Array();
		jimport('joomla.utilities.date');
		foreach ( $cal->matches as $row )
		{
			$created= new JDate($row['date'], -$offset);
			$createdYear=$created->toFormat('%Y');
			$createdMonth=$created->toFormat('%m');
			$createdDay=$created->toFormat('%d'); //have to use %d because %e doesn't works on windows
			$createdDate=$createdYear . $createdMonth . $createdDay; //this makes an unique variable for every day
			$counter[$createdDate]['createdYear'] = $createdYear;
			$counter[$createdDate]['createdMonth'] = $createdMonth;
			$counter[$createdDate]['createdDay'] = $createdDay;
			$counter[$createdDate]['tiptitle'] = $created->toFormat('%A, %d.%m.%Y');
			if (!isset($counter[$createdDate]['count'])) $counter[$createdDate]['count'] = 1;
			else $counter[$createdDate]['count'] += 1; //$counter[$date] counts the number of articles in each day, to display it as a title in the link of the day
		}
		foreach ($counter AS $createdDate => $val) {
			$title =  $counter[$createdDate]['tiptitle'].' :: ' .$counter[$createdDate]['count'] . ' ';
			$title .= ($counter[$createdDate]['count'] > 1)? $articles : $article;
			$title .= ' ' . $article2;
			$inject = $params->get('inject', 0);
			$update_module = $params->get('update_module', 0);
			$cal->linklist[$createdDate]['click']=	'jlCalmod_showhide(\'jlCalList-'.$modid.'\', \'jlcal_'
			.$counter[$createdDate]['createdYear'].'-'.$counter[$createdDate]['createdMonth'].'-'.$counter[$createdDate]['createdDay'].'-'.$modid
			.'\', \''.str_replace(' :: ', ': ',$title).'\', '.$inject.', '.$modid.');';
			//$cal->linklist[$createdDate]['click']=	'jlcnewDate('.$counter[$createdDate]['createdMonth'].",". $counter[$createdDate]['createdYear'].",".$modid."," .$day.');';
			$cal->linklist[$createdDate]['link']=	'javascript:void(0)';
			$cal->linklist[$createdDate]['link'].="\" title=\""; //the calendar class sets the links this way: <a href=" . THE LINK STRING . ">
			//so, the easiest way to add a title to that link is by setting THE LINK STRING = the link" title="the title
			//the result link would be <a href="the link" title="the title">
			$cal->linklist[$createdDate]['link'].= $title;

			//the above 3 lines output something like: 3 articles on this day. Or: 1 article on this day
		}


		return $cal->getMonthView($month,$year);

	}

	function getDate_byId($id)
	{
		global $mainframe;
		$offset = 0; // $mainframe->getCfg('offset');
		$prefix = $params->get('custom_prefix');
		$query=	' SELECT match_date' .
			' FROM #__joomleague_matches'.
			' WHERE match_id=\'' . $id . '\'';
		$query = ($prefix != '') ? str_replace('#__', $prefix, $query) : $query;
		$db = JFactory::getDbo();
		$db->setQuery($query);
		$row=& $db->loadObjectList();


		jimport('joomla.utilities.date');
		$created=new JDate($row[0]->match_date, -$offset);


		$createdYear=$created->toFormat('%Y');
		$createdMonth=$created->toFormat('%m');
		$createdDay=$created->toFormat('%d');

		$createdDate=Array($createdYear,$createdMonth,$createdDay);

		return $createdDate;
	}

	function showDropDown($params,$year,$month,$day,$ajax=0){

		$results= $this->setTheQuery($params,$year,$month,$day,$ajax,1);

		foreach($results as $key => $result){
			$created=new JDate($results[$key]->match_date);
			$createdYear= $created->toFormat('%Y');
			$createdMonth= $created->toFormat('%m');


			$results[$key]->year = $createdYear;
			$results[$key]->month = $createdMonth;

			$createdYear == $year ? $articleCounter[$createdYear]['now']= true : '';
			$createdMonth== $month ? $articleCounter[$createdYear][$createdMonth]['now']= true : '';
			if (!isset($articleCounter[$createdYear][$createdMonth]['total'])) $articleCounter[$createdYear][$createdMonth]['total'] = 0;
			if (!isset($articleCounter[$createdYear]['total'])) $articleCounter[$createdYear]['total'] = 0;
			$articleCounter[$createdYear][$createdMonth]['total']++;
			$articleCounter[$createdYear]['total']++;

		}

		return array($results,$articleCounter);
	}

}


class JSMCalendar extends PHPCalendar
{

	static $linklist; //this variable will be an array that contains all the links of the month
	static $prefix;
	static $params;
	static $matches = array();
	static $teams = array();
	static $teamslist = array();

	function addTeam($id, $name='', $pic='')
	{
		if (! array_key_exists($id, self::$teams)&& $id > 0){
			self::$teams[$id] 			= new stdclass;
			self::$teams[$id]->value 	= $id;
			self::$teams[$id]->name 	= $name;
			self::$teams[$id]->picture 	= $pic;
			self::$teamslist[] 			= self::$teams[$id];
		}
	}

	function getDateLink($day, $month, $year) //this function is called from getMonthView(month,year) to get the link of the given day
	{										  //if this function returns nothing (""), then getMonthView wont put a link on that day
		$link = "";
		if(strlen($month)<2)
		$month = '0'.$month;
		if(strlen($day)<2)
		$day = '0'.$day;

		$date= $year . $month . $day;
		if(isset(self::$linklist[$date]['link'])){
			$link=self::$linklist[$date]['link'];  //$this->linklist[$date] was set for every date in the foreach bucle at lines 50-83
		}

		return $link;
	}
	function getDateClick($day, $month, $year) //this function is called from getMonthView(month,year) to get the link of the given day
	{										  //if this function returns nothing (""), then getMonthView wont put a link on that day
		$link = "";
		if(strlen($month)<2)
		$month = '0'.$month;
		if(strlen($day)<2)
		$day = '0'.$day;

		$date= $year . $month . $day;
		if(isset(self::$linklist[$date]['click'])){
			$link=self::$linklist[$date]['click'];  //$this->linklist[$date] was set for every date in the foreach bucle at lines 50-83
		}

		return $link;
	}


	//Return the URL to link to in order to display a calendar for a given month/year.
	//this function is called to get the links of the two arrows in the header.
	function getCalendarLink($month, $year)
	{
		$getquery = JRequest::get('GET'); //get the GET query
		$calendarLink= JUri::current().'?'; //get the current url, without the GET query; and add "?", to set the GET vars

		foreach($getquery as $key => $value){  /*this bucle goes through every GET variable that was in the url*/
			if($key!='month' AND $key!='year' AND $key!='day' AND $value){ /*the month,year, and day Variables must be diferent of the current ones, because this is a link for a diferent month */
				$calendarLink.= $key . '=' . $value . '&amp;';
			}
		}
		$calendarLink.='month='.$month.'&amp;year='.$year; //add the month and the year that was passed to the function to the GET string
		return $calendarLink;
	}


	function jl_utf8_convert ($text, $fromenc='iso-8859-1', $toenc='UTF-8' )
	{
		if (strtolower($fromenc)==strtolower($toenc) || self::$params->get('convert', 0)==0) return $text;

		elseif (function_exists('iconv')) {
			return iconv($fromenc, $toenc, $text);
		}
		elseif (strtolower($fromenc) == 'iso-8859-1' && strtolower($toenc) == 'utf-8') {
			return utf8_encode($text);
		}
		elseif (strtolower($fromenc) == 'utf-8' && strtolower($toenc) == 'iso-8859-1') {
			return utf8_decode($text);
		}
		else return $text;
	}
	function sortObject($array, $comparefunction, $property = '')
	{
		$zcount=count($array);
		for($i=1;$i<$zcount;$i++){
			for ($a=$zcount-1;$a>=$i;$a--) {
				if($this->$comparefunction($array[$a-1]->$property,$array[$a]->$property)>0){
					$tempzal=$array[$a-1];
					$array[$a-1]=$array[$a];
					$array[$a]=$tempzal;
				}
			}
		}
		return $array;
	}
	function sortArray($array, $comparefunction, $property = ''){
		$zcount=count($array);
		for($i=1;$i<$zcount;$i++){
			for ($a=$zcount-1;$a>=$i;$a--) {
				if($this->$comparefunction($array[$a-1][$property],$array[$a][$property])>0){
					$tempzal=$array[$a-1];
					$array[$a-1]=$array[$a];
					$array[$a]=$tempzal;
				}
			}
		}
		return $array;
	}


	function asc($a,$b){
		if($a<$b)return -1;
		if($a==$b)return 0;
		return 1;
	}

	function desc($a,$b){
		if($a>$b)return -1;
		if($a==$b)return 0;
		return 1;
	}

	function getMatches($month, $year) 
    {
		$livescore = self::$params->get('livescore', '');
		$joomleague = self::$params->get('sportsmanagement', '');
		$caldates = array();
		$caldates['start'] = date("$year-$month-01 00:00:00");
		$caldates['end'] = date("$year-$month-31 23:59:59");
		$jlrows = array();
		$lsrows = array();
		$usejevents = self::$params->get('jevents', 0);
		if ( $usejevents == 1 ) {
			$day = 0;
			require_once (dirname(__FILE__).DS.'connectors'.DS.'jevents.php');
			JEventsConnector::getEntries($caldates, self::$params, self::$matches);
		}
		require_once (dirname(__FILE__).DS.'connectors'.DS.$joomleague.'.php');
		self::$params->prefix = $this->prefix;
		SportsmanagementConnector::getEntries ( $caldates, self::$params, self::$matches );
		if ($livescore != ''){
			require_once (dirname(__FILE__).DS.'connectors'.DS.'livescore.php');
			self::$params->prefix = $this->params->get('prefix_livescore', '');
			LivescoreConnector::getMatches ( $caldates, self::$params, self::$matches );
		}
		$matches = self::sortArray(self::$matches, 'asc', 'date');
		self::$matches = $matches;
		return $matches;
	}

	function matches_output($month, $year) {
		global $mainframe;
		$language= JFactory::getLanguage(); //get the current language
		$language->load( 'mod_joomleague_calendar' ); //load the language ini file of the module
		$article= $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_VALUEMATCH');
		$articles= $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_VALUEMATCHES'); //this strings are used for the titles of the links
		$article2= $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_MATCHTHISDAY');
		$noarticle= $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_NOMATCHES');
		$outstring = '';
		$todaystring = '';
		$matches = $this->matches;
		$div = '';
		$now = new JDate();
		$today = $now->toFormat('%Y-%m-%d');
		$todaytitle = '';
		$pm='';
		$offset = 0; // $mainframe->getCfg('offset');
		$update_module = $this->params->get('update_module', 0);
		$totalgamesstring = (count($matches)>0) ? count($matches) : $noarticle;
		$totalgamesstring .= ' ';
		$totalgamesstring .= (count($matches) > 1) ? $articles : $article;
		$totalgamesstring .=  ' ';
		$totalgamesstring .= $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_VALUEMATCHESMONTH') . ' ' .$this->monthNames[$month - 1] . ' ' . $year;
		$thistitle = ($todaytitle != '') ? $todaytitle : $totalgamesstring;
		$thistitle = $totalgamesstring;
		$format = array();
		$format[] = array('tag' => 'span', 'divid' => 'oldjlCalListTitle-'.$this->modid, 'class' => 'jlcal_hiddenmatches', 'text' => $totalgamesstring);
		$format[] = array('tag' => 'span', 'divid' => 'jlCalListTitle-'.$this->modid, 'class' => 'jlCalListTitle', 'text' => $thistitle);
		$format[] = array('tag' => 'span', 'divid' => 'jlCalListDayTitle-'.$this->modid, 'class' => 'jlCalListTitle', 'text' => '');
		for ($x=0;$x<count($matches);$x++) {
			$sclass = ($x%2) ? 'sectiontableentry1' : 'sectiontableentry2';
			$row = $matches[$x];
			$thispm = $row['project_id'].'_'.$row['matchcode'].'_'.$row['type'];
			$da= new JDate($row['date'], -$offset);
			if ($div !=$da->toFormat('%Y-%m-%d')) {
				$counter = 0;
				$div = $da->toFormat('%Y-%m-%d');
				$format[] = array('tag' => 'div', 'divid' => 'jlcal_'.$div."-".$this->modid, 'class' => 'jlcal_hiddenmatches');
				$format[] = array('tag' => 'table', 'divid' => 'jlcal_'.$div."-".$this->modid, 'class' => 'jlcal_result_table');
			}
			if($pm != $thispm) {
				$format[] = array('tag' => 'headingrow', 'text' => $row['headingtitle']);
				$roundname = $row['headingtitle'];
			}

			$pm = $thispm;
			$format[] = $row;
			$counter++;
			if (isset($matches[$x+1])) $nd= new JDate($matches[$x+1]['date'], -$offset);
			else $nd = false;
			if (!$nd || $nd->toFormat('%Y-%m-%d') != $da->toFormat('%Y-%m-%d')) {

				$pm = '';
				$format[] = array('tag' => 'tableend');
				$format[] = array('tag' => 'divend');
				$titletext = $counter;
				$titletext .= ' ';
				$titletext .= ($counter > 1)? $articles : $article;
				$titletext .= ' ';
				$titletext .= ($today == $da->toFormat('%Y-%m-%d')) ? $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_TODAY'): $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_AT');
				$titletext .= ' ' .$da->toFormat('%d').'. '.$this->monthNames[$month - 1] . ' ' . $year;
				$format[] = array('tag' => 'span', 'divid' => 'jlcaltitte_'.$div."-".$this->modid, 'class' => 'jlcal_hiddenmatches', 'text' => $titletext);

			}
		}


		return $format;
	}
	
    function output_teamlist() 
    {
		$teamslist = array();
		if(count($this->teams) > 0 && $this->params->get('show_teamslist', 0) == 1) {
			$teams = $this->sortObject($this->teamslist, 'asc', 'name');
			$teamslist[] = JHtml::_('select.option', 0, JText::_($this->params->get('teamslist_option')));
			foreach ($teams AS $id => $obj) {
				$teamslist[] = JHtml::_('select.option', $obj->value, JText::_($obj->name));
			}
		}
		return $teamslist;
	}
}
?>