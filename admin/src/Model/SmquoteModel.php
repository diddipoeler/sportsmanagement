<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\SmquoteTable;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Native Joomla 5/6 administrator form model for SportsManagement quotes.
 */
final class SmquoteModel extends SportsManagementAdminModel
{
    public static int $db_num_rows = 0;

    public function getTable($type = 'smquote', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'smquote') === 0) {
            return new SmquoteTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

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
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__sportsmanagement_rquote'))
                ->set($db->quoteName('picture') . ' = ' . $db->quote((string) $data['picture']))
                ->where($db->quoteName('author') . ' = ' . $db->quote($author));

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
