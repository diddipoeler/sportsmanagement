<?php
// No direct access to this file
defined('_JEXEC') or die;
 
/**
 * SportsManagement component helper.
 */
abstract class sportsmanagementHelper
{
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($submenu) 
	{
	   $mainframe	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
        $show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        // retrieve the value of the state variable. If no value is specified,
        // the specified default value will be returned.
        // function syntax is getUserState( $key, $default );
        $project_id = $mainframe->getUserState( "$option.pid", '0' );
        $project_team_id = $mainframe->getUserState( "$option.project_team_id", '0' );
        $team_id = $mainframe->getUserState( "$option.team_id", '0' );
        $club_id = $mainframe->getUserState( "$option.club_id", '0' );
      
        
        
        if ( $show_debug_info )
        {
            $mainframe->enqueueMessage(JText::_('addSubmenu post<br><pre>'.print_r(JRequest::get('post'),true).'</pre>'),'');
            $mainframe->enqueueMessage(JText::_('addSubmenu project_id<br><pre>'.print_r($project_id,true).'</pre>'),'');
            $mainframe->enqueueMessage(JText::_('addSubmenu project_team_id<br><pre>'.print_r($project_team_id,true).'</pre>'),'');
            $mainframe->enqueueMessage(JText::_('addSubmenu team_id<br><pre>'.print_r($team_id,true).'</pre>'),'');
            $mainframe->enqueueMessage(JText::_('addSubmenu club_id<br><pre>'.print_r($club_id,true).'</pre>'),'');
        }
        
		
        JSubMenuHelper::addEntry(JText::_('COM_SPORTSMANAGEMENT_MENU'), 'index.php?option=com_sportsmanagement', $submenu == 'menu');
		
        JSubMenuHelper::addEntry(JText::_('COM_SPORTSMANAGEMENT_SUBMENU_EXTENSIONS'), 'index.php?option=com_sportsmanagement&view=extensions', $submenu == 'extensions');
        
        JSubMenuHelper::addEntry(JText::_('COM_SPORTSMANAGEMENT_SUBMENU_PROJECTS'), 'index.php?option=com_sportsmanagement&view=projects', $submenu == 'projects');
        
        if ( $project_id )
        {
        JSubMenuHelper::addEntry(JText::_('COM_SPORTSMANAGEMENT_SUBMENU_PROJECTS_DETAILS'), 'index.php?option=com_sportsmanagement&view=project&layout=panel&id='.$project_id, $submenu == 'project');
        }
        
        JSubMenuHelper::addEntry(JText::_('COM_SPORTSMANAGEMENT_SUBMENU_PREDICTIONS'), 'index.php?option=com_sportsmanagement&view=predictions', $submenu == 'predictions');
        
        JSubMenuHelper::addEntry(JText::_('COM_SPORTSMANAGEMENT_SUBMENU_CURRENT_SEASONS'), 'index.php?option=com_sportsmanagement&view=currentseasons', $submenu == 'currentseasons');
        
		// set some global property
		$document = JFactory::getDocument();
		$document->addStyleDeclaration('.icon-48-helloworld {background-image: url(../media/com_sportsmanagement/images/tux-48x48.png);}');
		if ($submenu == 'extensions') 
		{
			$document->setTitle(JText::_('COM_SPORTSMANAGEMENT_ADMINISTRATION_EXTENSIONS'));
		}
	}
	/**
	 * Get the actions
	 */
	public static function getActions($messageId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;
 
		if (empty($messageId)) {
			$assetName = 'com_sportsmanagement';
		}
		else {
			$assetName = 'com_sportsmanagement.message.'.(int) $messageId;
		}
 
		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.delete'
		);
 
		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}
 
		return $result;
	}
    
  
  	/**
	 * support for extensions which can overload extended data
	 * @param string $data
	 * @param string $file
	 * @return object
	 */
	static function getExtended($data='', $file, $format='ini') {
		$xmlfile = JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'extended'.DS.$file.'.xml';
		
    /*
    //extension management
		$extensions = sportsmanagementHelper::getExtensions(JRequest::getInt('p'));
		foreach ($extensions as $e => $extension) {
			$JLGPATH_EXTENSION = JPATH_COMPONENT_SITE.DS.'extensions'.DS.$extension.DS.'admin';
			//General extension extended xml 
			$file = $JLGPATH_EXTENSION.DS.'assets'.DS.'extended'.DS.$file.'.xml';
			if(file_exists(JPath::clean($file))) {
				$xmlfile = $file;
				break; //first extension file will win
			}
		}
		*/
		
		/*
		 * extended data
		*/
		
        $jRegistry = new JRegistry;
		//$jRegistry->loadString($data, $format);
        $jRegistry->loadJSON($data);
		$extended = JForm::getInstance('extended', $xmlfile,
				array('control'=> 'extended'),
				false, '/config');
		$extended->bind($jRegistry);
		return $extended;
	}
    
	/**
	 * Method to return a project array (id,name)
	 *
	 * @access	public
	 * @return	array project
	 * @since	1.5
	 */
	function getProjects()
	{
		$db = JFactory::getDBO();

		$query='	SELECT	id,
							name

					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project
					ORDER BY ordering, name ASC';

		$db->setQuery($query);

		if (!$result=$db->loadObjectList())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * Method to return the project teams array (id,name)
	 *
	 * @access	public
	 * @return	array
	 * @since	0.1
	 */
	function getProjectteams($project_id)
	{
		$db = JFactory::getDBO();
		$query='	SELECT	pt.id AS value,
							t.name AS text,
							t.notes

					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id=t.id
					WHERE pt.project_id='.$project_id.'
					ORDER BY name ASC ';

		$db->setQuery($query);
		if (!$result=$db->loadObjectList())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * Method to return the project teams array (id,name)
	 *
	 * @access	public
	 * @return	array
	 * @since	1.5.03a
	 */
	function getProjectteamsNew($project_id)
	{
		$db = JFactory::getDBO();

		$query='	SELECT	pt.team_id AS value,
							t.name AS text,
							t.notes

					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id=t.id
					WHERE pt.project_id='.(int) $project_id.'
					ORDER BY name ASC ';

		$db->setQuery($query);
		if (!$result=$db->loadObjectList())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}

	function getProjectFavTeams($project_id)
	{
		$db = JFactory::getDBO();

		$query='	SELECT fav_team,
							fav_team_color,
							fav_team_text_color,
							fav_team_highlight_type,
							fav_team_text_bold

					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project
					WHERE id='.(int) $project_id;

		$db->setQuery($query);
		if (!$result=$db->loadObject())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}

	
    /**
	 * Method to return the project
	 *
	 * @access	public
	 * @return	array project
	 * @since	1.5
	 */
	function getTeamplayerProject($projectteam_id)
	{
		$db = JFactory::getDBO();
		$query='SELECT project_id FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team WHERE id='.(int) $projectteam_id;
		$db->setQuery($query);
		if (!$result=$db->loadResult())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		return $result;
	}
    
    /**
	 * Method to return a SportsType name
	 *
	 * @access	public
	 * @return	array project
	 * @since	1.5
	 */
	function getSportsTypeName($sportsType)
	{
		$db = JFactory::getDBO();
		$query='SELECT name FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type WHERE id='.(int) $sportsType;
		$db->setQuery($query);
		if (!$result=$db->loadResult())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		return JText::_($result);
	}

	/**
	 * Method to return a sportsTypees array (id,name)
	 *
	 * @access	public
	 * @return	array seasons
	 * @since	1.5.0a
	 */
	function getSportsTypes()
	{
		$db = JFactory::getDBO();
		$query='SELECT id, name FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type ORDER BY name ASC ';
		$db->setQuery($query);
		if (!$result=$db->loadObjectList())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		foreach ($result as $sportstype){
			$sportstype->name=JText::_($sportstype->name);
		}
		return $result;
	}

	/**
	 * Method to return a SportsType name
	 *
	 * @access	public
	 * @return	array project
	 * @since	1.5
	 */
	function getPosPersonTypeName($personType)
	{
		switch ($personType)
		{
			case 2	:	$result =	JText::_('COM_SPORTSMANAGEMENT_F_TEAM_STAFF');
			break;
			case 3	:	$result =	JText::_('COM_SPORTSMANAGEMENT_F_REFEREES');
			break;
			case 4	:	$result =	JText::_('COM_SPORTSMANAGEMENT_F_CLUB_STAFF');
			break;
			default	:
			case 1	:	$result =	JText::_('COM_SPORTSMANAGEMENT_F_PLAYERS');
			break;
		}
		return $result;
	}

	/**
	 * return name of extension assigned to current project.
	 * @param int project_id
	 * @return string or false
	 */
	function getExtension($project_id=0)
	{
		$option='com_sportsmanagement';
		if (!$project_id)
		{
			$app=&JFactory::getApplication();
			$project_id=$app->getUserState($option.'project',0);
		}
		if (!$project_id){
			return false;
		}

		$db=&JFactory::getDBO();
		$query='SELECT extension FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project WHERE id='. $db->Quote((int)$project_id);
		$db->setQuery($query);
		$res=$db->loadResult();

		return (!empty($res) ? $res : false);
	}

	public static function getExtensions($project_id)
	{
		$option='com_sportsmanagement';
		$arrExtensions = array();
		$excludeExtension = array();
		if ($project_id) {
			$db= JFactory::getDBO();
			$query='SELECT extension FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project WHERE id='. $db->Quote((int)$project_id);

			$db->setQuery($query);
			$res=$db->loadObject();
			if(!empty($res)) {
				$excludeExtension = explode(",", $res->extension);
			}
		}
		if(JFolder::exists(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'extensions')) {
			$folderExtensions  = JFolder::folders(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'extensions',
													'.', false, false, $excludeExtension);
			if($folderExtensions !== false) {
				foreach ($folderExtensions as $ext)
				{
					$arrExtensions[] = $ext;
				}
			}
		}

		return $arrExtensions;
	}
	
		public static function getExtensionsOverlay($project_id)
	{
		$option='com_sportsmanagement';
		$arrExtensions = array();
		$excludeExtension = array();
		if ($project_id) {
			$db= JFactory::getDBO();
			$query='SELECT extension FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project WHERE id='. $db->Quote((int)$project_id);

			$db->setQuery($query);
			$res=$db->loadObject();
			if(!empty($res)) {
				$excludeExtension = explode(",", $res->extension);
			}
		}
		if(JFolder::exists(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'extensions-overlay')) {
			$folderExtensions  = JFolder::folders(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'extensions-overlay',
													'.', false, false, $excludeExtension);
			if($folderExtensions !== false) {
				foreach ($folderExtensions as $ext)
				{
					$arrExtensions[] = $ext;
				}
			}
		}

		return $arrExtensions;
	}

	/**
	 * returns number of years between 2 dates
	 *
	 * @param string $birthday date in YYYY-mm-dd format
	 * @param string $current_date date in YYYY-mm-dd format,default to today
	 * @return int age
	 */
	function getAge($date, $seconddate)
	{

		if ( ($date != "0000-00-00") &&
		(preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/',$date,$regs) ) &&
		($seconddate == "0000-00-00") )
		{
			$intAge=date('Y') - $regs[1];
			if($regs[2] > date('m'))
			{
				$intAge--;
			}
			else
			{
				if($regs[2] == date('m'))
				{
					if($regs[3] > date('d')) $intAge--;
				}
			}
			return $intAge;
		}

		if ( ($date != "0000-00-00") &&
		( preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/',$date,$regs) ) &&
		($seconddate != "0000-00-00") &&
		( preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/',$seconddate,$regs2) ) )
		{
			$intAge=$regs2[1] - $regs[1];
			if($regs[2] > $regs2[2])
			{
				$intAge--;
			}
			else
			{
				if($regs[2] == $regs2[2])
				{
					if($regs[3] > $regs2[3] ) $intAge--;
				}
			}
			return $intAge;
		}

		return '-';
	}

	/**
	 * returns the default placeholder
	 *
	 * @param string $type ,default is player
	 * @return string placeholder (path)
	 */
	public static function getDefaultPlaceholder($type="player")
	{	
		$params		 	=	JComponentHelper::getParams('com_sportsmanagement');
		$ph_player		=	$params->get('ph_player',0);
		$ph_logo_big	=	$params->get('ph_logo_big',0);	
		$ph_logo_medium	=	$params->get('ph_logo_medium',0);		
		$ph_logo_small	=	$params->get('ph_logo_small',0);
		$ph_icon		=	$params->get('ph_icon', 'images/com_sportsmanagement/database/placeholders/placeholder_21.png');			
		$ph_team		=	$params->get('ph_team',0);
		
			//setup the different placeholders
			switch ($type) {
				case "player": //player
					return $ph_player;
					break;
				case "clublogobig": //club logo big
					return $ph_logo_big;
					break;
				case "clublogomedium": //club logo medium
					return $ph_logo_medium;
					break;
				case "clublogosmall": //club logo small
					return $ph_logo_small;
					break;
				case "icon": //icon
					return $ph_icon;
					break;
				case "team": //team picture
					return $ph_team;;
					break;					
				default:
					$picture=null;
				break;
			}	
	}
	
	/**
	 *
	 * static method which return a <img> tag with the given picture
	 * @param string $picture
	 * @param string $alttext
	 * @param int $width=40, if set to 0 the original picture width will be used
	 * @param int $height=40, if set to 0 the original picture height will be used
	 * @param int $type=0, 0=player, 1=club logo big, 2=club logo medium, 3=club logo small
	 * @return string
	 */
	public static function getPictureThumb($picture, $alttext, $width=40, $height=40, $type=0)
	{
		$ret = "";
		$picturepath 	= 	JPath::clean(JPATH_SITE.DS.str_replace(JPATH_SITE.DS, '', $picture));
		$params		 	=	JComponentHelper::getParams('com_sportsmanagement');
		$ph_player		=	$params->get('ph_player',0);
		$ph_logo_big	=	$params->get('ph_logo_big',0);	
		$ph_logo_medium	=	$params->get('ph_logo_medium',0);		
		$ph_logo_small	=	$params->get('ph_logo_small',0);
		$ph_icon		=	$params->get('ph_icon',0);			
		$ph_team		=	$params->get('ph_team',0);
		
		if (!file_exists($picturepath) || $picturepath == JPATH_SITE.DS)
		{
			//setup the different placeholders
			switch ($type) {
				case 0: //player
					$picture=JPATH_SITE.DS.$ph_player;
					break;
				case 1: //club logo big
					$picture=JPATH_SITE.DS.$ph_logo_big;
					break;
				case 2: //club logo medium
					$picture=JPATH_SITE.DS.$ph_logo_medium;
					break;
				case 3: //club logo small
					$picture=JPATH_SITE.DS.$ph_logo_small;
					break;
				case 4: //icon
					$picture=JPATH_SITE.DS.$ph_icon;
					break;
				case 5: //team picture
					$picture=JPATH_SITE.DS.$ph_team;
					break;					
				default:
					$picture=null;
				break;
			}
		}
		if (!empty($picture))
		{
			$params = JComponentHelper::getParams('com_sportsmanagement');
			$format = "JPG"; //PNG is not working in IE8
			$format = $params->get('thumbformat', 'PNG');
			$bUseThumbLib = $params->get('usethumblib', false);
			if($bUseThumbLib) {
				if (file_exists($picturepath)) {
					$picture = $picturepath;
				}
				$thumb=PhpThumbFactory::create($picture);
				$thumb->setFormat($format);

				//height and width set, resize it with the thumblib
				if($height>0 && $width>0) {
					$thumb->setMaxHeight($height);
					$thumb->adaptiveResizeQuadrant ($width, $height, $quadrant = 'C');
					$pic=$thumb->getImageAsString();
					$ret .= '<img src="data:image/'.$format.';base64,'. base64_encode($pic);
					$ret .='" alt="'.$alttext.'" title="'.$alttext.'"/>';
				}
				//height==0 and width set, let the browser resize it
				if($height==0 && $width>0) {
					$thumb->setMaxWidth($width);
					$pic=$thumb->getImageAsString();
					$ret .= '<img src="data:image/'.$format.';base64,'. base64_encode($pic);
					$ret .='" width="'.$width.'" alt="'.$alttext.'" title="'.$alttext.'"/>';
				}
				//width==0 and height set, let the browser resize it
				if($height>0 && $width==0) {
					$thumb->setMaxHeight($height);
					$pic=$thumb->getImageAsString();
					$ret .= '<img src="data:image/'.$format.';base64,'. base64_encode($pic);
					$ret .='" height="'.$height.'" alt="'.$alttext.'" title="'.$alttext.'"/>';
				}
				//width==0 and height==0, use original picture size
				if($height==0 && $width==0) {
					$thumb->setMaxHeight($height);
					$pic=$thumb->getImageAsString();
					$ret .= '<img src="data:image/'.$format.';base64,'. base64_encode($pic);
					$ret .='" alt="'.$alttext.'" title="'.$alttext.'"/>';
				}
			} else {
				$picture = JURI::root(true).'/'.str_replace(JPATH_SITE.DS, "", $picture);
				$title = $alttext;
				//height and width set, let the browser resize it
				$bUseHighslide = $params->get('use_highslide', false);
				if($bUseHighslide && $type != 4) {
					$title .= ' (' . JText::_('COM_SPORTSMANAGEMENT_GLOBAL_CLICK_TO_ENLARGE') . ')';
					$ret .= '<a href="'.$picture.'" class="highslide">';
				}
				$ret .= '<img ';
				$ret .= ' ';
				if($height>0 && $width>0) {
					$ret .= ' src="'.$picture;
					$ret .='" width="'.$width.'" height="'.$height.'"
							alt="'.$alttext.'" title="'.$title.'"';
				}
				//height==0 and width set, let the browser resize it
				if($height==0 && $width>0) {
					$ret .= ' src="'.$picture;
					$ret .='" width="'.$width.'" alt="'.$alttext.'" title="'.$title.'"';
				}
				//width==0 and height set, let the browser resize it
				if($height>0 && $width==0) {
					$ret .= ' src="'.$picture;
					$ret .='" height="'.$height.'" alt="'.$alttext.'" title="'.$title.'"';
				}
				//width==0 and height==0, use original picture size
				if($height==0 && $width==0) {
					$ret .= ' src="'.$picture;
					$ret .='" alt="'.$alttext.'" title="'.$title.'"';
				}
				$ret .= '/>';
				if($bUseHighslide) {
					$ret .= '</a>';
				}
			}
				
		}
			
		return $ret;
	}

	/**
	 * static method which extends template path for given view names
	 * Can be used by views to search for extensions that implement parts of common views
	 * and add their path to the template search path.
	 * (e.g. 'projectheading', 'backbutton', 'footer')
	 * @param array(string) $viewnames, names of views for which templates need to be loaded,
	 *                      so that extensions are used when available
	 * @param JLGView       $view to which the template paths should be added
	 */
	public static function addTemplatePaths($templatesToLoad, &$view)
	{
		$extensions = sportsmanagementHelper::getExtensions(JRequest::getInt('p'));
		foreach ($templatesToLoad as $template)
		{
			$view->addTemplatePath(JPATH_COMPONENT . DS . 'views' . DS . $template . DS . 'tmpl');
			if (is_array($extensions) && count($extensions) > 0)
			{
				foreach ($extensions as $e => $extension) 
				{
					$extension_views = JPATH_COMPONENT_SITE . DS . 'extensions' . DS . $extension . DS . 'views';
					$tmpl_path = $extension_views . DS . $template . DS . 'tmpl';
					if (JFolder::exists($tmpl_path))
					{
						$view->addTemplatePath($tmpl_path);
					}
				}
			}
		}
	}

	/**
	 * get unix timestamp of specified date
	 *
	 * @param string $date now if null
	 * @param boolean $use_offset use time offset
	 * @param int $project_serveroffset custom time offset in hours. Use default joomla timeoffset if null
	 */
	function getTimestamp($date = null, $use_offset = 0, $offset = null)
	{
		$date = $date ? $date : 'now';
		$app = Jfactory::getApplication();
		$res = JFactory::getDate(strtotime($date));

		if ($use_offset)
		{
			if ($offset)
			{
				$serveroffset=explode(':', $offset);
				$res->setOffset($serveroffset[0]);
			}
			else
			{
				$res->setOffset($app->getCfg('offset'));
			}
		}
		return $res->toUnix('true');
	}

	/**
	 * Method to convert a date from 0000-00-00 to 00-00-0000 or back
	 * return a date string
	 * $direction == 1 means from convert from 0000-00-00 to 00-00-0000
	 * $direction != 1 means from convert from 00-00-0000 to 0000-00-00
	 * call by sportsmanagementHelper::convertDate($date) inside the script
	 *
	 * When no "-" are given in $date two short date formats (DDMMYYYY and DDMMYY) are supported
	 * for example "31122011" or "311211" for 31 december 2011
	 * 
	 * @access	public
	 * @return	array
	 *
	 */
	static function convertDate($DummyDate,$direction=1)
	{
		if(!strpos($DummyDate,"-")!==false)
		{
			// for example 31122011 is used for 31 december 2011
			if (strlen($DummyDate) == 8 )
			{
				$result  = substr($DummyDate,4,4);
				$result .= '-';
				$result .= substr($DummyDate,2,2);
				$result .= '-';
				$result .= substr($DummyDate,0,2);
			}
			// for example 311211 is used for 31 december 2011
			elseif (strlen($DummyDate) == 6 )
 			{
 				$result  = substr(date("Y"),0,2);
				$result .= substr($DummyDate,4,4);
				$result .= '-';
				$result .= substr($DummyDate,2,2);
				$result .= '-';
				$result .= substr($DummyDate,0,2);
			}
		}
		else
		{

			if ($direction == 1)
			{
				$result  = substr($DummyDate,8);
				$result .= '-';
				$result .= substr($DummyDate,5,2);
				$result .= '-';
				$result .= substr($DummyDate,0,4);
			}
			else
			{
				$result  = substr($DummyDate,6,4);
				$result .= '-';
				$result .= substr($DummyDate,3,2);
				$result .= '-';
				$result .= substr($DummyDate,0,2);
			}
		}

		return $result;
	}

	function showTeamIcons(&$team,&$config)
	{
		if(!isset($team->projectteamid)) return "";
		$projectteamid = $team->projectteamid;
		$teamname      = $team->name;
		$teamid        = $team->team_id;
		$teamSlug      = (isset($team->team_slug) ? $team->team_slug : $teamid);
		$clubSlug      = (isset($team->club_slug) ? $team->club_slug : $team->club_id);
		$division_slug = (isset($team->division_slug) ? $team->division_slug : $team->division_id);
		$projectSlug   = (isset($team->project_slug) ? $team->project_slug : $team->project_id);
		$output        = '';

		if ($config['show_team_link'])
		{
			$link =JoomleagueHelperRoute::getPlayersRoute($projectSlug,$teamSlug);
			$title=JText::_('COM_SPORTSMANAGEMENT_TEAMICONS_ROSTER_LINK').'&nbsp;'.$teamname;
			$picture = 'media/com_sportsmanagement/jl_images/team_icon.png';
			$desc = self::getPictureThumb($picture, $title, 0, 0, 4);
			$output .= JHTML::link($link,$desc);
		}

		if (((!isset($team_plan)) || ($teamid!=$team_plan->id)) && ($config['show_plan_link']))
		{
			$link =JoomleagueHelperRoute::getTeamPlanRoute($projectSlug,$teamSlug,$division_slug);
			$title=JText::_('COM_SPORTSMANAGEMENT_TEAMICONS_TEAMPLAN_LINK').'&nbsp;'.$teamname;
			$picture = 'media/com_sportsmanagement/jl_images/calendar_icon.gif';
			$desc = self::getPictureThumb($picture, $title, 0, 0, 4);
			$output .= JHTML::link($link,$desc);
		}

		if ($config['show_curve_link'])
		{
			$link =JoomleagueHelperRoute::getCurveRoute($projectSlug,$teamSlug,0,$division_slug);
			$title=JText::_('COM_SPORTSMANAGEMENT_TEAMICONS_CURVE_LINK').'&nbsp;'.$teamname;
			$picture = 'media/com_sportsmanagement/jl_images/curve_icon.gif';
			$desc = self::getPictureThumb($picture, $title, 0, 0, 4);
			$output .= JHTML::link($link,$desc);
		}

		if ($config['show_teaminfo_link'])
		{
// 			$link =JoomleagueHelperRoute::getProjectTeamInfoRoute($projectSlug,$projectteamid);
			$link =JoomleagueHelperRoute::getTeamInfoRoute($projectSlug,$teamSlug);
      $title=JText::_('COM_SPORTSMANAGEMENT_TEAMICONS_TEAMINFO_LINK').'&nbsp;'.$teamname;
			$picture = 'media/com_sportsmanagement/jl_images/teaminfo_icon.png';
			$desc = self::getPictureThumb($picture, $title, 0, 0, 4);
			$output .= JHTML::link($link,$desc);
		}

		if ($config['show_club_link'])
		{
			$link =JoomleagueHelperRoute::getClubInfoRoute($projectSlug,$clubSlug);
			$title=JText::_('COM_SPORTSMANAGEMENT_TEAMICONS_CLUBINFO_LINK').'&nbsp;'.$teamname;
			$picture = 'media/com_sportsmanagement/jl_images/mail.gif';
			$desc = self::getPictureThumb($picture, $title, 0, 0, 4);
			$output .= JHTML::link($link,$desc);
		}

		if ($config['show_teamstats_link'])
		{
			$link =JoomleagueHelperRoute::getTeamStatsRoute($projectSlug,$teamSlug);
			$title=JText::_('COM_SPORTSMANAGEMENT_TEAMICONS_TEAMSTATS_LINK').'&nbsp;'.$teamname;
			$picture = 'media/com_sportsmanagement/jl_images/teamstats_icon.png';
			$desc = self::getPictureThumb($picture, $title, 0, 0, 4);
			$output .= JHTML::link($link,$desc);
		}

		if ($config['show_clubplan_link'])
		{
			$link =JoomleagueHelperRoute::getClubPlanRoute($projectSlug,$clubSlug);
			$title=JText::_('COM_SPORTSMANAGEMENT_TEAMICONS_CLUBPLAN_LINK').'&nbsp;'.$teamname;
			$picture = 'media/com_sportsmanagement/jl_images/clubplan_icon.png';
			$desc = self::getPictureThumb($picture, $title, 0, 0, 4);
			$output .= JHTML::link($link,$desc);
		}

		return $output;
	}

	function formatTeamName($team,$containerprefix,&$config,$isfav=0,$link=null)
	{
		$output			= '';
		$desc			= '';

		if ((isset($config['results_below'])) && ($config['results_below']) && ($config['show_logo_small']))
		{
			$js_func		= 'visibleMenu';
			$style_append	= 'visibility:hidden';
			$container		= 'span';
		}
		else
		{
			$js_func		= 'switchMenu';
			$style_append	= 'display:none';
			$container		= 'div';
		}
		
		$showIcons=	(
						($config['show_info_link']==2) && ($isfav)
					) ||
					(
						($config['show_info_link']==1) &&
						(
							$config['show_club_link'] ||
							$config['show_team_link'] ||
							$config['show_curve_link'] ||
							$config['show_plan_link'] ||
							$config['show_teaminfo_link'] ||
							$config['show_teamstats_link'] ||
							$config['show_clubplan_link']
						)
					);
		$containerId = $containerprefix.'t'.$team->id.'p'.$team->project_id;
		if ($showIcons)
		{
			$onclick	= $js_func.'(\''.$containerId.'\');return false;';
			$params		= array('onclick' => $onclick);
		}

		$style = 'padding:2px;';
		if ($config['highlight_fav'] && $isfav)
		{
			$favs = self::getProjectFavTeams($team->project_id);
			$style .= ($favs->fav_team_text_bold != '') ? 'font-weight:bold;' : '';
			$style .= (trim($favs->fav_team_text_color) != '') ? 'color:'.trim($favs->fav_team_text_color).';' : '';
			$style .= (trim($favs->fav_team_color) != '') ? 'background-color:'.trim($favs->fav_team_color).';' : '';
		}

		$desc .= '<span style="'.$style.'">';
		
		$formattedTeamName = "";
		if ($config['team_name_format']== 0)
		{
			$formattedTeamName = $team->short_name;
		}
		else if ($config['team_name_format']== 1)
		{
			$formattedTeamName = $team->middle_name;
		}
		if (empty($formattedTeamName))
		{
			$formattedTeamName = $team->name;
		}

		if (($config['team_name_format']== 0) && (!empty($team->short_name))) 
		{
			$desc .=  '<acronym title="'.$team->name.'">'.$team->short_name.'</acronym>';
		}
		else
		{
			$desc .= $formattedTeamName;
		}

		$desc .=  '</span>';

		if ($showIcons)
		{
			$output .= JHTML::link('javascript:void(0);',$desc,$params);
			$output .= '<'.$container.' id="'.$containerId.'" style="'.$style_append.';">';
			$output .= self::showTeamIcons ($team,$config);
			$output .= '</'.$container.'>';
		}
		else
		{
			$output = $desc;
		}
		
		if ($link != null)
		{
			$output = JHTML::link($link, $output);
		}

		return $output;
	}

	function showClubIcon(&$team,$type=1,$with_space=0)
	{
		if (($type==1) && (isset($team->country)))
		{
			if ($team->logo_small!='')
			{
				echo JHTML::image($team->logo_small,'');
				if ($with_space==1){
					echo ' style="padding:1px;"';
				}
			}
			else
			{
				echo '&nbsp;';
			}
		}
		elseif (($type==2) && (isset($team->country)))
		{
			echo Countries::getCountryFlag($team->country);
		}
	}

	function showColorsLegend($colors)
	{
		$favshow=JRequest::getVar('func','');
		if (($favshow!='showCurve') && ($this->project->fav_team))
		{
			$fav=array('color'=>$this->project->fav_team_color,'description'=> JText::_('COM_SPORTSMANAGEMENT_RANKING_FAVTEAM'));
			array_push($colors,$fav);
		}
		foreach($colors as $color)
		{
			if (trim($color['description'])!='')
			{
				echo '<td align="center" style="background-color:'.$color['color'].';"><b>'.$color['description'].'</b>&nbsp;</td>';
			}

		}
	}


	/**
	 * Removes invalid XML
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	public function stripInvalidXml($value)
	{
		$ret='';
		$current='';
		if (is_null($value)){
			return $ret;
		}

		$length=strlen($value);
		for ($i=0; $i < $length; $i++)
		{
			$current=ord($value{$i});
			if (($current == 0x9) ||
			($current == 0xA) ||
			($current == 0xD) ||
			(($current >= 0x20) && ($current <= 0xD7FF)) ||
			(($current >= 0xE000) && ($current <= 0xFFFD)) ||
			(($current >= 0x10000) && ($current <= 0x10FFFF)))
			{
				$ret .= chr($current);
			}
			else
			{
				$ret .= ' ';
			}
		}
		return $ret;
	}

	public static function getVersion()
	{
		$database = JFactory::getDBO();

		$query="SELECT CONCAT(major,'.',minor,'.',build,'.',revision) AS version
				  FROM #__".COM_SPORTSMANAGEMENT_TABLE."_version 
				  ORDER BY date DESC LIMIT 1";
		$database->setQuery($query);
		$result=$database->loadResult();
		return $result;
	}

	/**
	 * returns formatName
	 *
	 * @param prefix
	 * @param firstName
	 * @param nickName
	 * @param lastName
	 * @param format
	 */
	function formatName($prefix, $firstName, $nickName, $lastName, $format)
	{
		$name = array();
		if ($prefix)
		{
			$name[] = $prefix;
		}
		switch ($format)
		{
			case 0: //Firstname 'Nickname' Lastname
				if ($firstName != "") {
					$name[] = $firstName;
				}
				if ($nickName != "") {
					$name[] = "'" . $nickName . "'";
				}
				if ($lastName != "") {
					$name[] = $lastName;
				}
				break;
			case 1: //Lastname, 'Nickname' Firstname
				if ($lastName != "") {
					$name[] = $lastName . ",";
				}
				if ($nickName != "") {
					$name[] = "'" . $nickName . "'";
				}
				if ($firstName != "") {
					$name[] = $firstName;
				}
				break;
			case 2: //Lastname, Firstname 'Nickname'
				if ($lastName != "") {
					$name[] = $lastName . ",";
				}
				if ($firstName != "") {
					$name[] = $firstName;
				}
				if ($nickName != "") {
					$name[] = "'" . $nickName . "'";
				}
				break;
			case 3: //Firstname Lastname
				if ($firstName != "") {
					$name[] = $firstName;
				}
				if ($lastName != "") {
					$name[] = $lastName;
				}
				break;
			case 4: //Lastname, Firstname
				if ($lastName != "") {
					$name[] = $lastName . ",";
				}
				if ($firstName != "") {
					$name[] = $firstName;
				}
				break;
			case 5: //'Nickname' - Firstname Lastname
				if ($nickName != "") {
					$name[] = "'" . $nickName . "' - ";
				}
				if ($firstName != "") {
					$name[] = $firstName;
				}
				if ($lastName != "") {
					$name[] = $lastName;
				}
				break;
			case 6: //'Nickname' - Lastname, Firstname
				if ($nickName != "") {
					$name[] = "'" . $nickName . "' - ";
				}
				if ($lastName != "") {
					$name[] = $lastName . ",";
				}
				if ($firstName != "") {
					$name[] = $firstName;
				}
				break;
			case 7: //Firstname Lastname (Nickname)
				if ($firstName != "") {
					$name[] = $firstName;
				}
				if ($lastName != "") {
					$name[] = $lastName ;
				}
				if ($nickName != "") {
					$name[] = "(" . $nickName . ")";
				}
				break;
			case 8: //F. Lastname
				if ($firstName != "") {
					$name[] = $firstName[0] . ".";
				}
				if ($lastName != "") {
					$name[] = $lastName;
				}
				break;
			case 9: //Lastname, F.
				if ($lastName != "") {
					$name[] = $lastName.",";
				}
				if ($firstName != "") {
					$name[] = $firstName[0] . ".";
				}
				break;
			case 10: //Lastname
				if ($lastName != "") {
					$name[] = $lastName;
				}
				break;
			case 11: //Firstname 'Nickname' L.
				if ($firstName != "") {
					$name[] = $firstName;
				}
				if ($nickName != "") {
					$name[] = "'" . $nickName . "'";
				}
				if ($lastName != "") {
					$name[] = $lastName[0]. ".";
				}
				break;
			case 12: //Nickname
				if ($nickName != "") {
					$name[] = $nickName;
				}
				break;
			case 13: //Firstname L.
				if ($firstName != "") {
					$name[] = $firstName;
				}
				if ($lastName != "") {
					$name[] = $lastName[0]. ".";
				}
				break;
			case 14: //Lastname Firstname
				if ($lastName != "") {
					$name[] = $lastName;
				}
				if ($firstName != "") {
					$name[] = $firstName;
				}
				break;
			case 15: //Lastname newline Firstname
				if ($lastName != "") {
					$name[] = $lastName;
					$name[] = '<br \>';
				}
				if ($firstName != "") {
					$name[] = $firstName;
				}
				break;
			case 16: //Firstname newline Lastname
				if ($lastName != "") {
					$name[] = $lastName;
					$name[] = '<br \>';
				}
				if ($firstName != "") {
					$name[] = $firstName;
				}
				break;
		}

		return implode(" ", $name);
	}

	/**
	 * Creates the print button
	 *
	 * @param string $print_link
	 * @param array $config
	 * @since 1.5.2
	 */
	public static function printbutton($print_link, &$config)
	{
		if ($config['show_print_button'] == 1) {
			JHTML::_('behavior.tooltip');
			$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=800,height=600,directories=no,location=no';
			// checks template image directory for image, if non found default are loaded
			if ($config['show_icons'] == 1 ) {
				$image = JHTML::_('image.site', 'printButton.png', 'media/com_sportsmanagement/jl_images/', NULL, NULL, JText::_( 'Print' ));
			} else {
				$image = JText::_( 'Print' );
			}
			if (JRequest::getInt('pop')) {
				//button in popup
				$output = '<a href="javascript: void(0)" onclick="window.print();return false;">'.$image.'</a>';
			} else {
				//button in view
				$overlib = JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_PRINT_TIP' );
				$text = JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_PRINT' );
				$sef = JFactory::getConfig()->getValue('config.sef', false);
				$print_urlparams = ($sef ? "?tmpl=component&print=1" : "&tmpl=component&print=1");

				if(is_null($print_link)) {
				    $output	= '<a href="javascript: void(0)" class="editlinktip hasTip" onclick="window.open(window.location.href + \''.$print_urlparams.'\',\'win2\',\''.$status.'\'); return false;" rel="nofollow" title="'.$text.'::'.$overlib.'">'.$image.'</a>';
				} else {
				    $output	= '<a href="'. JRoute::_($print_link) .'" class="editlinktip hasTip" onclick="window.open(window.location.href + \''.$print_urlparams.'\',\'win2\',\''.$status.'\'); return false;" rel="nofollow" title="'.$text.'::'.$overlib.'">'.$image.'</a>';
				}
			}
			return $output;
		}
		return;
	}

	/**
	 * return true if mootools upgrade is enabled
	 *
	 * @return boolean
	 */
	function isMootools12()
	{
		$version = new JVersion();
		if ($version->RELEASE == '1.5' && $version->DEV_LEVEL >= 19 && JPluginHelper::isEnabled( 'system', 'mtupgrade' ) ) {
			return true;
		}
		else {
			return false;
		}
	}
    
    
    function ToolbarButton($layout = Null,$icon_image = 'upload',$alt_text = 'My Label',$view = '',$type=0)
	{
	$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
    
    //$mainframe->enqueueMessage(JText::_('ToolbarButton layout<br><pre>'.print_r(JRequest::getVar('layout'),true).'</pre>'),'Notice');
    //$mainframe->enqueueMessage(JText::_('ToolbarButton get<br><pre>'.print_r($_GET,true).'</pre>'),'Notice');
    
    if ( !$view )
    {
    $view = JRequest::getVar( "view") ;
    }
    $modal_popup_width = JComponentHelper::getParams($option)->get('modal_popup_width',0) ;
    $modal_popup_height = JComponentHelper::getParams($option)->get('modal_popup_height',0) ;
    $bar = JToolBar::getInstance('toolbar');
    $page_url = JFilterOutput::ampReplace('index.php?option=com_sportsmanagement&view='.$view.'&tmpl=component&layout='.$layout.'&type='.$type );
    
    $bar->appendButton('Popup', $icon_image, $alt_text, $page_url, $modal_popup_width, $modal_popup_height);
    
    
    
    }
    
    
    static function ToolbarButtonOnlineHelp()
	{
	$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
    $view = JRequest::getVar( "view") ;
    $view = ucfirst(strtolower($view));
    
    $window_width = '<script>alert($(window).width()); </script>';
    $window_height = '<script>alert(window.screen.height); </script>';
    
    //$mainframe->enqueueMessage(JText::_('ToolbarButtonOnlineHelp width<br><pre>'.print_r($window_width,true).'</pre>'),'Notice');
    //$mainframe->enqueueMessage(JText::_('ToolbarButtonOnlineHelp width<br><pre>'.print_r($_SESSION,true).'</pre>'),'Notice');
    
    switch ($view)
    {
    case 'Template':
    case 'Predictiontemplate':
    $template_help = $mainframe->getUserState($option.'template_help');
    $view = $view.'_'.$template_help;
    break;
    default:
    break;
    }
    $cfg_help_server = JComponentHelper::getParams($option)->get('cfg_help_server','') ;
    $modal_popup_width = JComponentHelper::getParams($option)->get('modal_popup_width',0) ;
    $modal_popup_height = JComponentHelper::getParams($option)->get('modal_popup_height',0) ;
    $bar = JToolBar::getInstance('toolbar');
    
    $send = '<a class="modal" rel="{handler: \'iframe\', size: {x: '.$modal_popup_width.', y: '.$modal_popup_height.'}}" '.
         ' href="'.$cfg_help_server.'SM-Backend:'.$view.'"><span title="send" class="icon-32-help"></span>'.JText::_('Onlinehilfe').'</a>';
	
    /*
    $send = '<a class="modal" rel="{handler: \'iframe\', size: {x: '.'<script>width; </script>'.', y: '.$modal_popup_height.'}}" '.
         ' href="'.$cfg_help_server.'SM-Backend:'.$view.'"><span title="send" class="icon-32-help"></span>'.JText::_('Onlinehilfe').'</a>';	
    */
        
        // Add a help button.
		$bar->appendButton('Custom',$send);	
		//$bar->appendButton('Help', $ref, $com, $override, $component);
	}
    
		
	/**
	* return project rounds as array of objects(roundid as value, name as text)
	*
	* @param string $ordering
	* @return array
	*/
	function getRoundsOptions($project_id, $ordering='ASC', $required = false)
	{
		$mainframe = JFactory::getApplication();
        $db = JFactory::getDBO();
		$query = ' SELECT id as value '
		       . '      , CASE LENGTH(name) when 0 then CONCAT('.$db->Quote(JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME')). ', " ", id)	else name END as text '
		       . '      , id, name, round_date_first, round_date_last, roundcode '
		       . ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_round '
		       . ' WHERE project_id= ' .$project_id
           . ' AND published =  1'
		       . ' ORDER BY roundcode '.$ordering;
	
		$db->setQuery($query);
		if(!$required) {
			$mitems = array(JHTML::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));
			return array_merge($mitems, $db->loadObjectList());
		} else {
			return $db->loadObjectList();
		}
	}
	
	/**
	 * returns -1/0/1 if the team lost/drew/won in specified game, or false if not played/cancelled
	 *  
	 * @param object $game date from match table
	 * @param int $ptid project team id
	 * @return false|int
	 */
	function getTeamMatchResult($game, $ptid)
	{
		if (!isset($game->team1_result)) {
			return false;
		}
		if ($game->cancel) {
			return false;
		}
		
		if (!$game->alt_decision)
		{
			$result1 = $game->team1_result;
			$result2 = $game->team2_result;
		}
		else
		{
			$result1 = $game->team1_result_decision;
			$result2 = $game->team2_result_decision;
		}
		if ($result1 == $result2) {
			return 0;
		}
		
		if ($ptid == $game->projectteam1_id) {
			return ($result1 > $result2) ? 1 : -1;
		}
		else {
			return ($result1 > $result2) ? -1 : 1;
		}
	}
    
    /**
	 * Method to check extra fields
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
    static function checkUserExtraFields()
    {
         $mainframe	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
        $db = JFactory::getDBO();
    $query="SELECT id FROM #__".COM_SPORTSMANAGEMENT_TABLE."_user_extra_fields WHERE template_backend LIKE '".JRequest::getVar('view')."' ";
			//echo '<pre>'.print_r($query,true).'</pre>';
			$db->setQuery($query);
			if ($db->loadResult())
			{
				return true;
			}
            else
            {
                return false;
            }    
        
    }
    
    /**
	 * Method to get extra fields
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
    static function getUserExtraFields($jlid)
    {
    	$db = JFactory::getDBO();
        $query = "SELECT ef.*,
        ev.fieldvalue as fvalue,
        ev.id as value_id 
        FROM #__".COM_SPORTSMANAGEMENT_TABLE."_user_extra_fields as ef 
        LEFT JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_user_extra_fields_values as ev 
        ON ef.id = ev.field_id 
        AND ev.jl_id = ".$jlid." 
        WHERE ef.template_backend LIKE '".JRequest::getVar('view')."'  
        ORDER BY ef.ordering";    
        $db->setQuery($query);
		if (!$result=$db->loadObjectList())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		return $result;
    
    }
    
    
    function saveExtraFields($post,$pid)
  {
    $mainframe = JFactory::getApplication();
       $address_parts = array();
    //$mainframe->enqueueMessage(JText::_('sportsmanagementHelper saveExtraFields<br><pre>'.print_r($post,true).'</pre>'),'Notice');
    
    $db = JFactory::getDBO();
  //-------extra fields-----------//
		if(isset($post['extraf']) && count($post['extraf']))
    {
        //$mainframe->enqueueMessage(JText::_('sportsmanagementHelper saveExtraFields<br><pre>'.print_r($post,true).'</pre>'),'Notice');
        //$mainframe->enqueueMessage(JText::_('sportsmanagementHelper saveExtraFields pid<br><pre>'.print_r($pid,true).'</pre>'),'Notice');
			for($p=0;$p<count($post['extraf']);$p++)
            {
                // Create a new query object.
                $query = $db->getQuery(true);
// delete all
$conditions = array(
    $db->quoteName('field_id') . '='.$post['extra_id'][$p],
    $db->quoteName('jl_id') . '='.$pid
);
 
$query->delete($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_user_extra_fields_values'));
$query->where($conditions);
 
$db->setQuery($query);  

if (!$db->query())
		{
			
            $mainframe->enqueueMessage(JText::_('sportsmanagementHelper saveExtraFields delete<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
		}
        
// Create a new query object.
        $query = $db->getQuery(true);
        // Insert columns.
        $columns = array('field_id','jl_id','fieldvalue');
        // Insert values.
        $values = array($post['extra_id'][$p],$pid,'\''.$post['extraf'][$p].'\'');
        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_user_extra_fields_values'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        // Set the query using our newly populated query object and execute it.
        $db->setQuery($query);

if (!$db->query())
		{
			
            $mainframe->enqueueMessage(JText::_('sportsmanagementHelper saveExtraFields insert<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
		}
        
/*
				$query = "DELETE FROM #__".COM_SPORTSMANAGEMENT_TABLE."_user_extra_values WHERE f_id = ".$post['extra_id'][$p]." AND uid = ".$pid;
				$db->setQuery($query);
				$db->query();
                */
                /*
				$query = "INSERT INTO #__".COM_SPORTSMANAGEMENT_TABLE."_user_extra_values(f_id,uid,fvalue) VALUES(".$post['extra_id'][$p].",".$pid.",'".$post['extraf'][$p]."')";
				$db->setQuery($query);
				$db->query();
                */
			}
		}
  
  }
    
    /**
	* Fetch google map data refere to
	* http://code.google.com/apis/maps/documentation/geocoding/#Geocoding	 
	*/	 	
	public function getAddressData($address)
	{

		$url = 'http://maps.google.com/maps/api/geocode/json?' . 'address='.urlencode($address) .'&sensor=false&language=de';
		$content = self::getContent($url);
		
		$status = null;	
		if(!empty($content))
		{
			$json = new Services_JSON();
			$status = $json->decode($content);
		}

		return $status;
	}
    
    public function resolveLocation($address)
	{
		$mainframe = JFactory::getApplication();
    $coords = array();
		$data = self::getAddressData($address);
		//$mainframe->enqueueMessage(JText::_('google -> '.'<pre>'.print_r($data,true).'</pre>' ),'');
		if($data){
			if($data->status == 'OK')
			{
				$this->latitude  = $data->results[0]->geometry->location->lat;
				$coords['latitude'] = $data->results[0]->geometry->location->lat; 
				$this->longitude = $data->results[0]->geometry->location->lng;
				$coords['longitude'] = $data->results[0]->geometry->location->lng;
				
				for ($a=0; $a < sizeof($data->results[0]->address_components); $a++ )
				{
        switch($data->results[0]->address_components[$a]->types[0])
        {
        case 'administrative_area_level_1':
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_SHORT_NAME'] = $data->results[0]->address_components[$a]->short_name;
        break;
        
        case 'administrative_area_level_2':
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_SHORT_NAME'] = $data->results[0]->address_components[$a]->short_name;
        break;
        
        case 'administrative_area_level_3':
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_SHORT_NAME'] = $data->results[0]->address_components[$a]->short_name;
        break;

        case 'locality':
        $coords['COM_SPORTSMANAGEMENT_LOCALITY_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
        break;
        
        case 'sublocality':
        $coords['COM_SPORTSMANAGEMENT_SUBLOCALITY_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
        break;
                        
        }
                
        
        }
				
				
				return $coords;
			}
		}
	}
    
    // Return content of the given url
	static public function getContent($url , $raw = false , $headerOnly = false)
	{
		if (!$url)
			return false;
		
		if (function_exists('curl_init'))
		{
			$ch			= curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, true );
			
			if($raw){
				curl_setopt($ch, CURLOPT_BINARYTRANSFER, true );
			}

			$response	= curl_exec($ch);
			
			$curl_errno	= curl_errno($ch);
			$curl_error	= curl_error($ch);
			
			if ($curl_errno!=0)
			{
				$mainframe	= JFactory::getApplication();
				$err		= 'CURL error : '.$curl_errno.' '.$curl_error;
				$mainframe->enqueueMessage($err, 'error');
			}
			
			$code		= curl_getinfo( $ch , CURLINFO_HTTP_CODE );

			// For redirects, we need to handle this properly instead of using CURLOPT_FOLLOWLOCATION
			// as it doesn't work with safe_mode or openbase_dir set.
			if( $code == 301 || $code == 302 )
			{
				list( $headers , $body ) = explode( "\r\n\r\n" , $response , 2 );
				
				preg_match( "/(Location:|URI:)(.*?)\n/" , $headers , $matches );
				
				if( !empty( $matches ) && isset( $matches[2] ) )
				{
					$url	= JString::trim( $matches[2] );
					curl_setopt( $ch , CURLOPT_URL , $url );
					curl_setopt( $ch , CURLOPT_RETURNTRANSFER, 1);
					curl_setopt( $ch , CURLOPT_HEADER, true );
					$response	= curl_exec( $ch );
				}
			}
			
			
			if(!$raw){
				list( $headers , $body )	= explode( "\r\n\r\n" , $response , 2 );
			}
			
			$ret	= $raw ? $response : $body;
			$ret	= $headerOnly ? $headers : $ret;
			
			curl_close($ch);
			return $ret;
		}
	
		// CURL unavailable on this install
		return false;
	}
    
    static function getPictureClub($id)
    {
        $db = JFactory::getDBO();
    // Create a new query object.
        $query = $db->getQuery(true);
        $query->select(array('logo_big'))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_club')
        ->where('id = '.$id);    
        $db->setQuery($query);
		return $db->loadResult();
    }
    
    static function getPicturePlayground($id)
    {
        $db = JFactory::getDBO();
    // Create a new query object.
        $query = $db->getQuery(true);
        $query->select(array('picture'))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_playground')
        ->where('id = '.$id);    
        $db->setQuery($query);
		return $db->loadResult();    
        
    }
        
}
