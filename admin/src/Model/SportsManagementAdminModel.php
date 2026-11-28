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
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Application\AdministratorApplication;
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
    /**
     * Initialise the SportsManagement administrator form-model base.
     * @param array $config Joomla admin model configuration.
     * @param MVCFactoryInterface|null $factory Optional MVC factory.
     * @param FormFactoryInterface|null $formFactory Optional Joomla form factory.
     */
    public function __construct(
        $config = [],
        ?MVCFactoryInterface $factory = null,
        ?FormFactoryInterface $formFactory = null
    ) {
        parent::__construct($config, $factory, $formFactory);
    }

    /**
     * Resolve the active Joomla administrator application for form models.
     * Delegates to the shared runtime service so form data, identity and
     * database preferences are read from the correct Joomla client.
     * @return AdministratorApplication Active administrator application.
     * @throws \RuntimeException If the application cannot be resolved.
     */
    protected function administratorApplication(): AdministratorApplication
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    /**
     * Bind the administrator form model to its selected component database.
     *
     * Uses exactly the same two supported database modes as the administrator
     * form controller. Invalid submitted or stored choices fail closed before
     * opening an external connection. A missing application during Joomla's
     * early model construction and a failed connection resolution continue
     * to fall back to the injected Joomla database.
     *
     * @param DatabaseInterface $db Joomla's default database connection.
     * @return void
     * @throws \InvalidArgumentException For malformed database preferences.
     */
    public function setDatabase(DatabaseInterface $db): void
    {
        try {
            $app = $this->administratorApplication();
            $input = $app->getInput();
            $requested = $input->get('cfg_which_database', null, 'raw');
            $value = $requested ?? $app->getUserState(
                'com_sportsmanagement.cfg_which_database',
                0
            );
        } catch (\Throwable) {
            // Joomla may create the model before the administrator application
            // is available. Keep the injected database in that case.
            parent::setDatabase($db);
            return;
        }

        $selector = self::databaseSelector($value);

        if ($selector === null) {
            throw new \InvalidArgumentException('Invalid SportsManagement model database selector.', 400);
        }

        try {
            parent::setDatabase((new SportsManagementDatabaseResolver())->resolve($selector, $db));
        } catch (\Throwable) {
            // Recoverable external connection problems still use Joomla's DB.
            parent::setDatabase($db);
        }
    }

    /**
     * Adopt the already resolved connection of a trusted parent model.
     *
     * Internal callers must pass their active database object, not a request
     * parameter or a new connection. This preserves transaction identity
     * without resolving the external database configuration a second time.
     *
     * @param DatabaseInterface $db Parent model's active connection.
     * @return void
     */
    final public function useResolvedDatabase(DatabaseInterface $db): void
    {
        parent::setDatabase($db);
    }

    /**
     * Construct a native table whose constructor supports resolved connections.
     *
     * Internal callers supply a known component table class. Reuses this model's
     * exact database driver and propagates Joomla 6's exception mode when present.
     *
     * @param class-string<\Diddipoeler\Component\SportsManagement\Administrator\Table\SportsManagementTable> $tableClass Native table class.
     * @return \Diddipoeler\Component\SportsManagement\Administrator\Table\SportsManagementTable Initialised native table.
     */
    protected function createResolvedTable(string $tableClass): \Diddipoeler\Component\SportsManagement\Administrator\Table\SportsManagementTable
    {
        $table = new $tableClass($this->getDatabase(), true);

        if (method_exists($this, 'shouldUseExceptions') && $this->shouldUseExceptions()) {
            $table->setUseExceptions(true);
        }

        return $table;
    }

    /**
     * Validate the database preference without Joomla's lossy getInt().
     *
     * Accept zero and one, as well as zero-padded legacy radio-field values.
     * Reject structured data, booleans, unsupported modes, whitespace and
     * overflowing input before any component database resolver is called.
     *
     * @param mixed $value Raw request or administrator user-state selection.
     * @return int|null Supported 0/1 database mode, or null for invalid input.
     */
    private static function databaseSelector(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value === 0 || $value === 1 ? $value : null;
        }

        if (!is_string($value) || strlen($value) > 64
            || preg_match('/^0*([01])$/D', $value, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * Load the native Joomla form and SportsManagement custom fields.
     * @param array $data Existing form values.
     * @param bool $loadData Whether to load persisted values.
     * @return mixed Joomla form object or failure result.
     */
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

    /**
     * Load pending administrator form data or the stored item.
     * @return mixed Current form data.
     */
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

    /**
     * Persist a SportsManagement administrator record with audit fields.
     * Rejects non-array submissions before preparing persistence metadata.
     * Requires an authenticated administrator identity, supports copying
     * an existing record and invokes preparation and post-save hooks.
     * @param array $data Submitted form model values.
     * @return bool Whether the record was saved.
     */
    public function save($data)
    {
        if (!is_array($data)) {
            $this->setError(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));

            return false;
        }

        $app = $this->administratorApplication();
        $input = $app->getInput();
        $user = $app->getIdentity();

        if ($user === null || (int) $user->id <= 0) {
            $this->setError(Text::_('JERROR_ALERTNOAUTHOR'));

            return false;
        }

        $db = $this->getDatabase();

        $data['modified'] = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
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

    /**
     * Convert extended form arrays into serialised registry fields.
     * @param array $data Submitted admin form values.
     * @return array Prepared values.
     */
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

    /**
     * Handle an optional post-save hook in derived admin models.
     * @param array $data Saved form values.
     * @param int $id Saved record identifier.
     * @param bool $isNew Whether a new record was inserted.
     * @return void
     */
    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
    }
}
