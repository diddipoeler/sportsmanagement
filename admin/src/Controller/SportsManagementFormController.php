<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
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

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);
        $databaseSelector = $this->jsmjinput->getInt(
            'cfg_which_database',
            (int) $this->jsmapp->getUserState($this->jsmoption . '.cfg_which_database', 0)
        );
        $this->jsmdb = SportsManagementDatabaseResolver::resolve($joomlaDatabase, $databaseSelector);

        $this->team_club_id = (int) $this->jsmapp->getUserState($this->jsmoption . '.club_id', 0);
        $this->jsmdocument = $this->jsmapp->getDocument();
        $this->jsmuser = $this->jsmapp->getIdentity();
        $this->jsmdate = Factory::getDate();
    }

    /**
     * Default import handler for entity forms without an importer.
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
     * Default export handler for entity forms without an exporter.
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
     * Close a modal edit workflow.
     */
    public function cancelmodal($key = null): void
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }

    /**
     * Save a form while preserving SportsManagement-specific redirect rules.
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

        if ($this->view_item === 'round' && $this->getTask() === 'save' && !$data) {
            $data['round_date_first'] = '0000-00-00';
            $data['round_date_last'] = '0000-00-00';
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
     * Resolve administrator models through the component MVCFactory by default.
     */
    public function getModel($name = '', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    protected function postSaveHook(BaseDatabaseModel $model, $validData = [])
    {
    }
}
