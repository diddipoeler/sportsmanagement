<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      match.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage match
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\FormController; 
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Router\Route;

/**
 * sportsmanagementControllermatch
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllermatch extends FormController
{

/**
	 * Class Constructor
	 *
	 * @param	array	$config		An optional associative array of configuration settings.
	 * @return	void
	 * @since	1.5
	 */
	function __construct($config = array())
	{
		parent::__construct($config);

		// Map the apply task to the save method.
		$this->registerTask('apply', 'save');
		$this->jsmuser = Factory::getUser(); 
        $this->jsmdate = Factory::getDate();
	}
    
    
    /**
     * sportsmanagementControllermatch::cancelmodal()
     * 
     * @param mixed $key
     * @return void
     */
    function cancelmodal($key = NULL)
	{
	$msg = '';
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
	}
    
    
    /**
     * sportsmanagementControllermatch::copyfrom()
     * 
     * @return void
     */
    public function copyfrom()
	{
		$app = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
        $db = Factory::getDbo();
		$msg = '';
		$post = Factory::getApplication()->input->post->getArray(array());
		$model = $this->getModel('match');
		$add_match_count = $post['add_match_count'];
		$round_id = Factory::getApplication()->input->getInt('rid');
		$post['project_id'] = $app->getUserState($option.'.pid',0);
		$post['round_id'] = $round_id;
        $mdlProject = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
	    $projectws = $mdlProject->getProject($post['project_id']);
        
        
		//$project_tz = new DateTimeZone($projectws->timezone);
		//$timezone = $projectws->timezone;
        //if ( empty($timezone) )
        //{
//            $timezone = 'Europe/Berlin';
//        }
        
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');
        
		// Add matches (type=1)
		if ( $post['addtype'] == 1 )
		{
			if ($add_match_count > 0) // Only MassAdd a number of new and empty matches
			{
				if (!empty($post['autoPublish'])) // 1=YES Publish new matches
				{
					$post['published'] = 1;
				}

				$matchNumber = Factory::getApplication()->input->getInt('firstMatchNumber',1);
				$roundFound = false;
				
				if ($projectRounds = $model->getProjectRoundCodes($post['project_id']))
				{
					// convert date and time to utc
					//$uiDate = $post['match_date'];
					//$uiTime = $post['startTime'];
					//$post['match_date'] = $this->convertUiDateTimeToMatchDate($uiDate, $uiTime, $timezone);
                    $post['match_date'] = sportsmanagementHelper::convertDate($post['match_date'],0).' '.$post['startTime'];
                    //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' match_date<br><pre>'.print_r($post['match_date'],true).'</pre>'),'');
			
					foreach ($projectRounds AS $projectRound)
					{
						if ( $projectRound->id == $post['round_id'] )
                        {
							$roundFound=true;
						}
						if ($roundFound)
						{
							$post['round_id'] = $projectRound->id;
							$post['roundcode'] = $projectRound->roundcode;
							for ($x=1; $x <= $add_match_count; $x++)
							{
								if (!empty($post['firstMatchNumber'])) // 1=YES Add continuous match Number to new matches
								{
									$post['match_number'] = $matchNumber;
								}
                                
                                //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' x -> '.$x.'' ),'Error');
                                //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' add_match_count -> '.$add_match_count.'' ),'Error');

								//if ($model->store($post))
                                $model = $this->getModel('match');
                                if ($model->save($post))
								{
									//$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_MATCH');
                                    //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' getErrorMsg<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
									$matchNumber++;
								}
								else
								{
									$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_ADD_MATCH');
                                    //$msg = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_FROM_FILE','<b>'.print_r($db->getErrorMsg(),true).'</b>');
                                    //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' getErrorMsg<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
									break;
								}
							}
							if (empty($post['addToRound'])) // 1=YES Add matches to all rounds
							{
								break;
							}
						}
					}
				}
			}
		}
		// Copy matches (type=2)
		if ( $post['addtype'] == 2 )// Copy or mirror new matches from a selected existing round
		{
			if ( $matches = $model->getRoundMatches($round_id))
			{
				// convert date and time to utc
				//$uiDate = $post['date'];
				//$uiTime = $post['startTime'];
				//$post['match_date'] = $this->convertUiDateTimeToMatchDate($uiDate, $uiTime, $timezone);
                $post['match_date'] = sportsmanagementHelper::convertDate($post['date'],0).' '.$post['startTime'];

if ( $post['create_new'] )
{
$round = new stdClass();
$round->project_id = $post['project_id'];
$round->roundcode = '';
$round->name = $post['start_round_name'];
$round->modified = $this->jsmdate->toSql();
$round->modified_by = $this->jsmuser->get('id');	
/**
 * Insert the object into the table.
 */
try{
$resultinsert = $db->insertObject('#__sportsmanagement_round', $round);
$post['round_id'] = $db->insertid();
}
catch (Exception $e)
{
$this->setError('COM_SPORTSMANAGEMENT_ADMIN_ROUND_FAILED');	
return false;
}		

}
				
				foreach($matches as $match)
				{
					//aufpassen,was uebernommen werden soll und welche daten durch die aus der post ueberschrieben werden muessen
					//manche daten muessen auf null gesetzt werden

 					$dmatch['match_date'] = $post['match_date'];
					
					if ($post['mirror'] == '1')
					{
						$dmatch['projectteam1_id'] = $match->projectteam2_id;
						$dmatch['projectteam2_id'] = $match->projectteam1_id;
					}
					else
					{
						$dmatch['projectteam1_id'] = $match->projectteam1_id;
						$dmatch['projectteam2_id'] = $match->projectteam2_id;
					}
					$dmatch['project_id'] = $post['project_id'];
					$dmatch['round_id']	= $post['round_id'];
					if ($post['start_match_number'] != '')
					{
						$dmatch['match_number'] = $post['start_match_number'];
						$post['start_match_number']++;
					}

					//if ($model->store($dmatch))
                    $model = $this->getModel('match');
                    if ($model->save($dmatch))
					{
						$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_COPY_MATCH');
					}
					else
					{
						$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_COPY_MATCH');
					}
				}
			}
			else
			{
				$msg=Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_COPY_MATCH2');
			}
		}
		//echo $msg;
		$link = 'index.php?option=com_sportsmanagement&view=matches';
		//$link .= '&hidemainmenu='.Factory::getApplication()->input->getVar('hidemainmenu',0);
		$this->setRedirect($link,$msg);
	}
    
	/**
	 * sportsmanagementControllermatch::insertgooglecalendar()
	 * 
	 * @return void
	 */
	function insertgooglecalendar()
    {
        $option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
        $model = $this->getModel('match');
        $result = $model->insertgooglecalendar();
        
        if ( $result )
        {
            $msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_GOOGLE_EVENT');
        }
        else
        {
            $msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_NO_GOOGLECALENDAR_ID');
        }
        
        $link = 'index.php?option=com_sportsmanagement&view=matches';
		$this->setRedirect($link,$msg);
    }
    
    
    /**
     * sportsmanagementControllermatch::cancelmassadd()
     * 
     * @return void
     */
    function cancelmassadd()
    {
    $link = 'index.php?option=com_sportsmanagement&view=matches&massadd=0';
	$this->setRedirect($link,$msg);       
    }
    
    /**
     * sportsmanagementControllermatch::massadd()
     * 
     * @return void
     */
    function massadd()
	{
	$link = 'index.php?option=com_sportsmanagement&view=matches&layout=massadd&massadd=1';
	$this->setRedirect($link,$msg);   
    }
       
    /**
	 * Method add a match to round
	 *
	 * @access	public
	 * @return	
	 * @since	0.1
	 */
    function addmatch()
	{
	$option = Factory::getApplication()->input->getCmd('option');
	$app = Factory::getApplication();
	$post = Factory::getApplication()->input->post->getArray(array());
	$post['project_id'] = $app->getUserState( "$option.pid", '0' );
	$post['round_id'] = $app->getUserState( "$option.rid", '0' );
        $post['count_result'] = 1;
        $post['published'] = 1;
        $post['summary'] = '-';
        $post['preview'] = '-';
	$model = $this->getModel('match');
        
        // store to the database
        if ($model->save($post))
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_MATCH');
		}
		else
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_ADD_MATCH').$model->getError();
		}
		$link = 'index.php?option=com_sportsmanagement&view=matches';
		$this->setRedirect($link,$msg);
	}
    
    
    /**
     * sportsmanagementControllermatch::save()
     * 
     * @return void
     */
    function save($key = NULL, $urlVar = NULL)
	{
	// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app = Factory::getApplication();
        $db = sportsmanagementHelper::getDBConnection();
        $id	= Factory::getApplication()->input->getInt('id');
//        $tmpl = Factory::getApplication()->input->getVar('tmpl');
		$model = $this->getModel('match');
        $data = Factory::getApplication()->input->getVar('jform', array(), 'post', 'array');
//        $createTeam = Factory::getApplication()->input->getVar('createTeam');
        $return = $model->save($data);   
       
       // Set the redirect based on the task.
		switch ($this->getTask())
		{
			case 'apply':
			$message = Text::_('JLIB_APPLICATION_SAVE_SUCCESS');
			$this->setRedirect('index.php?option=com_sportsmanagement&view=match&layout=edit&tmpl=component&id='.$id, $message);
			break;

			case 'save':
			default:
			$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
			break;
		}
        
       
    }   
    
    
    /**
	 * Method to remove a matchday
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function remove()
	{
	$app = Factory::getApplication();
    $pks = Factory::getApplication()->input->getVar('cid', array(), 'post', 'array');
    $model = $this->getModel('match');
    $model->delete($pks);
	
    $this->setRedirect('index.php?option=com_sportsmanagement&view=matches');    
        
   }
   
   
	/**
	 * sportsmanagementControllermatch::picture()
	 * 
	 * @return void
	 */
	function picture()
  {
  //$cid = Factory::getApplication()->input->getVar('cid',array(0),'','array');
	$match_id = Factory::getApplication()->input->getInt('id',0);
  $dest = JPATH_ROOT.'/images/com_sportsmanagement/database/matchreport/'.$match_id;
  $folder = 'matchreport/'.$match_id;
  //$this->setState('folder', $folder);
  if(Folder::exists($dest)) {
  }
  else
  {
  Folder::create($dest);
  }

  $msg=Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_MATCHPICTURE');
  $link='index.php?option=com_media&view=images&tmpl=component&asset=com_sportsmanagement&author=&folder=com_sportsmanagement/database/'.$folder;
	$this->setRedirect($link,$msg);
  
  }
  
