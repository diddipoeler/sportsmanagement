<?php
/**
 * Joomla 5/6 administrator controller for database-tool actions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

/** Native administrator controller for destructive database-tool actions. */
final class DatabasetoolController extends BaseController
{
    /**
     * Run the administrator's REPAIR action on supported database tables.
     * Requires Joomla CSRF and component administrator rights, delegates
     * the maintenance to the existing database-tool model and redirects
     * with a result rather than executing raw SQL in the controller.
     * @return void
     */
    public function repair(): void
    {
        $this->runTableAction('REPAIR', false, 'Alle Tabellen repariert');
    }

    /**
     * Run the administrator's OPTIMIZE action for component tables.
     * Uses the shared authorised maintenance path and reports the
     * result after invoking the native database-tool model.
     * @return void
     */
    public function optimize(): void
    {
        $this->runTableAction('OPTIMIZE', false, 'Alle Tabellen optimiert');
    }

    /**
     * Request an authorised TRUNCATE operation on component tables.
     * Requires a valid Joomla form token and core.admin permission
     * before passing a destructive database operation to the model.
     * @return void
     */
    public function truncate(): void
    {
        $this->runTableAction('TRUNCATE', false, 'Alle Tabellen geleert');
    }

    /**
     * Truncate legacy JoomLeague tables through the administrator tool.
     * Uses the dedicated legacy table selection while preserving the
     * shared CSRF, permission and error-handling contract.
     * @return void
     */
    public function truncatejl(): void
    {
        $this->runTableAction('TRUNCATE', true, 'Alle JL Tabellen geleert');
    }

    /**
     * Explain that the legacy template-master maintenance task is unavailable.
     * Checks administrator authorisation but does not claim a successful
     * database mutation when no native implementation exists.
     * @return void
     */
    public function updatetemplatemasters(): void
    {
        $this->authoriseAction();
        // The legacy task no longer has an implementation in DatabasetoolModel.
        // Do not report a successful database update when nothing was changed.
        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=databasetools',
            'Die Aktualisierung der Template-Master ist derzeit nicht implementiert.',
            'warning'
        );
    }

    /**
     * Invoke the database-tool model to update stored picture paths.
     * Checks administrator permission and token, rejects missing models
     * and redirects with a message describing the attempted operation.
     * @return void
     */
    public function picturepath(): void
    {
        $this->authoriseAction();
        $model = $this->getModel('Databasetool', 'Administrator', ['ignore_request' => true]);

        if (!is_object($model)) {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=databasetools',
                'Datenbankwerkzeug konnte nicht geladen werden',
                'error'
            );

            return;
        }

        $model->setNewPicturePath();
        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=databasetools',
            'Alle Bilderpfade angepasst'
        );
    }

    /**
     * Load the native database-tool model through Joomla MVCFactory.
     * Retains caller configuration but forces ignore_request for database
     * maintenance so request filters cannot overwrite model state.
     * @param string $name Model to load.
     * @param string $prefix Joomla administrator MVC prefix.
     * @param array $config Caller-supplied model options.
     * @return mixed Model returned by the parent controller.
     */
    public function getModel($name = 'Databasetool', $prefix = 'Administrator', $config = [])
    {
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Perform authorised table maintenance using the native model.
     * Requires a valid CSRF token and administrator access, resolves the
     * Joomla or JoomLeague table selection, applies a fixed operation to
     * each table and reports empty sets or per-table failures by redirect.
     * @param string $command Supported maintenance action.
     * @param bool $joomleague Whether to select legacy JoomLeague tables.
     * @param string $successMessage Feedback after all actions succeed.
     * @return void
     */
    private function runTableAction(string $command, bool $joomleague, string $successMessage): void
    {
        $this->authoriseAction();
        $model = $this->getModel();

        if (!is_object($model)) {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=databasetools',
                'Datenbankwerkzeug konnte nicht geladen werden',
                'error'
            );

            return;
        }

        $tables = $joomleague
            ? $model->getJoomleagueTablesTruncate()
            : $model->getSportsManagementTables();
        if ($tables === []) {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=databasetools',
                'Keine passenden Datenbanktabellen gefunden.',
                'warning'
            );

            return;
        }

        $failed = [];

        foreach ($tables as $table) {
            if (!$model->setSportsManagementTableQuery($table, $command)) {
                $failed[] = $table;
            }
        }

        if ($failed) {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=databasetools',
                'Fehler bei: ' . implode(', ', $failed),
                'error'
            );

            return;
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=databasetools', $successMessage);
    }

    /**
     * Require an administrator identity and valid Joomla form token.
     * Checks the CSRF token before any database action and denies guest
     * or unauthorised callers with an HTTP 403 error.
     * @return void
     * @throws \RuntimeException If the request lacks valid authorisation.
     */
    private function authoriseAction(): void
    {
        $this->checkToken();
        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise('core.admin', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
