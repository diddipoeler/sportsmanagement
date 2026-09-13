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

use Diddipoeler\Component\SportsManagement\Administrator\Service\JoomLeagueCleanupService;
use Diddipoeler\Component\SportsManagement\Administrator\Service\JoomLeagueFinalImportService;
use Diddipoeler\Component\SportsManagement\Administrator\Service\JoomLeaguePostImportService;
use Diddipoeler\Component\SportsManagement\Administrator\Service\JoomLeagueStagingImportService;
use Diddipoeler\Component\SportsManagement\Administrator\Service\JoomLeagueStructureMigrationService;
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
        } elseif (in_array($step, ['11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26'], true)) {
            $result = $this->runNativePostImportStep((int) $step, $sportsTypeId);
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

        $this->setImportStep(11, $sportsTypeId);

        return [
            'Laufzeit:' => $this->runtimeText($started),
            'Tabellenkopie:' => implode('', $messages),
        ];
    }

    private function runNativePostImportStep(int $step, int $sportsTypeId): array
    {
        $started = microtime(true);
        /** @var DatabaseInterface $database */
        $database = $this->app->getContainer()->get(DatabaseInterface::class);
        $service = new JoomLeaguePostImportService($database);
        $finalService = new JoomLeagueFinalImportService($database);
        $structureService = new JoomLeagueStructureMigrationService($database);

        $rows = match ($step) {
            11 => $service->applySportsType($sportsTypeId),
            12 => $service->remapClubRelations(),
            13 => $service->remapSeasonRelations(),
            14 => $service->remapLeagueRelations(),
            15 => $service->remapProjectRelations(),
            16 => $service->remapPositionAndEventTypeRelations(
                gmdate('Y-m-d H:i:s'),
                (int) $this->app->getIdentity()->id
            ),
            17 => $service->remapPersonRelations(),
            18 => $service->remapProjectTeamTeamRelations(),
            19 => $service->remapProjectTeamAndProjectPositionRelations(),
            20 => $service->remapMatchRosterRelations(),
            21 => $finalService->remapRoundDivisionAndProjectTeamMatchRelations(),
            22 => $finalService->remapMatchRelations(),
            23 => $structureService->migrate(),
            24 => $finalService->remapStatisticRelations(),
            25 => $finalService->ensureProjectPositionRelations(
                gmdate('Y-m-d H:i:s'),
                (int) $this->app->getIdentity()->id
            ),
            26 => $this->runNativeCleanupStep($database),
            default => [],
        };

        $messages = [];

        foreach ($rows as $row) {
            $success = (bool) ($row['success'] ?? false);
            $label = (string) ($row['label'] ?? 'JoomLeague');
            $count = (int) ($row['count'] ?? 0);
            $message = (string) ($row['message'] ?? '');
            $color = $success ? 'green' : 'red';
            $messages[] = '<span style="color:' . $color . '"><strong>'
                . $count . ' Datensätze: '
                . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . ' '
                . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
                . '!</strong></span><br />';
        }

        $this->setImportStep($step + 1, $sportsTypeId);
        $resultKey = match ($step) {
            11 => 'Tabellenaktualisierung:',
            12 => 'Update Mannschaften/Spielorte:',
            13 => 'Update Saison:',
            14 => 'Update Liga:',
            15 => 'Update Runden/Gruppen/Projektpositionen/Projektschiedsrichter/Projektmannschaft:',
            16 => 'Update Personen/Projektpositionen:',
            17 => 'Update Team-Spieler/Team-Staff:',
            18 => 'Update Projektmannschaften:',
            19 => 'Update Team-Spieler/Team-Staff:',
            20 => 'Update Spiele:',
            21 => 'Update Spiele:',
            22 => 'Update Spiele:',
            23 => 'Update Spiele:',
            24 => 'Update Match-Statistic:',
            25 => 'Update Projektpositionen:',
            26 => 'Update Bilderpfade:',
            default => 'JoomLeague:',
        };

        return [
            'Laufzeit:' => $this->runtimeText($started),
            $resultKey => implode('', $messages),
        ];
    }

    /** @return array<int,array{label:string,success:bool,count:int,message:string}> */
    private function runNativeCleanupStep(DatabaseInterface $database): array
    {
        $rows = [];
        $databaseTool = $this->getModel('Databasetool');

        foreach ([
            'setNewPicturePath' => 'Bildpfade',
            'setNewComponentName' => 'Komponentenbezeichner',
            'setParamstoJSON' => 'Template-Parameter',
        ] as $method => $label) {
            try {
                if (!is_object($databaseTool) || !method_exists($databaseTool, $method)) {
                    throw new \RuntimeException('Native Databasetool-Methode fehlt: ' . $method);
                }

                $databaseTool->{$method}();
                $rows[] = [
                    'label' => $label,
                    'success' => true,
                    'count' => 0,
                    'message' => 'aktualisiert',
                ];
            } catch (\Throwable $exception) {
                $rows[] = [
                    'label' => $label,
                    'success' => false,
                    'count' => 0,
                    'message' => $exception->getMessage(),
                ];
            }
        }

        return array_merge($rows, (new JoomLeagueCleanupService($database))->cleanup());
    }

    private function setImportStep(int $step, int $sportsTypeId): void
    {
        $input = $this->app->getInput();
        $input->set('filter_sports_type', $sportsTypeId);
        $input->set('jl_table_import_step', (string) $step);
    }

    private function runtimeText(float $started): string
    {
        return Text::sprintf(
            'This page was created in %1$s seconds',
            number_format(microtime(true) - $started, 4, '.', '')
        );
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
