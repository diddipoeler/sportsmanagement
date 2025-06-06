<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage division
 * @file       division.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementModeldivision
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModeldivision extends JSMModelAdmin
{

	/**
	 * sportsmanagementModeldivision::divisiontoproject()
	 *
	 * @return void
	 */
	function divisiontoproject()
	{
		$post = $this->jsmjinput->post->getArray(array());

		// $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' post -> <pre>'.print_r($post,true).'</pre>'),'');

		$divisions  = $post['cid'];
		$project_id = $post['pid'];

		$this->jsmquery->clear();
		$this->jsmquery->select('p.name');
		$this->jsmquery->from('#__sportsmanagement_season AS s');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project AS p on p.season_id = s.id');
		$this->jsmquery->where('p.id = ' . $project_id);
		$this->jsmdb->setQuery($this->jsmquery);
		$reaulseasonname = $this->jsmdb->loadResult();

		// $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' reaulseasonname -> <pre>'.print_r($reaulseasonname,true).'</pre>'),'');

		foreach ($divisions as $key => $value)
		{
			$this->jsmquery->clear();
			$this->jsmquery->select('dv.name');
			$this->jsmquery->from('#__sportsmanagement_division AS dv');
			$this->jsmquery->where('dv.project_id = ' . $project_id);
			$this->jsmquery->where('dv.id = ' . $value);
			$this->jsmdb->setQuery($this->jsmquery);
			$resultdvname = $this->jsmdb->loadResult();

			// $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' resultdvname -> <pre>'.print_r($resultdvname,true).'</pre>'),'');

			// $orig_table = $this->getTable('project');
			$orig_table = clone $this->getTable('project');
			$orig_table->load((int) $project_id);
			$orig_table->id    = null;
			$orig_table->name  = $reaulseasonname . ' ' . $resultdvname ;
            $orig_table->project_type = 'SIMPLE_LEAGUE';
			$orig_table->alias = OutputFilter::stringURLSafe($orig_table->name);
            $orig_table->published = 1;

			// $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' orig_table -> <pre>'.print_r($orig_table,true).'</pre>'),'');
			$new_project_id = 0;

			try
			{
				$result         = $this->jsmdb->insertObject('#__sportsmanagement_project', $orig_table);
				$new_project_id = $this->jsmdb->insertid();
			}
			catch (Exception $e)
			{
				Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
				Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');
			}

			if ($new_project_id)
			{
				$this->jsmquery->clear();

				// Fields to update.
				$fields = array(
					$this->jsmdb->quoteName('project_id') . ' = ' . $new_project_id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$this->jsmdb->quoteName('id') . ' = ' . $value,
					$this->jsmdb->quoteName('project_id') . ' = ' . $project_id
				);
				$this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_division'))->set($fields)->where($conditions);
				$this->jsmdb->setQuery($this->jsmquery);

				try
				{
					$resultupdate1 = $this->jsmdb->execute();
				}
				catch (Exception $e)
				{
					Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
					Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');
				}

				$this->jsmquery->clear();

				// Conditions for which records should be updated.
				$conditions = array(
					$this->jsmdb->quoteName('division_id') . ' = ' . $value,
					$this->jsmdb->quoteName('project_id') . ' = ' . $project_id
				);
				$this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_project_team'))->set($fields)->where($conditions);
				$this->jsmdb->setQuery($this->jsmquery);

				try
				{
					$resultupdate2 = $this->jsmdb->execute();
				}
				catch (Exception $e)
				{
					Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
					Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');
				}
			}
		}

	}
    
    
    /**
     * sportsmanagementModeldivision::massadd()
     * 
     * @return
     */
    function massadd()
	{
		$post            = Factory::getApplication()->input->post->getArray(array());
		$project_id      = $this->jsmapp->getUserState("$this->jsmoption.pid", '0');
		$add_division_count = (int) $post['add_division_count'];
		$max = 0;

		if ($add_division_count > 0) // Only MassAdd a number of new and empty rounds
		{
			$max = $this->getMaxDivision($project_id);
			$max++;
			$i = 0;

			for ($x = 0; $x < $add_division_count; $x++)
			{
				$i++;
				$tblDivision             =& $this->getTable();
				$tblDivision->project_id = $project_id;
				$tblDivision->ordering  = $max;
				$tblDivision->name       = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_CTRL_DIVISION_NAME', $max);
                $tblDivision->alias = OutputFilter::stringURLSafe($tblDivision->name);
                $tblDivision->modified         = $this->jsmdate->toSql();
		        $tblDivision->modified_by      = $this->jsmuser->get('id');

				try{
					$tblDivision->store();
					$msg = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_CTRL_ROUNDS_ADDED', $i);
					}
		catch (Exception $e)
		{
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
		$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_CTRL_ERROR_ADD') . $e->getMessage();	
		}
				

				$max++;
			}
		}

		return $msg;

	}
    
    /**
     * sportsmanagementModeldivision::getMaxDivision()
     * 
     * @param mixed $project_id
     * @return
     */
    function getMaxDivision($project_id)
	{
        $this->jsmquery->clear();
		$this->jsmquery->select('COUNT(ordering)');
		$this->jsmquery->from('#__sportsmanagement_division');
		$this->jsmquery->where('project_id = ' . (int) $project_id);
		$result = 0;

		if ($project_id > 0)
		{
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->loadResult();
		}

		return $result;
	}

	/**
	 * sportsmanagementModeldivision::count_teams_division()
	 *
	 * @param   integer  $division_id
	 *
	 * @return void
	 */
	function count_teams_division($division_id = 0)
	{
		$results        = array();
		$division_teams = array();

		try
		{
			$this->jsmquery->clear();
			$this->jsmquery->select('m.projectteam1_id');
			$this->jsmquery->from('#__sportsmanagement_match as m');
			$this->jsmquery->where('division_id = ' . $division_id);
			$this->jsmquery->where('projectteam1_id != 0');
			$this->jsmquery->group('projectteam1_id');
			$this->jsmdb->setQuery($this->jsmquery);
			$results = $this->jsmdb->loadObjectList('projectteam1_id');
		}
		catch (Exception $e)
		{
			$msg     = $e->getMessage(); // Returns "Normally you would have other code...
			$code    = $e->getCode(); // Returns '500';
			$results = array();
		}

		foreach ($results as $key => $value)
		{
			$division_teams[$key] = $key;
		}

		try
		{
			$this->jsmquery->clear();
			$this->jsmquery->select('m.projectteam2_id');
			$this->jsmquery->from('#__sportsmanagement_match as m');
			$this->jsmquery->where('division_id = ' . $division_id);
			$this->jsmquery->where('projectteam2_id != 0');
			$this->jsmquery->group('projectteam2_id');
			$this->jsmdb->setQuery($this->jsmquery);
			$results = $this->jsmdb->loadObjectList('projectteam2_id');
		}
		catch (Exception $e)
		{
			$msg     = $e->getMessage(); // Returns "Normally you would have other code...
			$code    = $e->getCode(); // Returns '500';
			$results = array();

			// Factory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
		}

		foreach ($results as $key => $value)
		{
			$division_teams[$key] = $key;
		}

		try
		{
			$this->jsmquery->clear();
			$this->jsmquery->select('id');
			$this->jsmquery->from('#__sportsmanagement_project_team');
			$this->jsmquery->where('division_id = ' . $division_id);
			$this->jsmdb->setQuery($this->jsmquery);
			$results = $this->jsmdb->loadObjectList('id');
		}
		catch (Exception $e)
		{
			$msg     = $e->getMessage(); // Returns "Normally you would have other code...
			$code    = $e->getCode(); // Returns '500';
			$results = array();

			// Factory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
		}

		foreach ($results as $key => $value)
		{
			$division_teams[$key] = $key;
		}

		return count($division_teams);

	}

	/**
	 * sportsmanagementModeldivision::saveshort()
	 *
	 * @return
	 */
	public function saveshort()
	{
		$app  = Factory::getApplication();
		$date = Factory::getDate();
		$user = Factory::getUser();

		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		$pks = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');

		if (!$pks)
		{
			return Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_SAVE_NO_SELECT');
		}

		$post = Factory::getApplication()->input->post->getArray(array());

		for ($x = 0; $x < count($pks); $x++)
		{
			$tblRound       = &$this->getTable();
			$tblRound->id   = $pks[$x];
			$tblRound->name = $post['name' . $pks[$x]];

			$tblRound->alias = OutputFilter::stringURLSafe($post['name' . $pks[$x]]);

			$tblRound->modified    = $date->toSql();
			$tblRound->modified_by = $user->get('id');

			if (!$tblRound->store())
			{
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);

				return false;
			}
		}

		return Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_SAVE');
	}


	/**
	 * Method to remove division
	 *
	 * @access public
	 * @return boolean    True on success
	 * @since  0.1
	 */
	public function delete(&$pks)
	{
		$app = Factory::getApplication();

		return parent::delete($pks);

	}


}
