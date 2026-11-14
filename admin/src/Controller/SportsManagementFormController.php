<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Joomla 5/6 migration.
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\PlayerPersistenceService;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;
use Joomla\Input\Input;

/**
 * Shared SportsManagement form controller for Joomla 5/6.
 *
 * This class preserves the component-specific save and redirect behaviour from
 * the former JSMControllerForm while keeping Joomla's injected MVCFactory,
 * application, input and form factory intact.
 */
class SportsManagementFormController extends FormController
{
    protected $jsmdb;
    protected $jsmapp;
    protected $jsmjinput;
    protected $jsmoption = 'com_sportsmanagement';
    protected $jsmdocument;
    protected $jsmuser;
    protected $jsmdate;
    protected $team_club_id = 0;
    protected $club_id = 0;
    protected $person_id = 0;
    protected $team_id = 0;
    protected $insert_id = 0;

    /**
     * Initialise the Joomla MVC form controller and legacy component context.
     *
     * Preserves injected MVC factory, application, input and form factory,
     * then resolves the selected SportsManagement database connection and
     * exposes the legacy properties required by existing edit controllers.
     * Rejects malformed database selectors before an external connection can
     * be chosen and preserves zero-padded legacy radio-button values.
     *
     * @param array $config Joomla controller configuration.
     * @param MVCFactoryInterface|null $factory Optional injected MVC factory.
     * @param CMSWebApplicationInterface|null $app Optional Joomla application.
     * @param Input|null $input Optional injected request input.
     * @param FormFactoryInterface|null $formFactory Optional form factory.
     * @throws \RuntimeException If Joomla's database service is invalid.
     * @throws \InvalidArgumentException For a malformed database selector.
     */
    public function __construct(
        $config = [],
        ?MVCFactoryInterface $factory = null,
        ?CMSWebApplicationInterface $app = null,
        ?Input $input = null,
        ?FormFactoryInterface $formFactory = null
    ) {
        parent::__construct($config, $factory, $app, $input, $formFactory);

        $this->jsmapp = $this->app;
        $this->jsmjinput = $this->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option', 'com_sportsmanagement');

        $joomlaDatabase = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);

        if (!$joomlaDatabase instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement form database service is unavailable.', 500);
        }
        // Joomla's getInt() coerces malformed request values (such as "1junk")
        // into a valid database choice. Validate the raw request or stored
        // preference before resolving an external component connection.
        $requestedSelector = $this->jsmjinput->get('cfg_which_database', null, 'raw');
        $rawSelector = $requestedSelector ?? $this->jsmapp->getUserState(
            $this->jsmoption . '.cfg_which_database',
            0
        );
        $databaseSelector = self::databaseSelector($rawSelector);

        if ($databaseSelector === null) {
            throw new \InvalidArgumentException('Invalid SportsManagement form database selector.', 400);
        }

        $this->jsmdb = SportsManagementDatabaseResolver::resolve($joomlaDatabase, $databaseSelector);

