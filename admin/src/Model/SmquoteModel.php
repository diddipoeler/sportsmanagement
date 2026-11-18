<?php
/**
 * Joomla 5/6 administrator quote form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\SmquoteTable;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Native Joomla 5/6 administrator form model for SportsManagement quotes.
 */
final class SmquoteModel extends SportsManagementAdminModel
{
    public static int $db_num_rows = 0;

    /**
     * Resolve the Joomla administrator quote table.
     * @param string $type Requested quote table type.
     * @param string $prefix Legacy Joomla table prefix.
     * @param array $config Optional table creation options.
     * @return mixed Native SmquoteTable or parent table.
     */
    public function getTable($type = 'smquote', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'smquote') === 0) {
            return new SmquoteTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Serialise submitted extended quote fields before storage.
     * @param array $data Submitted quote data.
     * @return array Normalised data for the parent model.
     */
    protected function prepareSportsManagementData(array $data): array
    {
        $post = $this->administratorApplication()->getInput()->post->getArray();

        if (array_key_exists('extended', $post) && is_array($post['extended'])) {
            $registry = new Registry();
            $registry->loadArray($post['extended']);
            $data['extended'] = (string) $registry;
        }

        return parent::prepareSportsManagementData($data);
    }

    /**
     * Notify administrators after saving and synchronise author images.
     *
     * Uses one newly created record for the plural translation rather than
     * the generated primary-key number. Validates author and image text before
     * the Joomla parameter-bound bulk update, keeping malformed imported
     * arrays out of SQL. Optional synchronisation failures are logged without
     * exposing private SQL details in the administrator message, and the
     * original quote remains saved through the normal Joomla model flow.
     * @param array $data Saved quote data.
     * @param int $id Saved quote identifier.
     * @param bool $isNew Whether a new record was inserted.
     * @return void
     */
    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
        $app = $this->administratorApplication();

        if ($isNew) {
            $app->enqueueMessage(
                Text::plural('COM_SPORTSMANAGEMENT_N_ITEMS_CREATED', 1),
                'message'
            );
        }

        $authorValue = $data['author'] ?? null;
        $pictureValue = $data['picture'] ?? null;

        if (!is_string($authorValue) || !is_string($pictureValue)) {
            parent::afterSportsManagementSave($data, $id, $isNew);
            return;
        }

        $author = trim($authorValue);
        $picture = $pictureValue;

        // Empty picture names still clear a previously saved author image,
        // while malformed imported values cannot reach a public media field.
        if ($author === '' || strlen($author) > 255 || strlen($picture) > 2048
            || preg_match('/[\\x00-\\x1F\\x7F]/', $author) === 1
            || preg_match('/[\\x00-\\x1F\\x7F]/', $picture) === 1) {
            parent::afterSportsManagementSave($data, $id, $isNew);
            return;
        }

        try {
            $db = $this->getDatabase();
            $query = $db->createQuery()
                ->update($db->quoteName('#__sportsmanagement_rquote'))
                ->set($db->quoteName('picture') . ' = :quotePicture')
                ->where($db->quoteName('author') . ' = :quoteAuthor')
                ->bind(':quotePicture', $picture, ParameterType::STRING)
                ->bind(':quoteAuthor', $author, ParameterType::STRING);

            $db->setQuery($query)->execute();
            $affected = $db->getAffectedRows();
            self::$db_num_rows = is_int($affected) && $affected >= 0 ? $affected : 0;
        } catch (\Throwable $error) {
            Log::add(
                __METHOD__ . ': quote image synchronisation failed (' . get_class($error) . ').',
                Log::ERROR,
                'com_sportsmanagement'
            );
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'warning');
        }

        parent::afterSportsManagementSave($data, $id, $isNew);
    }
}
