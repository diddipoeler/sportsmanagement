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

/**
 * Native Joomla 5/6 club-name form model.
 */
final class ClubnameModel extends SportsManagementAdminModel
{
    /**
     * Import bundled club-name aliases.
     */
    public function import(): void
    {
        $app = $this->administratorApplication();
        $db = $this->getDatabase();
        $file = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/xml_files/clubnames.xml';
        $xml = @simplexml_load_file($file);

        if ($xml === false) {
            $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', $file, __LINE__), 'error');

            return;
        }

        foreach ($xml->children() as $quote) {
            $country = (string) $quote->clubname->attributes()->country;
            $name = (string) $quote->clubname->attributes()->name;
            $clubname = trim((string) $quote->clubname);

            if ($country === '' || $name === '' || $clubname === '') {
                continue;
            }

            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_club_names'))
                ->where($db->quoteName('country') . ' = ' . $db->quote($country))
                ->where($db->quoteName('name') . ' = ' . $db->quote($name));

            try {
                $db->setQuery($query);

                if ($db->loadResult()) {
                    continue;
                }

                $query = $db->getQuery(true)
                    ->insert($db->quoteName('#__sportsmanagement_club_names'))
                    ->columns([
                        $db->quoteName('country'),
                        $db->quoteName('name'),
                        $db->quoteName('name_long'),
                    ])
                    ->values(implode(',', [
                        $db->quote($country),
                        $db->quote($name),
                        $db->quote($clubname),
                    ]));

                $db->setQuery($query);
                $db->execute();
            } catch (\RuntimeException $e) {
                $app->enqueueMessage(
                    Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                    'error'
                );
            }
        }
    }
}
