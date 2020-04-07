<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       clubname.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

/**
 * sportsmanagementModelclubname
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelclubname extends JSMModelAdmin
{

	/**
	 * sportsmanagementModelclubname::import()
	 *
	 * @return void
	 */
	public function import()
	{
		// Reference global application object
		$app = Factory::getApplication();

		// Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		$option = Factory::getApplication()->input->getCmd('option');

		// JInput object
		$jinput = $app->input;

		$xml = Factory::getXML(JPATH_ADMINISTRATOR . '/components/' . $option . '/helpers/xml_files/clubnames.xml', true);

		foreach ($xml->children() as $quote)
		{
						 $country = (string) $quote->clubname->attributes()->country;
			 $name = (string) $quote->clubname->attributes()->name;
			 $clubname = (string) $quote->clubname;

			$query->clear();
			$query->select('id');
			$query->from('#__sportsmanagement_club_names');
			$query->where('country LIKE ' . $db->Quote('' . $country . ''));
			$query->where('name LIKE ' . $db->Quote('' . $name . ''));
			$db->setQuery($query);

			$result = $db->loadResult();

			if (!$result)
			{
					 $insertquery = $db->getQuery(true);

					 // Insert columns.
					 $columns = array('country','name','name_long');

					 // Insert values.
					 $values = array('\'' . $country . '\'','\'' . $name . '\'','\'' . $clubname . '\'');

					 // Prepare the insert query.
					 $insertquery
						 ->insert($db->quoteName('#__sportsmanagement_club_names'))
						 ->columns($db->quoteName($columns))
						 ->values(implode(',', $values));

					 // Set the query using our newly populated query object and execute it.
					 $db->setQuery($insertquery);

					 sportsmanagementModeldatabasetool::runJoomlaQuery();
			}
		}

	}

}
