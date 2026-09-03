<?php
/**
 * Joomla 5/6 base form model for SportsManagement administrator models.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ActionLogHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Native Joomla 5/6 base model for SportsManagement administrator forms.
 *
 * It keeps the component-specific database selection and save metadata while
 * relying on Joomla's current AdminModel flow.
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

    /** Resolve the active Joomla administrator application. */
    protected function administratorApplication(): AdministratorApplication
    {
        $app = Factory::getApplication();

        if (!$app instanceof AdministratorApplication) {
            throw new \RuntimeException('SportsManagement administrator application is unavailable.');
        }

        return $app;
    }

    public function setDatabase(DatabaseInterface $db): void
    {
        try {
            $app = $this->administratorApplication();
            $input = $app->getInput();
            $selector = $input->getInt(
                'cfg_which_database',
                (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
            );
            parent::setDatabase((new SportsManagementDatabaseResolver())->resolve($selector, $db));
        } catch (\Throwable) {
            // Model construction must remain usable even if external DB resolution fails.
            parent::setDatabase($db);
        }
    }

    public function getForm($data = [], $loadData = true)
    {
        FormHelper::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        FormHelper::addFieldPrefix('Diddipoeler\\Component\\SportsManagement\\Administrator\\Field');

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
        $data = $this->administratorApplication()->getUserState(
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
        $app = $this->administratorApplication();
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

        try {
            $this->afterSportsManagementSave($data, $id, $isNew);
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return false;
        }

        try {
            ActionLogHelper::record(
                $user,
                $data,
                $isNew,
                $input->getCmd('view', 'cpanel')
            );
        } catch (\Throwable) {
            // Action logging must not turn a successful entity save into a failure.
        }

        return true;
    }

    protected function prepareSportsManagementData(array $data): array
    {
        $post = $this->administratorApplication()->getInput()->post->getArray();

        foreach (['extended', 'extendeduser'] as $field) {
            if (!isset($post[$field]) || !is_array($post[$field])) {
                continue;
            }

            $registry = new Registry();
            $registry->loadArray($post[$field]);
            $data[$field] = $registry->toString();
        }

        return $data;
    }

    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
    }
}
