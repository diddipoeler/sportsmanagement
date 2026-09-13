<?php
/**
 * Native Joomla 5/6 controller for the JoomLeague import workflow.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\JoomLeagueStagingImportService;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseFactory;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 controller for the JoomLeague import workflow. */
final class JoomleagueimportsController extends BaseController
{
    public function joomleaguesetagegroup()
    {
        $this->checkToken();

        $model = $this->getModel();
        $model->joomleaguesetagegroup();

        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=joomleagueimports&jl_table_import_step=0&layout=infofield',
                false
            ),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT_SETAGEGROUP')
        );

        return true;
    }

    public function importjoomleaguenew()
    {
        $this->checkToken();

        $app = $this->app;
        $input = $app->getInput();
        $step = $input->getString('jl_table_import_step', '0');
        $sportsTypeId = $input->getInt('filter_sports_type', 0);

        if ($step === 'ENDE') {
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_sportsmanagement&view=joomleagueimports&jl_table_import_step=0&layout=infofield',
                    false
                )
            );

            return true;
        }

        if ($step === '10') {
            $result = $this->runNativeStagingStep($sportsTypeId);
        } else {
            $model = $this->getModel();
            $result = $model->importjoomleaguenew($step, $sportsTypeId);
        }

        $app->getDocument()->addScriptOptions('success', $result);

        $nextStep = $input->getString('jl_table_import_step', '0');
        $app->setUserState('com_sportsmanagement.jl_table_import_success', $result);

        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=joomleagueimports&layout=default'
                . '&jl_table_import_step=' . rawurlencode($nextStep)
                . '&filter_sports_type=' . $sportsTypeId,
                false
            )
        );

        return true;
    }

    public function importjoomleagueagegroup()
    {
        $this->checkToken();

        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=joomleagueimports&layout=infofield',
                false
            )
        );

        return true;
    }

    public function getModel($name = 'Joomleagueimports', $prefix = 'Administrator', $config = [])
    {
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }

    private function runNativeStagingStep(int $sportsTypeId): array
    {
        $started = microtime(true);
        /** @var DatabaseInterface $target */
        $target = $this->app->getContainer()->get(DatabaseInterface::class);
        $source = $this->createJoomLeagueDatabase();

        try {
            $rows = (new JoomLeagueStagingImportService($target, $source))->stage($sportsTypeId);
        } finally {
            if (method_exists($source, 'disconnect')) {
                $source->disconnect();
            }
        }

        $messages = [];

        foreach ($rows as $row) {
            $success = (bool) ($row['success'] ?? false);
            $table = (string) ($row['table'] ?? 'joomleague');
            $copied = (int) ($row['copied'] ?? 0);
            $message = (string) ($row['message'] ?? '');
            $color = $success ? 'green' : 'red';
            $messages[] = '<span style="color:' . $color . '"><strong>'
                . $copied . ' Daten aus der Tabelle: ( '
                . htmlspecialchars($table, ENT_QUOTES, 'UTF-8') . ' ) '
                . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
                . '!</strong></span><br />';
        }

        $this->app->getInput()->set('filter_sports_type', $sportsTypeId);
        $this->app->getInput()->set('jl_table_import_step', '11');

        return [
            'Laufzeit:' => Text::sprintf(
                'This page was created in %1$s seconds',
                number_format(microtime(true) - $started, 4, '.', '')
            ),
            'Tabellenkopie:' => implode('', $messages),
        ];
    }

    private function createJoomLeagueDatabase(): DatabaseInterface
    {
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $driver = trim((string) $params->get('jl_dbtype', ''));

        if ($driver === '') {
            $driver = (string) $this->app->get('dbtype', 'mysqli');
        }

        $driver = match (strtolower($driver)) {
            'mysql' => 'mysqli',
            'postgresql' => 'pgsql',
            default => strtolower($driver),
        };

        return (new DatabaseFactory())->getDriver($driver, [
            'host' => (string) ($params->get('jl_host') ?: $this->app->get('host', 'localhost')),
            'user' => (string) ($params->get('jl_user') ?: $this->app->get('user', '')),
            'password' => (string) ($params->get('jl_password') ?: $this->app->get('password', '')),
            'database' => (string) ($params->get('jl_db') ?: $this->app->get('db', '')),
            'prefix' => (string) $params->get('jl_dbprefix', ''),
            'select' => true,
        ]);
    }
}