        $this->team_club_id = (int) $this->jsmapp->getUserState($this->jsmoption . '.club_id', 0);
        $this->jsmdocument = $this->jsmapp->getDocument();
        $this->jsmuser = $this->jsmapp->getIdentity();
        $this->jsmdate = Factory::getDate();
    }

    /**
     * Validate the database selection supplied to a Joomla administrator form.
     *
     * Accepts the two supported component database choices, including
     * zero-padded legacy strings stored by older Joomla radio fields.
     * Rejects booleans, arrays, whitespace, negative values and strings
     * with junk suffixes so invalid input cannot silently select an
     * unrelated database when the club, team or project form is opened.
     *
     * @param mixed $value Unfiltered request value or saved administrator state.
     * @return int|null Database selector zero/one, or null if malformed.
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
     * Redirect unsupported imports back to the administrator entity list.
     *
     * Provides translated feedback for forms without a dedicated import task.
     *
     * @return void
     */
    public function import(): void
    {
        $message = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_IMPORT');
        $this->setRedirect(
            Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false),
            $message
        );
    }

    /**
     * Redirect unsupported exports back to the administrator entity list.
     *
     * Provides translated feedback for forms without a dedicated exporter.
     *
     * @return void
     */
    public function export(): void
    {
        $message = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_EXPORT');
        $this->setRedirect(
            Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false),
            $message
        );
    }

    /**
     * Close the modal component edit dialog without modifying records.
     *
     * @param string|null $key Optional legacy form record key.
     * @return void
     */
    public function cancelmodal($key = null): void
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }

    /**
     * Save an administrator form using the legacy SportsManagement workflow.
     *
     * Checks the Joomla CSRF token, handles round and player-specific
     * persistence and preserves the historical save/apply/close redirects.
     *
     * @param string|null $key Optional primary record key name.
     * @param string|null $urlVar Optional record identifier URL variable.
     * @return bool Whether the record was saved successfully.
     */
    public function save($key = null, $urlVar = null)
    {
        $this->checkToken();
        $urlVar = $urlVar ?: 'id';

        $post = $this->jsmjinput->post->getArray();
        $tmpl = $this->jsmjinput->getCmd('tmpl');
        $model = $this->getModel($this->view_item, 'Administrator');
        $data = $this->jsmjinput->post->get('jform', [], 'array');
        $setRedirect = '';
        $createTeam = $this->jsmjinput->getBool('createTeam');
        $playerPersistence = null;

        // Round dates are nullable since schema 4.24.02. Do not synthesize
        // legacy zero dates for an empty form submission: let Joomla's
        // validation reject incomplete data and preserve existing records.

        // Joomla's FormController::save() normally applies allowSave() before
        // calling the model. This compatibility override must keep that ACL
        // gate for both new records and edits, including custom form tasks.
        if (!$this->allowSave($data, $key ?: 'id')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        if ($this->view_item === 'player') {
            try {
                $playerPersistence = new PlayerPersistenceService($this->jsmdb);
                $data = $playerPersistence->prepare($data);
            } catch (\Throwable $e) {
                $id = (int) ($data['id'] ?? $this->jsmjinput->getInt('id'));
                $this->setRedirect(
                    Route::_('index.php?option=com_sportsmanagement&view=player&layout=edit&id=' . $id, false),
                    $e->getMessage(),
                    'error'
                );
                return false;
            }
        }

        $return = $model->save($data);
        $modelError = $model->getError();

        if ($modelError) {
            $this->jsmapp->enqueueMessage($modelError, 'error');
        }

        $this->club_id = (int) $this->jsmapp->getUserState($this->jsmoption . '.club_id', 0);
        $this->person_id = (int) $this->jsmapp->getUserState($this->jsmoption . '.person_id', 0);
        $this->team_id = (int) $this->jsmapp->getUserState($this->jsmoption . '.team_id', 0);
        $this->insert_id = $this->jsmjinput->getInt('insert_id');

        $id = $this->insert_id ?: (int) ($data['id'] ?? 0);

        if (empty($data['id'])) {
            $id = $this->jsmjinput->getInt('insert_id');
        }

        if ($return && $playerPersistence instanceof PlayerPersistenceService) {
            try {
                $playerPersistence->afterSave($id, $post);
                $this->person_id = $id;
            } catch (\Throwable $e) {
                $return = false;
                $modelError = $e->getMessage();
                $this->jsmapp->enqueueMessage($modelError, 'error');
            }
        }

        if (!$return) {
            $message = $modelError ?: Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED');
            $this->setRedirect(
                Route::_(
                    'index.php?option=' . $this->option . '&view=' . $this->view_item
                    . $this->getRedirectToItemAppend($id, $urlVar) . $setRedirect,
                    false
                ),
                $message,
                'error'
            );

            return false;
        }

        switch ($this->view_item) {
            case 'club':
                if ($createTeam) {
                    $teamModel = $this->getModel('Team', 'Administrator', ['ignore_request' => true]);
                    $teamName = (string) ($data['name'] ?? '');
                    $teamShortName = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $teamName), 0, 3));

                    if ($teamModel) {
                        $teamModel->save([
                            'id' => 0,
                            'name' => $teamName,
                            'short_name' => $teamShortName,
                            'club_id' => $this->club_id,
                        ]);
                    }
                }
                break;

            case 'rounds':
                $setRedirect = '&pid=' . (int) ($post['pid'] ?? 0);
                break;

            case 'projects':
                $setRedirect = '&pid=' . $id;
                break;

            case 'project':
                $id = $this->jsmjinput->getInt('insert_project_id');
                $setRedirect = '&pid=' . $id;
                break;

            case 'projectteam':
                $setRedirect = '&pid=' . (int) ($data['project_id'] ?? 0);
                break;
        }

        $message = Text::_('JLIB_APPLICATION_SAVE_SUCCESS');

        switch ($this->getTask()) {
            case 'apply':
                if ($tmpl) {
                    $applyId = $this->view_item === 'club' ? $this->club_id : $id;
                    $this->setRedirect(
                        'index.php?option=com_sportsmanagement&view=' . $this->view_item
                        . '&layout=edit&tmpl=component&id=' . $applyId,
                        $message
                    );
                    break;
                }

                if ($this->view_item === 'club') {
                    $itemId = $this->club_id;
                } elseif ($this->view_item === 'player') {
                    $itemId = $this->person_id ?: $id;
                } else {
                    $itemId = $this->team_id && $this->view_item === 'team' ? $this->team_id : $id;
                }

                $extra = $this->view_item === 'team' ? '&club_id=' . $this->team_club_id : '';
                $this->setRedirect(
                    Route::_(
                        'index.php?option=' . $this->option . '&view=' . $this->view_item
                        . $extra . $this->getRedirectToItemAppend($itemId, $urlVar) . $setRedirect,
                        false
                    ),
                    $message
                );
                break;

            case 'save2copy':
                $this->setRedirect(
                    Route::_(
                        'index.php?option=' . $this->option . '&view=' . $this->view_item
                        . $this->getRedirectToItemAppend($id, $urlVar) . $setRedirect,
                        false
                    )
                );
                break;

            case 'save2new':
                $this->setRedirect(
                    Route::_(
                        'index.php?option=' . $this->option . '&view=' . $this->view_item
                        . $this->getRedirectToItemAppend(null, $urlVar) . $setRedirect,
                        false
                    ),
                    $message
                );
                break;

            default:
                if ($tmpl) {
                    $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
                    break;
                }

                if ($this->view_item === 'club') {
                    $extra = '&club_id=' . $this->club_id;
                } elseif ($this->view_item === 'team') {
                    $extra = '&club_id=' . $this->team_club_id . '&team_id=' . $this->team_id;
                } else {
                    $extra = '';
                }

                $this->setRedirect(
                    Route::_(
                        'index.php?option=' . $this->option . '&view=' . $this->view_list
                        . $extra . $this->getRedirectToListAppend() . $setRedirect,
                        false
                    ),
                    $message
                );
                break;
        }

        return true;
    }

    /**
     * Resolve an administrator form model using Joomla's MVC factory.
     *
     * Preserves the model name, namespace and caller-provided options
     * when forwarding to the Joomla parent controller.
     *
     * @param string $name Requested form model name.
     * @param string $prefix Joomla MVC model namespace.
     * @param array $config Additional model factory configuration.
     * @return mixed Joomla model instance or parent failure result.
     */
    public function getModel($name = '', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Provide the legacy extension point after a successful form save.
     *
     * The default implementation intentionally does nothing; child
     * controllers may override it to handle saved model records.
     *
     * @param BaseDatabaseModel $model Saved Joomla database model.
     * @param array $validData Validated form values.
     * @return void
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = [])
    {
    }
}
