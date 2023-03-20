<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage season
 * @file       season.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelseason
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelseason extends JSMModelAdmin
{

	/**
	 * Override parent constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see   BaseDatabaseModel
	 * @since 3.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * sportsmanagementModelseason::saveshortpersons()
	 *
	 * @return void
	 */
	function saveshortpersons()
	{
		$msg = '';
		$pks        = $this->jsmjinput->getVar('cid', null, 'post', 'array');
		$teams      = $this->jsmjinput->getVar('team_id', null, 'post', 'array');
		$season_id  = $this->jsmjinput->getVar('season_id', 0, 'post', 'array');
		$project_id = $this->jsmjinput->getVar('project_id', 0, 'post', 'array');
		$persontype = $this->jsmjinput->getVar('persontype', 0, 'post', 'array');
        $whichview = $this->jsmjinput->getVar('whichview', 0, 'post', 'array');
		$post   = Factory::getApplication()->input->post->getArray(array());

//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . '<pre>'.print_r($teams,true).'</pre>'  , 'Error');
		
		foreach ($pks as $key => $value)
		{
			$this->jsmquery->clear();
			$columns = array('person_id', 'season_id', 'modified', 'modified_by');
			$values  = array($value, $season_id, $this->jsmdb->Quote('' . $this->jsmdate->toSql() . ''), $this->jsmuser->get('id'));
			$this->jsmquery
				->insert($this->jsmdb->quoteName('#__sportsmanagement_season_person_id'))
				->columns($this->jsmdb->quoteName($columns))
				->values(implode(',', $values));

			try
			{
				$this->jsmdb->setQuery($this->jsmquery);
				$this->jsmdb->execute();
			}
			catch (Exception $e)
			{
				$row = Table::getInstance('season', 'sportsmanagementTable');
				$row->load($season_id);
				$this->jsmapp->enqueueMessage('Saisonzuordnung : ' . $row->name . ' schon vorhanden.', 'notice');
			}
/** hier machen wir schon mal einen insert*/
switch ($whichview )
{
case 'teamplayers':
$personposition	= $post['position' . $value];
$this->jsmquery->clear();
$this->jsmquery->select('id');
$this->jsmquery->from('#__sportsmanagement_project_position');
$this->jsmquery->where('project_id = ' . $project_id);
$this->jsmquery->where('position_id = ' . $personposition);
try
{
$this->jsmdb->setQuery($this->jsmquery);
$project_position_id = $this->jsmdb->loadResult();		
}
catch (Exception $e)
{
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'error');
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'error');
$this->jsmapp->enqueueMessage('<pre>'.print_r($this->jsmquery->dump(),true).'</pre>', 'error');

//$msg .= '<br>'.Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage());
//$msg .= '<br>'.Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__);
}
		
$profile = new stdClass;
$profile->project_id = $project_id;
$profile->person_id = $value;
$profile->published = 1;
$profile->project_position_id = $project_position_id;
$profile->persontype = $persontype;
$profile->modified = $this->jsmdate->toSql();
$profile->modified_by = $this->jsmuser->get('id');
try
{
$result = $this->jsmdb->insertObject('#__sportsmanagement_person_project_position', $profile);
}
catch (Exception $e)
{
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'error');
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'error');    
//$msg .= '<br>'.Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage());
//$msg .= '<br>'.Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__);
}
break;
}
            
            if ($persontype == 3)
				{
					$this->jsmquery->clear();
					$this->jsmquery->select('id');
					$this->jsmquery->from('#__sportsmanagement_season_person_id');
					$this->jsmquery->where('season_id = ' . $season_id);
					$this->jsmquery->where('person_id = ' . $value);
					$this->jsmdb->setQuery($this->jsmquery);
					$new_id = $this->jsmdb->loadResult();

					$mdlTable              = new stdClass;
					$mdlTable->id          = $new_id;
					$mdlTable->modified    = $this->jsmdate->toSql();
					$mdlTable->modified_by = $this->jsmuser->get('id');
					$mdlTable->persontype  = 3;
					$mdlTable->published   = 1;

					try
					{
						$resultupdate = $this->jsmdb->updateObject('#__sportsmanagement_season_person_id', $mdlTable, 'id');
					}
					catch (Exception $e)
					{
					   $this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'error');
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'error');
						//$msg .= '<br>'.Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage());
//						$msg .= '<br>'.Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__);
					}

		    			$this->jsmquery->clear();
					$this->jsmquery->select('id');
					$this->jsmquery->from('#__sportsmanagement_project_referee');
					$this->jsmquery->where('project_id = ' . $project_id);
					$this->jsmquery->where('person_id = ' . $new_id);
					$this->jsmdb->setQuery($this->jsmquery);
					$ref_id = $this->jsmdb->loadResult();
		    
		    
		    if ( !$ref_id )
		    {
					$profile              = new stdClass;
					$profile->project_id  = $project_id;
					$profile->person_id   = $new_id;
					$profile->published   = 1;
					$profile->modified    = $this->jsmdate->toSql();
					$profile->modified_by = $this->jsmuser->get('id');

					try
					{
						$resultproref = $this->jsmdb->insertObject('#__sportsmanagement_project_referee', $profile);
					}
					catch (Exception $e)
					{
					   $this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'error');
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'error');
					//	$msg .= '<br>'.Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage());
//						$msg .= '<br>'.Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__);
					}
		    
	    }
		    
				}
            

			if (isset($teams) && $persontype != 3)
			{
			 if ( !$teams )
             {
             $teams = 0;   
             }
             if ( !$persontype )
             {
             $persontype = 1;   
             }
             
				$this->jsmquery->clear();
				$columns = array('person_id', 'season_id', 'team_id', 'published', 'persontype', 'modified', 'modified_by');
				$values  = array($value, $season_id, $teams, '1', $persontype, $this->jsmdb->Quote('' . $this->jsmdate->toSql() . ''), $this->jsmuser->get('id'));
				$this->jsmquery
					->insert($this->jsmdb->quoteName('#__sportsmanagement_season_team_person_id'))
					->columns($this->jsmdb->quoteName($columns))
					->values(implode(',', $values));
				try
				{
					$this->jsmdb->setQuery($this->jsmquery);
					$this->jsmdb->execute();
				}
				catch (Exception $e)
				{
					$msg .= '<br>'.Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage());
					$msg .= '<br>'.Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__);
				}
			}
		}
return $msg;
	}

	/**
	 * sportsmanagementModelseason::saveshortteams()
	 *
	 * @return void
	 */
	function saveshortteams()
	{
		$pks       = $this->jsmjinput->getVar('cid', null, 'post', 'array');
		$season_id = $this->jsmjinput->getVar('season_id', 0, 'post', 'array');

		foreach ($pks as $key => $value)
		{
			$this->jsmquery->clear();
			$columns = array('team_id', 'season_id');
			$values  = array($value, $season_id);
			$this->jsmquery
				->insert($this->jsmdb->quoteName('#__sportsmanagement_season_team_id'))
				->columns($this->jsmdb->quoteName($columns))
				->values(implode(',', $values));

			try
			{
				$this->jsmdb->setQuery($this->jsmquery);
				$this->jsmdb->execute();
			}
			catch (Exception $e)
			{
				//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getMessage()), 'Error');
				//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getCode()), 'Error');
			}
		}

	}

}
