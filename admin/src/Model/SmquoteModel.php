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
     * Updates quotes by the same author using bound database parameters
     * when a picture is supplied, then runs the parent save hook.
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
                Text::plural('COM_SPORTSMANAGEMENT_N_ITEMS_CREATED', $id),
                'message'
            );
        }

        $author = trim((string) ($data['author'] ?? ''));

        if ($author !== '' && array_key_exists('picture', $data)) {
            $db = $this->getDatabase();
            $picture = (string) $data['picture'];
            $query = $db->createQuery()
                ->update($db->quoteName('#__sportsmanagement_rquote'))
                ->set($db->quoteName('picture') . ' = :quotePicture')
                ->where($db->quoteName('author') . ' = :quoteAuthor')
                ->bind(':quotePicture', $picture, ParameterType::STRING)
                ->bind(':quoteAuthor', $author, ParameterType::STRING);

            try {
                $db->setQuery($query)->execute();
                self::$db_num_rows = (int) $db->getAffectedRows();
            } catch (\Throwable $e) {
                $app->enqueueMessage($e->getMessage(), 'error');
            }
        }

        parent::afterSportsManagementSave($data, $id, $isNew);
    }
}
