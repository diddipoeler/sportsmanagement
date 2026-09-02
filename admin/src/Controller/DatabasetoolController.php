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

use Joomla\CMS\MVC\Controller\BaseController;

/** Native administrator controller for destructive database-tool actions. */
final class DatabasetoolController extends BaseController
{
    public function repair(): void
    {
        $this->runTableAction('REPAIR', false, 'Alle Tabellen repariert');
    }

    public function optimize(): void
    {
        $this->runTableAction('OPTIMIZE', false, 'Alle Tabellen optimiert');
    }

    public function truncate(): void
    {
        $this->runTableAction('TRUNCATE', false, 'Alle Tabellen geleert');
    }

    public function truncatejl(): void
    {
        $this->runTableAction('TRUNCATE', true, 'Alle JL Tabellen geleert');
    }

    public function updatetemplatemasters(): void
    {
        $this->authoriseAction();
        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=databasetools',
            'Alle Templates angepasst'
        );
    }

    public function picturepath(): void
    {
        $this->authoriseAction();
        $model = $this->getModel('Databasetool', 'Administrator', ['ignore_request' => true]);

        if ($model === false) {
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

    public function getModel($name = 'Databasetool', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, $config + ['ignore_request' => true]);
    }

    private function runTableAction(string $command, bool $joomleague, string $successMessage): void
    {
        $this->authoriseAction();
        $model = $this->getModel();

        if ($model === false) {
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

    private function authoriseAction(): void
    {
        $this->checkToken();
        $identity = $this->app->getIdentity();

        if (!$identity->authorise('core.admin', 'com_sportsmanagement')) {
            throw new \RuntimeException('JERROR_ALERTNOAUTHOR', 403);
        }
    }
}
