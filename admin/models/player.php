<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage player
 * @file       player.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filter\OutputFilter;

/**
 * sportsmanagementModelplayer
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2019
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelplayer extends JSMModelAdmin
{

	/**
	 * sportsmanagementModelplayer::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

	}


/**
 * sportsmanagementModelplayers::importupload()
 * https://www.w3schools.com/php/php_file_upload.asp
 * @param mixed $post
 * @return void
 */
function importupload($post = array())
{
jimport('joomla.filesystem.file');

/**    
Factory::getApplication()->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' task <pre>'.print_r(Factory::getApplication()->input->post->getArray(),true).'</pre>'  ), '');
$target_dir = "tmp/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
Factory::getApplication()->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' task <pre>'.print_r($target_file,true).'</pre>'  ), '');


// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
  echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
  if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
  } else {
    echo "Sorry, there was an error uploading your file.";
  }
}
*/

// Cleans the name of teh file by removing weird characters
$filename = File::makeSafe($_FILES["fileToUpload"]['name']); 

$src  = $_FILES["fileToUpload"]['tmp_name'];
$dest = JPATH_BASE . '/tmp/' . $filename;

Factory::getApplication()->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' filename <pre>'.print_r($filename,true).'</pre>'  ), '');
Factory::getApplication()->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' src <pre>'.print_r($src,true).'</pre>'  ), '');
Factory::getApplication()->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' dest <pre>'.print_r($dest,true).'</pre>'  ), '');



if (File::upload($src, $dest)) 
{
      echo 'The file has successfully been uploaded :)<br>';
} 
else 
{
      echo 'Oh crap, something happened. Run!<br>';
}


// lastname,firstname,birthday,country,club,classement (extrafield),licence/registrationN,gender
$file = $dest;
$handle = fopen($file,"r");

        while(($fileop = fgetcsv($handle,1000,",")) !== false)
        {
			/**
            echo $fileop[0].' - ';
			echo $fileop[1].' - ';
			echo $fileop[2].' - ';
			echo $fileop[3].' - ';
			echo $fileop[4].' - ';
			echo $fileop[5].' - ';
			echo $fileop[6].' - ';
			echo $fileop[7].' <br> ';
			*/
            $temp = new stdClass();
     $temp->id = 0; 
$temp->lastname  = ucfirst(strtolower($fileop[0]));
$temp->firstname  = $fileop[1];
$temp->birthday  = $fileop[2];

switch ( $fileop[3] )
{
	case 'France':
$temp->country  = 'FRA';
	break;
}

switch ( $fileop[7] )
{
	case 'Male':
$temp->gender  = 1;
	break;
	case 'Female':
$temp->gender  = 2;
	break;
}

//$temp->alias = OutputFilter::stringURLSafe($this->name);
$parts = array(trim($temp->firstname), trim($temp->lastname));
$temp->alias = OutputFilter::stringURLSafe(implode(' ', $parts));

$temp->club  = $fileop[4];
$temp->classement  = $fileop[5];
$temp->knvbnr  = $fileop[6];
//$temp->gender  = $fileop[7];
$players_upload[] = $temp;

        }

Factory::getApplication()->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' players_upload <pre>'.print_r($players_upload,true).'</pre>'  ), '');

