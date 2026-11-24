<?php
/**
 * Joomla 5/6 administrator model for club-name aliases.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

/**
 * Native Joomla 5/6 club-name form model.
 */
final class ClubnameModel extends SportsManagementAdminModel
{
    /**
     * Import packaged alternative club names into the administrator database.
     *
     * Read the bundled SportsManagement XML file, skip records lacking
     * country, club name or long name, and check for an existing alias
     * using bound Joomla query parameters before inserting a new row.
     * Require component edit permission even for direct model calls,
     * then report malformed input files and database errors to the backend
     * message queue while allowing remaining valid aliases to import.
     *
     * @return void Existing aliases are left unchanged.
     * @throws \RuntimeException If the administrator cannot edit component data.
     */
    public function import(): void
    {
        $app = $this->administratorApplication();
        $identity = $app->getIdentity();

        // Imported aliases modify component tables. Protect direct model
        // calls as well as the controller task, which checks its POST token.
        if ($identity === null || !$identity->authorise('core.edit', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $db = $this->getDatabase();
        $file = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/xml_files/clubnames.xml';
        $xml = @simplexml_load_file($file, \SimpleXMLElement::class, LIBXML_NONET);

        if ($xml === false) {
            \Joomla\CMS\Log\Log::add(
                'SportsManagement clubname import XML file could not be loaded: ' . $file,
                \Joomla\CMS\Log\Log::ERROR,
                'com_sportsmanagement'
            );
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

            return;
        }

        foreach ($xml->children() as $quote) {
            $country = (string) $quote->clubname->attributes()->country;
            $name = (string) $quote->clubname->attributes()->name;
            $clubname = trim((string) $quote->clubname);

            if ($country === '' || $name === '' || $clubname === '') {
                continue;
            }

            $query = $db->createQuery()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_club_names'))
                ->where($db->quoteName('country') . ' = :country')
                ->where($db->quoteName('name') . ' = :name')
                ->bind(':country', $country, ParameterType::STRING)
                ->bind(':name', $name, ParameterType::STRING);

            try {
                $db->setQuery($query);

                if ($db->loadResult()) {
                    continue;
                }

                $insertCountry = $country;
                $insertName = $name;
                $insertLongName = $clubname;
                $query = $db->createQuery()
                    ->insert($db->quoteName('#__sportsmanagement_club_names'))
                    ->columns([
                        $db->quoteName('country'),
                        $db->quoteName('name'),
                        $db->quoteName('name_long'),
                    ])
                    ->values(':insertCountry, :insertName, :insertLongName')
                    ->bind(':insertCountry', $insertCountry, ParameterType::STRING)
                    ->bind(':insertName', $insertName, ParameterType::STRING)
                    ->bind(':insertLongName', $insertLongName, ParameterType::STRING);

                $db->setQuery($query);
                $db->execute();
            } catch (\RuntimeException $e) {
                \Joomla\CMS\Log\Log::add(
                    'SportsManagement clubname import database query failed: ' . $e->getMessage(),
                    \Joomla\CMS\Log\Log::ERROR,
                    'com_sportsmanagement'
                );
                $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            }
        }
    }
}
