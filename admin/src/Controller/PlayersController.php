<?php
/**
 * Joomla 5/6 SportsManagement administrator controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Model\PlayerModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\FinderRelationNotifier;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 list controller for persons/players. */
final class PlayersController extends SportsManagementAdminController
{
    /**
     * Import player records from the submitted administrator upload form.
     * Requires a valid POST token and component creation permission,
     * delegates to the player model and redirects with save feedback.
     * @return void
     */
    public function importupload(): void
    {
        $this->requireToken();
        $this->requirePermission('core.create');
        $model = $this->playerModel();
        $ok = $model->importupload($this->app->getInput()->post->getArray());

        if (!$ok && $model->getError()) {
            $this->app->enqueueMessage($model->getError(), 'error');
        } elseif ($ok) {
            $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_SAVE_SUCCESS'), 'message');
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=players');
    }

    /**
     * Assign selected players to the current team or season.
     * Checks the POST token and edit access, saves assignments using
     * the native model and notifies Joomla Finder on success.
     * @return void
     */
    public function assign(): void
    {
        $this->requireToken();
        $this->requirePermission('core.edit');
        $model = $this->playerModel();
        $post = $this->app->getInput()->post->getArray();
        $ok = $model->storeAssign($post);

        if ($ok) {
            $this->finderNotifier()->notifyPeople((array) ($post['cid'] ?? []));
        } elseif ($model->getError()) {
            $this->app->enqueueMessage($model->getError(), 'error');
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }

    /**
     * Close the player assignment popup after a valid form submission.
     * @return void
     */
    public function close(): void
    {
        $this->requireToken();
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }

    /**
     * Save inline player changes and refresh their Finder records.
     * Verifies Joomla token and edit permission, reads selected person
     * IDs from POST and notifies Finder only after successful storage.
     * @return void
     */
    public function saveshort(): void
    {
        $this->requireToken();
        $this->requirePermission('core.edit');
        $model = $this->playerModel();
        $personIds = (array) $this->app->getInput()->post->get('cid', [], 'array');
        $ok = $model->saveshort();

        if ($ok) {
            $this->finderNotifier()->notifyPeople($personIds);
        } elseif ($model->getError()) {
            $this->app->enqueueMessage($model->getError(), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_sportsmanagement&view=players', false));
    }

    /**
     * Resolve the administrator player model with caller-supplied MVC options.
     * @param string $name Requested model name.
     * @param string $prefix Joomla administrator model namespace.
     * @param array $config MVC model configuration.
     * @return mixed Parent controller model result.
     */
    public function getModel($name = 'Player', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Require the native player model for administrator write operations.
     * @return PlayerModel Resolved and validated player model.
     * @throws \RuntimeException When Joomla cannot load PlayerModel.
     */
    private function playerModel(): PlayerModel
    {
        $model = $this->getModel('Player', 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof PlayerModel) {
            throw new \RuntimeException('PlayerModel is unavailable.', 500);
        }

        return $model;
    }

    /**
     * Create a Finder relation notifier for updated player associations.
     * @return FinderRelationNotifier Finder update service.
     */
    private function finderNotifier(): FinderRelationNotifier
    {
        return new FinderRelationNotifier($this->sportsManagementDatabase());
    }

    /**
     * Resolve the selected SportsManagement database for Finder updates.
     * Reads the database selector from request or user state and delegates
     * connection selection to the administrator database resolver.
     * @return DatabaseInterface Selected component database service.
     */
    private function sportsManagementDatabase(): DatabaseInterface
    {
        $input = $this->app->getInput();
        $selector = $input->getInt(
            'cfg_which_database',
            (int) $this->app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );

        return (new SportsManagementDatabaseResolver())->resolve(
            $selector,
            self::joomlaDatabase()
        );
    }

    /**
     * Obtain and validate Joomla's database interface service.
     * @return DatabaseInterface Current Joomla container database.
     * @throws \RuntimeException If the resolved service has the wrong type.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement player database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Require a valid Joomla POST session token for administrator actions.
     * @return void
     * @throws \RuntimeException If the form token is invalid.
     */
    private function requireToken(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }
    }

    /**
     * Require the requested SportsManagement component ACL action.
     * @param string $action Joomla ACL action name to check.
     * @return void
     * @throws \RuntimeException If the user is not authorised.
     */
    private function requirePermission(string $action): void
    {
        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise($action, 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