foreach ( $players_upload as $key => $value) if ( $key > 0 )
{
//for($a=1; $a < sizeof($players_upload); $a++ )        
//{  
$this->jsmquery->clear();
$this->jsmquery->select('id');
$this->jsmquery->from('#__sportsmanagement_person');
$this->jsmquery->where('firstname like ' . $this->jsmdb->Quote('' . $value->firstname . '') );
$this->jsmquery->where('lastname like ' . $this->jsmdb->Quote('' . $value->lastname . '') );
$this->jsmquery->where('birthday like ' . $this->jsmdb->Quote('' . $value->birthday . '') );
$this->jsmquery->where('country like ' . $this->jsmdb->Quote('' . $value->country . '') );
$this->jsmdb->setQuery($this->jsmquery );
$res = $this->jsmdb->loadResult();

if ( !$res )
{
$profile             = new stdClass;
$profile->firstname       = $value->firstname;
$profile->lastname      = $value->lastname;
$profile->birthday  = $value->birthday;
$profile->alias  = $value->alias;
$profile->knvbnr   = $value->knvbnr;
$profile->gender   = $value->gender;
$profile->country   = $value->country;
try
{
$insertresult = $this->jsmdb->insertObject('#__sportsmanagement_person', $profile);   
}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'error');
			$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'error');
		
		}
            
}


}











    
    }
    
    
    
	/**
	 * sportsmanagementModelplayer::getAgeGroupID()
	 *
	 * @param   mixed  $age
	 *
	 * @return
	 */
	public function getAgeGroupID($age)
	{

		if (is_numeric($age))
		{
			$this->jsmquery->clear();
			$this->jsmquery->select('id');
			$this->jsmquery->from('#__sportsmanagement_agegroup ');
			$this->jsmquery->where($age . " >= age_from and " . $age . " <= age_to");

			try
			{
				$this->jsmdb->setQuery($this->jsmquery);
				$person_range = $this->jsmdb->loadResult();
			}
			catch (Exception $e)
			{
				$this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');

				return false;
			}
		}
		else
		{
			$person_range = 0;
		}

		return $person_range;

	}


	/**
	 * sportsmanagementModelplayer::getPerson()
	 *
	 * @param   integer  $person_id
	 * @param   integer  $season_person_id
	 * @param   integer  $inserthits
	 *
	 * @return
	 */
	function getPerson($person_id = 0, $season_person_id = 0, $inserthits = 0)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('p.*');
		$this->jsmquery->from('#__sportsmanagement_person as p');

		if ($person_id)
		{
			$this->jsmquery->where('p.id = ' . $person_id);
		}

		if ($season_person_id)
		{
			$this->jsmquery->join('INNER', '#__sportsmanagement_season_person_id AS tp on tp.person_id = p.id');
			$this->jsmquery->where('tp.id = ' . $season_person_id);
		}

		try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			return $this->jsmdb->loadObject();
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');
			return false;
		}

	}


	/**
	 * sportsmanagementModelplayer::saveshort()
	 *
	 * @return
	 */
	function saveshort()
	{
		$pks  = $this->jsmjinput->getVar('cid', null, 'post', 'array');
		$post = $this->jsmjinput->post->getArray(array());

		$result = true;

		for ($x = 0; $x < count($pks); $x++)
		{
			$tblPerson            = new stdClass;
			$tblPerson->id        = $pks[$x];
			$tblPerson->firstname = $post['firstname' . $pks[$x]];
			$tblPerson->lastname  = $post['lastname' . $pks[$x]];
			$tblPerson->nickname  = $post['nickname' . $pks[$x]];
            $tblPerson->knvbnr  = $post['knvbnr' . $pks[$x]];

			if ($post['birthday' . $pks[$x]] != '0000-00-00' && $post['birthday' . $pks[$x]] != '')
			{
				$tblPerson->birthday           = sportsmanagementHelper::convertDate($post['birthday' . $pks[$x]], 0);
				$tblPerson->birthday_timestamp = sportsmanagementHelper::getTimestamp($tblPerson->birthday);
			}

			if ($post['deathday' . $pks[$x]] != '0000-00-00' && $post['deathday' . $pks[$x]] != '')
			{
				$tblPerson->deathday           = sportsmanagementHelper::convertDate($post['deathday' . $pks[$x]], 0);
				$tblPerson->deathday_timestamp = sportsmanagementHelper::getTimestamp($tblPerson->deathday);
			}

			$tblPerson->country     = $post['country' . $pks[$x]];
			$tblPerson->position_id = $post['position' . $pks[$x]];
			$tblPerson->agegroup_id = $post['agegroup' . $pks[$x]];

			try
			{
				$result = Factory::getDbo()->updateObject('#__sportsmanagement_person', $tblPerson, 'id');
			}
			catch (Exception $e)
			{
				$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
				$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');

				return false;
			}

		}

		return $result;
	}


	/**
	 * sportsmanagementModelplayer::storeAssign()
	 *
	 * @param   mixed  $post
	 *
	 * @return
	 */
	function storeAssign($post)
	{
		$app = Factory::getApplication();
		$jinput      = $app->input;
		$option      = $jinput->getCmd('option');
		$db          = Factory::getDbo();
		$date        = Factory::getDate();

		$this->_project_id      = $app->getUserState("$option.pid", '0');
		$this->_team_id         = $app->getUserState("$option.team_id", '0');
		$this->_project_team_id = $app->getUserState("$option.project_team_id", '0');
		$this->_season_id       = $app->getUserState("$option.season_id", '0');
		$cid                    = $post['cid'];

		$mdlPerson      = BaseDatabaseModel::getInstance("person", "sportsmanagementModel");
		$mdlPersonTable = $mdlPerson->getTable();

		switch ($post['persontype'])
		{
			/** spieler */
			case 1:
				$mdl      = BaseDatabaseModel::getInstance("seasonteamperson", "sportsmanagementModel");
				$mdlTable = $mdl->getTable();

				for ($x = 0; $x < count($cid); $x++)
				{
					$mdlPersonTable->load($cid[$x]);
					$mdlTable             = $mdl->getTable();
					$mdlTable->team_id    = $this->_team_id;
					$mdlTable->season_id  = $this->_season_id;
					$mdlTable->persontype = 1;

					$mdlTable->modified    = $this->jsmdate->toSql();
					$mdlTable->modified_by = $this->jsmuser->get('id');

					$mdlTable->picture   = $mdlPersonTable->picture;
					$mdlTable->active    = 1;
					$mdlTable->published = 1;
					$mdlTable->person_id = $cid[$x];

					try
					{
						if ($mdlTable->store() === false)
						{
							sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
						}
						else
						{
						}
					}
					catch (Exception $e)
					{
						$msg  = $e->getMessage(); // Returns "Normally you would have other code...
						$code = $e->getCode(); // Returns '500';
						Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
					}

					/** Projekt position eintragen
					zuerst die positions id zum projekt ermitteln. */
					$this->jsmquery->clear();
					$this->jsmquery->select('id');
					$this->jsmquery->from('#__sportsmanagement_project_position');
					$this->jsmquery->where('project_id = ' . $this->_project_id);
					$this->jsmquery->where('position_id =' . $mdlPersonTable->position_id);
					$this->jsmdb->setQuery($this->jsmquery);

					try
					{
						$res = $this->jsmdb->loadResult();
					}
					catch (Exception $e)
					{
						$msg  = $e->getMessage(); // Returns "Normally you would have other code...
						$code = $e->getCode(); // Returns '500';
						Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
					}

					if (!$res)
					{
						$res = 0;
					}

					$insertquery = $db->getQuery(true);
					$columns = array('person_id', 'project_id', 'project_position_id', 'persontype', 'modified', 'modified_by');
					$values = array($cid[$x], $this->_project_id, $res, 1, $db->Quote('' . $this->jsmdate->toSql() . ''), $this->jsmuser->get('id'));
					$insertquery
						->insert($db->quoteName('#__sportsmanagement_person_project_position'))
						->columns($db->quoteName($columns))
						->values(implode(',', $values));
					$db->setQuery($insertquery);

					try
					{
						sportsmanagementModeldatabasetool::runJoomlaQuery();
					}
					catch (Exception $e)
					{
						$msg  = $e->getMessage(); // Returns "Normally you would have other code...
						$code = $e->getCode(); // Returns '500';
						Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
					}
				}
				break;
			/** trainer */
			case 2:
				$mdl      = BaseDatabaseModel::getInstance("seasonteamperson", "sportsmanagementModel");
				$mdlTable = $mdl->getTable();

				for ($x = 0; $x < count($cid); $x++)
				{
					$mdlPersonTable->load($cid[$x]);
					$mdlTable             = $mdl->getTable();
					$mdlTable->team_id    = $this->_team_id;
					$mdlTable->season_id  = $this->_season_id;
					$mdlTable->persontype = 2;

					$mdlTable->modified    = $this->jsmdate->toSql();
					$mdlTable->modified_by = $this->jsmuser->get('id');

					$mdlTable->picture   = $mdlPersonTable->picture;
					$mdlTable->active    = 1;
					$mdlTable->published = 1;
					$mdlTable->person_id = $cid[$x];

					try
					{
						if ($mdlTable->store() === false)
						{
							sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
						}
						else
						{
						}
					}
					catch (Exception $e)
					{
						$msg  = $e->getMessage(); // Returns "Normally you would have other code...
						$code = $e->getCode(); // Returns '500';
						Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
					}

					/** Projekt position eintragen
					 zuerst die positions id zum projekt ermitteln. */
					$this->jsmquery->clear();
					$this->jsmquery->select('id');
					$this->jsmquery->from('#__sportsmanagement_project_position');
					$this->jsmquery->where('project_id = ' . $this->_project_id);
					$this->jsmquery->where('position_id =' . $mdlPersonTable->position_id);
					$this->jsmdb->setQuery($this->jsmquery);

					try
					{
						$res = $this->jsmdb->loadResult();
					}
					catch (Exception $e)
					{
						$msg  = $e->getMessage(); // Returns "Normally you would have other code...
						$code = $e->getCode(); // Returns '500';
						Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
					}

					if (!$res)
					{
						$res = 0;
					}

					$insertquery = $db->getQuery(true);
					$columns = array('person_id', 'project_id', 'project_position_id', 'persontype', 'modified', 'modified_by');
					$values = array($cid[$x], $this->_project_id, $res, 2, $db->Quote('' . $this->jsmdate->toSql() . ''), $this->jsmuser->get('id'));
					$insertquery
						->insert($db->quoteName('#__sportsmanagement_person_project_position'))
						->columns($db->quoteName($columns))
						->values(implode(',', $values));
					$db->setQuery($insertquery);

					try
					{
						sportsmanagementModeldatabasetool::runJoomlaQuery();
					}
					catch (Exception $e)
					{
						$msg  = $e->getMessage(); // Returns "Normally you would have other code...
						$code = $e->getCode(); // Returns '500';
						Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
					}
				}
				break;
			/** schiedsrichter */
			case 3:
				$mdl      = BaseDatabaseModel::getInstance("seasonperson", "sportsmanagementModel");
				$mdlTable = $mdl->getTable();

				for ($x = 0; $x < count($cid); $x++)
				{
					$query = $db->getQuery(true);
					$query->select('id');
					$query->from('#__sportsmanagement_season_person_id');
					$query->where('person_id = ' . $cid[$x]);
					$query->where('season_id = ' . $this->_season_id);
					$db->setQuery($query);
					$season_person_id = $db->loadResult();

					$mdlPersonTable->load($cid[$x]);

					$mdlTable              = new stdClass;
					$mdlTable->id          = $season_person_id;
					$mdlTable->person_id   = $cid[$x];
					$mdlTable->team_id     = 0;
					$mdlTable->season_id   = $this->_season_id;
					$mdlTable->modified    = $this->jsmdate->toSql();
					$mdlTable->modified_by = $this->jsmuser->get('id');
					$mdlTable->picture     = $mdlPersonTable->picture;
					$mdlTable->persontype  = 3;
					$mdlTable->published   = 1;

					try
					{
						$result           = $db->insertObject('#__sportsmanagement_season_person_id', $mdlTable);
						$season_person_id = $db->insertid();
					}
					catch (Exception $e)
					{
						$result = $db->updateObject('#__sportsmanagement_season_person_id', $mdlTable, 'id');
					}

					$profile              = new stdClass;
					$profile->project_id  = $this->_project_id;
					$profile->person_id   = $season_person_id;
					$profile->published   = 1;
					$profile->modified    = $db->Quote('' . $this->jsmdate->toSql() . '');
					$profile->modified_by = $this->jsmuser->get('id');

					try
					{
						$result = Factory::getDbo()->insertObject('#__sportsmanagement_project_referee', $profile);
					}
					catch (Exception $e)
					{
						$result = $db->updateObject('#__sportsmanagement_season_person_id', $mdlTable, 'id');
					}
				}
				break;
		}

		return true;
	}

}
