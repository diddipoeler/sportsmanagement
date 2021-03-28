<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       clubname.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
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

if (version_compare(JVERSION, '3.0.0', 'ge'))
{
$xml = simplexml_load_file(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/xml_files/clubnames.xml');
}
else
{
$xml = Factory::getXML(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/xml_files/clubnames.xml', true);
}

		foreach ($xml->children() as $quote)
		{
			$country  = (string) $quote->clubname->attributes()->country;
			$name     = (string) $quote->clubname->attributes()->name;
			$clubname = (string) $quote->clubname;

			$this->jsmquery->clear();
			$this->jsmquery->select('id');
			$this->jsmquery->from('#__sportsmanagement_club_names');
			$this->jsmquery->where('country LIKE ' . $db->Quote('' . $country . ''));
			$this->jsmquery->where('name LIKE ' . $db->Quote('' . $name . ''));
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->loadResult();

			if (!$result)
			{
				$insertquery = $this->jsmdb->getQuery(true);
				$columns = array('country', 'name', 'name_long');
				$values = array('\'' . $country . '\'', '\'' . $name . '\'', '\'' . $clubname . '\'');
				$insertquery
					->insert($this->jsmdb->quoteName('#__sportsmanagement_club_names'))
					->columns($this->jsmdb->quoteName($columns))
					->values(implode(',', $values));
				$this->jsmdb->setQuery($insertquery);
         try{       
            $this->jsmdb->execute();
                }
		catch (Exception $e)
		{
        $this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
        $this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
		}

			}
		}

	}

}
