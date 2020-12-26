<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage projectreferee
 * @file       projectreferee.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;


/**
 * sportsmanagementModelprojectreferee
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2020
 * @version $Id$
 * @access public
 */
class sportsmanagementModelprojectreferee extends JSMModelAdmin
{

	/**
	 * Method to update checked project referees
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function saveshort()
	{
		$pks  = $this->jsmjinput->getVar('cid', null, 'post', 'array');
		$post = $this->jsmjinput->post->getArray(array());

		$result = true;

		for ($x = 0; $x < count($pks); $x++)
		{
			$tblPerson                      = new stdClass;
			$tblPerson->id                  = $pks[$x];
			$tblPerson->project_position_id = $post['project_position_id' . $pks[$x]];
            $tblPerson->modified    = $this->jsmdate->toSql();
			$tblPerson->modified_by = $this->jsmuser->get('id');
            
            try
			{
			$result = $this->jsmdb->updateObject('#__sportsmanagement_project_referee', $tblPerson, 'id');
    		}
			catch (Exception $e)
			{
			}

		}

		return $result;
	}

	/**
	 * Method to remove projectreferee
	 *
	 * @access public
	 * @return boolean    True on success
	 * @since  0.1
	 */
	public function delete(&$pks)
	{
		$app = Factory::getApplication();
		/**
		 *
		 * Ein Datenbankobjekt beziehen
		 */
		$db = Factory::getDbo();
		/**
		 *
		 * Ein JDatabaseQuery Objekt beziehen
		 */
		$query = $db->getQuery(true);

		$result = false;

		if (count($pks))
		{
			$cids = implode(',', $pks);

			// Wir löschen mit join
			$query = 'DELETE mre
            FROM #__sportsmanagement_project_referee as m  
            LEFT JOIN #__sportsmanagement_match_referee as mre
            ON mre.project_referee_id = m.id
            WHERE m.id IN (' . $cids . ')';
			$db->setQuery($query);
			$db->execute();

			if (!$db->execute())
			{
				return false;
			}
		}

		return parent::delete($pks);
	}


}
