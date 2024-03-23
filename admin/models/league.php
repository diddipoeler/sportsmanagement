<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       league.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelleague
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelleague extends JSMModelAdmin
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
 * sportsmanagementModelleague::getlogohistoryLeague()
 * 
 * @param integer $league_id
 * @param integer $season_id
 * @param bool $logoonly
 * @return
 */
function getlogohistoryLeague($league_id = 0, $season_id = 0, $logoonly = false )
	{
$app    = Factory::getApplication();
$db    = Factory::getDbo();
$query = $db->getQuery(true);
$result    = array();

$query->select('cl.*,se.name as seasonname');
$query->from('#__sportsmanagement_league_logos as cl');
$query->join('INNER', '#__sportsmanagement_season AS se ON se.id = cl.season_id');

if ( $league_id )
{		
$query->where('cl.league_id = ' . $league_id);
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
	 * Method to update checked leagues
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function saveshort()
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Get the input
		$pks    = $jinput->getVar('cid', null, 'post', 'array');
		$post   = Factory::getApplication()->input->post->getArray(array());
		$result = true;

		for ($x = 0; $x < count($pks); $x++)
		{
			$tblLeague                       = &$this->getTable();
			$tblLeague->id                   = $pks[$x];
			$tblLeague->associations         = $post['association' . $pks[$x]];
			$tblLeague->country              = $post['country' . $pks[$x]];
			$tblLeague->agegroup_id          = $post['agegroup' . $pks[$x]];
			$tblLeague->published_act_season = $post['published_act_season' . $pks[$x]];
            $tblLeague->champions_complete = $post['champions_complete' . $pks[$x]];

			if (!$tblLeague->store())
			{
				$result = false;
			}
		}

		return $result;
	}


}
