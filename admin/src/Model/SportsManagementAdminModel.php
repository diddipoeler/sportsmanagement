<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Database\DatabaseInterface;

/**
 * Native Joomla 5/6 base model for SportsManagement administrator forms.
 *
 * It keeps the component-specific database connection and the save metadata
 * used by the legacy model while relying on Joomla's current AdminModel flow.
 */
abstract class SportsManagementAdminModel extends AdminModel
{
    public function __construct(
        $config = [],
        ?MVCFactoryInterface $factory = null,
        ?FormFactoryInterface $formFactory = null
    ) {
        parent::__construct($config, $factory, $formFactory);
    }

    public function setDatabase(DatabaseInterface $db): void
    {
        if (!class_exists('sportsmanagementHelper')) {
            $helperFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';

            if (is_file($helperFile)) {
                require_once $helperFile;
            }
        }

        try {
            if (class_exists('sportsmanagementHelper')) {
                $sportsManagementDb = \sportsmanagementHelper::getDBConnection();

                if ($sportsManagementDb instanceof DatabaseInterface) {
                    parent::setDatabase($sportsManagementDb);

                    return;
                }
            }
        } catch (\Throwable) {
            // Fall back to Joomla's injected database connection.
        }

        parent::setDatabase($db);
    }

    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');

        $name = strtolower($this->getName());

        return $this->loadForm(
            'com_sportsmanagement.' . $name,
            $name,
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    protected function loadFormData()
    {
        $name = strtolower($this->getName());
        $data = Factory::getApplication()->getUserState(
            'com_sportsmanagement.edit.' . $name . '.data',
            []
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function save($data)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $user = $app->getIdentity();
        $db = $this->getDatabase();

        $data['modified'] = Factory::getDate()->toSql();
        $data['modified_by'] = (int) $user->id;
        $data['checked_out'] = 0;
        $data['checked_out_time'] = $db->getNullDate();

        $task = strtolower($input->getCmd('task', ''));

        if ($task === 'save2copy' || str_ends_with($task, '.save2copy')) {
            $table = $this->getTable();
            $sourceId = (int) ($data['id'] ?? $input->getInt('id'));

            if ($sourceId > 0 && $table->load($sourceId)) {
                $data['id'] = 0;

                if (isset($data['name']) && isset($table->name) && (string) $data['name'] === (string) $table->name) {
                    $data['name'] .= ' ' . Text::_('JGLOBAL_COPY');

                    if (array_key_exists('alias', $data)) {
                        $data['alias'] = OutputFilter::stringURLSafe($data['name']);
                    }
                }
            }
        }

        $data = $this->prepareSportsManagementData($data);

        if (!parent::save($data)) {
            return false;
        }

        $id = (int) $this->getState($this->getName() . '.id');
        $isNew = (bool) $this->getState($this->getName() . '.new');
        $data['id'] = $id;

        $input->set('insert_id', $id);

        if (class_exists('sportsmanagementHelper') && method_exists('sportsmanagementHelper', 'recordActionLog')) {
            try {
                \sportsmanagementHelper::recordActionLog($user, $data, $isNew ? 0 : $id);
            } catch (\Throwable) {
                // Action logging must not turn a successful entity save into a failure.
            }
        }

        $this->afterSportsManagementSave($data, $id, $isNew);

        return true;
    }

    protected function prepareSportsManagementData(array $data): array
    {
        return $data;
    }

    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
    }
}
