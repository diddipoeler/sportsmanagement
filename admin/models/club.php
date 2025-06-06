<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       club.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filter\OutputFilter;

/**
 * sportsmanagementModelclub
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelclub extends JSMModelAdmin
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
 * sportsmanagementModelclub::getlogohistory()
 * 
 * @param integer $club_id
 * @param integer $season_id
 * @param bool $logoonly
 * @return
 */
function getlogohistory($club_id = 0, $season_id = 0, $team_id = 0, $logoonly = false )
	{
$app    = Factory::getApplication();
$db    = Factory::getDbo();
$query = $db->getQuery(true);
$result    = array();

$query->select('cl.*,se.name as seasonname');
$query->from('#__sportsmanagement_club_logos as cl');
$query->join('INNER', '#__sportsmanagement_season AS se ON se.id = cl.season_id');

if ( $team_id )
{
$query->join('INNER', '#__sportsmanagement_club AS c ON c.id = cl.club_id');
$query->join('INNER', '#__sportsmanagement_team AS t ON t.club_id = c.id');
$query->where('t.id = ' . $team_id);    
}

if ( $club_id )
{		
$query->where('cl.club_id = ' . $club_id);
}

if ( $season_id )
{
$query->where('se.id = ' . $season_id);
}

$query->order('seasonname DESC');		
$db->setQuery($query);
try
			{
				$result = $db->loadObjectList();
				$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
			}
			catch (Exception $e)
			{
				$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
				$msg  = $e->getMessage(); // Returns "Normally you would have other code...
				$code = $e->getCode(); // Returns '500';
				$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
				//$result = false;
			}		

		
return $result;


	}
	
	/**
	 * sportsmanagementModelclub::getuserextrafieldvalue()
	 * 
	 * @param integer $club_id
	 * @param string $fieldtext
	 * @return
	 */
	function getuserextrafieldvalue($club_id = 0,$fieldtext = '' )
	{
	if ( $club_id && $fieldtext )
	{
	$this->jsmquery->clear();
		$this->jsmquery->select('uefv.fieldvalue');
		$this->jsmquery->from('#__sportsmanagement_user_extra_fields_values AS uefv');
		$this->jsmquery->join('INNER', '#__sportsmanagement_user_extra_fields AS uef ON uef.id = uefv.field_id');
		$this->jsmquery->where('uefv.jl_id = ' . $club_id);
		$this->jsmquery->where('uef.name LIKE ' . $this->jsmdb->Quote('%' . $fieldtext . '%'));
		$this->jsmquery->where('uef.template_backend LIKE ' . $this->jsmdb->Quote('' . 'club' . ''));
		
	try{
	   $this->jsmdb->setQuery($this->jsmquery);
		$clubfieldvalue = $this->jsmdb->loadResult();
        return $clubfieldvalue;
        }
		catch (Exception $e)
		{
        $this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
        $this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
        return false;
		}	
		
	}
		
	}
	
	
	/**
	 * Method to update checked clubs
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function saveshort()
	{
		$app    = Factory::getApplication();
        $date = Factory::getDate();
		$user = Factory::getUser();
		$option = Factory::getApplication()->input->getCmd('option');
		$pks  = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
		$post = Factory::getApplication()->input->post->getArray(array());

		$result = true;

		for ($x = 0; $x < count($pks); $x++)
		{
			$address_parts  = array();
			$address_parts2 = array();
			$tblClub        = &$this->getTable();

			$tblClub->id       = $pks[$x];
			$tblClub->zipcode  = $post['zipcode' . $pks[$x]];
			$tblClub->location = $post['location' . $pks[$x]];
			$tblClub->address  = $post['address' . $pks[$x]];
			$tblClub->country  = $post['country' . $pks[$x]];
            $tblClub->founded_year  = $post['founded_year' . $pks[$x]];

			$tblClub->unique_id   = $post['unique_id' . $pks[$x]];
			$tblClub->new_club_id = $post['new_club_id' . $pks[$x]];
			$tblClub->name = $post['club_name' . $pks[$x]];
			$tblClub->alias = OutputFilter::stringURLSafe($tblClub->name);

            $tblClub->modified    = $date->toSql();
			$tblClub->modified_by = $user->get('id');

			if (!empty($tblClub->address))
			{
				$address_parts[] = $tblClub->address;
			}

			if (!empty($tblClub->location))
			{
				if (!empty($tblClub->zipcode))
				{
					$address_parts[]  = $tblClub->zipcode . ' ' . $tblClub->location;
					$address_parts2[] = $tblClub->zipcode . ' ' . $tblClub->location;
				}
				else
				{
					$address_parts[]  = $tblClub->location;
					$address_parts2[] = $tblClub->location;
				}
			}

			if (!empty($tblClub->country))
			{
				$address_parts[]  = JSMCountries::getShortCountryName($tblClub->country);
				$address_parts2[] = JSMCountries::getShortCountryName($tblClub->country);
			}

			$address = implode(', ', $address_parts);
			$coords  = sportsmanagementHelper::resolveLocation($address);

			if ($coords)
			{
				$tblClub->latitude  = $coords['latitude'];
				$tblClub->longitude = $coords['longitude'];
			}
			else
			{
			}

			if (!$tblClub->store())
			{
				$result = false;
			}
		}

		return $result;
	}


	/**
	 * sportsmanagementModelclub::teamsofclub()
	 *
	 * @param   mixed  $club_id
	 *
	 * @return void
	 */
	function teamsofclub($club_id)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('t.id,t.name,t.club_id,t.short_name');
		$this->jsmquery->from('#__sportsmanagement_team AS t');
		$this->jsmquery->where('t.club_id = ' . $club_id);
		$this->jsmdb->setQuery($this->jsmquery);
        try{
		$teamsofclub = $this->jsmdb->loadObjectList();
        return $teamsofclub;
        }
		catch (Exception $e)
		{
        $this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
        $this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
        return false;
		}

	}


}
