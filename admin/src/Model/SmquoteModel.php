<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

/**
 * Native Joomla 5/6 administrator form model for SportsManagement quotes.
 */
final class SmquoteModel extends SportsManagementAdminModel
{
    public static int $db_num_rows = 0;

    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        return $this->loadForm(
            'com_sportsmanagement.smquote',
            'smquote',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    public function getTable($type = 'smquote', $prefix = 'sportsmanagementTable', $config = [])
    {
        $config['dbo'] = $this->getDatabase();

        return Table::getInstance($type, $prefix, $config);
    }

    protected function prepareSportsManagementData(array $data): array
    {
        $extended = Factory::getApplication()->getInput()->post->get('extended', [], 'array');

        if ($extended) {
            $registry = new Registry();
            $registry->loadArray($extended);
            $data['extended'] = (string) $registry;
        }

        return $data;
    }

    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
        $app = Factory::getApplication();

        if ($isNew) {
            $app->enqueueMessage(
                Text::plural('COM_SPORTSMANAGEMENT_N_ITEMS_CREATED', $id),
                'message'
            );
        }

        $author = trim((string) ($data['author'] ?? ''));

        if ($author === '' || !array_key_exists('picture', $data)) {
            return;
        }

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
}