//  /**
//   * sportsmanagementControllermatch::readpressebericht()
//   * 
//   * @return void
//   */
//  function readpressebericht()
//    {
//    Factory::getApplication()->input->setVar('hidemainmenu',1);
//		Factory::getApplication()->input->setVar('layout','readpressebericht');
//		Factory::getApplication()->input->setVar('view','match');
//		Factory::getApplication()->input->setVar('edit',true);
//		parent::display();    
//    }
    
    /**
     * sportsmanagementControllermatch::savepressebericht()
     * 
     * @return
     */
    function savepressebericht()
    {
    	// Check for request forgeries
		Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));
		$msg='';
		ToolbarHelper::back(Text::_('JPREV'),Route::_('index.php?option=com_sportsmanagement&task=jlxmlimport.display'));
		$app = Factory::getApplication();
		$post = Factory::getApplication()->input->post->getArray(array());
        $model = $this->getModel('match');

		// first step - upload
		if (isset($post['sent']) && $post['sent']==1)
		{
			//$upload = Factory::getApplication()->input->getVar('import_package',null,'files','array');
            $upload = $app->input->files->get('import_package');
            //$cid = Factory::getApplication()->input->getVar('cid',array(0),'','array');
            $match_id = Factory::getApplication()->input->getInt('id',0);
			$tempFilePath = $upload['tmp_name'];
			$app->setUserState('com_sportsmanagement'.'uploadArray',$upload);
			$filename = '';
			$msg = '';
			$dest = JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$upload['name'];
			$extractdir = JPATH_SITE.DIRECTORY_SEPARATOR.'tmp';
			//$importFile = JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'. DS.'pressebericht.jlg';
if(!Folder::exists(JPATH_SITE.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'com_sportsmanagement'.DIRECTORY_SEPARATOR.'pressebericht'))
{
Folder::create(JPATH_SITE.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'com_sportsmanagement'.DIRECTORY_SEPARATOR.'pressebericht');
}			
            $importFile = JPATH_SITE.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'com_sportsmanagement'.DIRECTORY_SEPARATOR.'pressebericht'.DIRECTORY_SEPARATOR.$match_id.'.jlg';
            
			if (File::exists($importFile))
			{
				File::delete($importFile);
			}
            
			if (File::exists($tempFilePath))
			{
					if (File::exists($dest))
					{
						File::delete($dest);
					}
					if (!File::upload($tempFilePath,$dest))
					{
						JError::raiseWarning(500,Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_CANT_UPLOAD'));
						return;
					}
					else
					{
						if (strtolower(File::getExt($dest))=='zip')
						{
							$result=JArchive::extract($dest,$extractdir);
							if ($result === false)
							{
								JError::raiseWarning(500,Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_EXTRACT_ERROR'));
								return false;
							}
							File::delete($dest);
							$src=Folder::files($extractdir,'jlg',false,true);
							if(!count($src))
							{
								JError::raiseWarning(500,'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_EXTRACT_NOJLG');
								//todo: delete every extracted file / directory
								return false;
							}
							if (strtolower(File::getExt($src[0]))=='jlg')
							{
								if (!@ rename($src[0],$importFile))
								{
									JError::raiseWarning(21,Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_ERROR_RENAME'));
									return false;
								}
							}
							else
							{
								JError::raiseWarning(500,Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_TMP_DELETED'));
								return;
							}
						}
						else
						{
							if (strtolower(File::getExt($dest))=='csv')
							{
								if (!@ rename($dest,$importFile))
								{
									JError::raiseWarning(21,Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_RENAME_FAILED'));
									return false;
								}
							}
							else
							{
								JError::raiseWarning(21,Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_WRONG_EXTENSION'));
								return false;
							}
						}
					}
			}
		}
        //$csv_file = $model->getPressebericht();  
		//$link='index.php?option=com_sportsmanagement&tmpl=component&task=match.readpressebericht&match_id='.$match_id;
		$link='index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=readpressebericht&match_id='.$match_id;	
	    $this->setRedirect($link,$msg);    
        
        
    }
       
    /**
     * sportsmanagementControllermatch::pressebericht()
     * 
     * @return void
     */
    function pressebericht()
    {
    Factory::getApplication()->input->setVar('hidemainmenu',1);
	Factory::getApplication()->input->setVar('layout','pressebericht');
	Factory::getApplication()->input->setVar('view','match');
	Factory::getApplication()->input->setVar('edit',true);
	
	parent::display();    
        
    }
    
    /**
     * sportsmanagementControllermatch::convertUiDateTimeToMatchDate()
     * 
     * @param mixed $uiDate
     * @param mixed $uiTime
     * @param mixed $timezone
     * @return
     */
    private function convertUiDateTimeToMatchDate($uiDate, $uiTime, $timezone)
	{
		$format = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_DATE_FORMAT');

		if (((!strpos($uiDate,'-')!==false) && (!strpos($uiDate,'.')!==false)) && (strlen($uiDate) <= 8 ))
		{
			// to support short date inputs
			if (strlen($uiDate) == 8 )
			{
				if ($format == 'Y-m-d') 
				{
					// for example 20111231 is used for 31 december 2011
					$dateStr = substr($uiDate,0,4) . '-' . substr($uiDate,4,2) . '-' . substr($uiDate,6,2);
				} 
				elseif ($format == 'd-m-Y')
				{
					// for example 31122011 is used for 31 december 2011
					$dateStr = substr($uiDate,0,2) . '-' . substr($uiDate,2,2) . '-' . substr($uiDate,4,4);
				}
				elseif ($format == 'd.m.Y')
				{
					// for example 31122011 is used for 31 december 2011
					$dateStr = substr($uiDate,0,2) . '.' . substr($uiDate,2,2) . '.' . substr($uiDate,4,4);
				}
			}
			
			elseif (strlen($uiDate) == 6 )
			{
				if ($format == 'Y-m-d') 
				{
					// for example 111231 is used for 31 december 2011
					$dateStr = substr(date('Y'),0,2) . substr($uiDate,0,2) . '-' . substr($uiDate,2,2) . '-' . substr($uiDate,4,2);
				} 
				elseif ($format == 'd-m-Y')
				{
					// for example 311211 is used for 31 december 2011
					$dateStr = substr($uiDate,0,2) . '-' . substr($uiDate,2,2) . '-' . substr(date('Y'),0,2) . substr($uiDate,4,2);
				}
				elseif ($format == 'd.m.Y')
				{
					// for example 311211 is used for 31 december 2011
					$dateStr = substr($uiDate,0,2) . '.' . substr($uiDate,2,2) . '.' . substr(date('Y'),0,2) . substr($uiDate,4,2);
				}
			}
		}
		else
		{
			$dateStr = $uiDate;
		}

		if (!empty($uiTime))
		{
			$format  .= ' H:i';

			if(strpos($uiTime,":")!==false)
			{
				$dateStr .= ' '.$uiTime;
			}
			// to support short time inputs
			// for example 2158 is used instead of 21:58
			elseif (strlen($uiTime) == 4 )
			{
				$dateStr .= ' '.substr($uiTime, 0, -2) . ':' . substr($uiTime, -2);  
			}
			// for example 21 is used instead of 2100
			elseif (strlen($uiTime) == 2 )
			{
				$dateStr .= ' '.$uiTime. ':00';  
			}
		}
		$timestamp = DateTime::createFromFormat($format, $dateStr, $timezone);
		if(is_object($timestamp))  {
			$timestamp->setTimezone(new DateTimeZone('UTC'));
			return $timestamp->format('Y-m-d H:i:s');
		} else {
			return '0000-00-00 00:00:00';
		}
	}
       
    

}
