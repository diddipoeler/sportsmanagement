<?php
/**
 * SportsManagement legacy site entry point for Joomla 5/6.
 *
 * The component dispatcher only reaches this file for requests which have not yet
 * been migrated to native namespaced MVC classes. Bootstrap work shared by these
 * legacy views lives in Site\Legacy\LegacyBootstrap.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
use Diddipoeler\Component\SportsManagement\Site\Model\ResultsDataModel;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;

/** @var SiteApplication $app */
$app = Factory::getContainer()->get(SiteApplication::class);
$input = $app->getInput();
$document = $app->getDocument();
$view = strtolower($input->getCmd('view', ''));

LegacyBootstrap::bootForView($view);

// Load the administrator country language file used by legacy site views.
$language = $app->getLanguage();
$language->load(
    'com_sportsmanagement_countries',
    JPATH_ADMINISTRATOR,
    $language->getTag(),
    true
);

$document->getWebAssetManager()
    ->registerAndUseScript(
        'com_sportsmanagement.legacy',
        'components/com_sportsmanagement/assets/js/sm_functions.js',
        ['version' => 'auto']
    );

$metaKeys = [];
$configuredMetaKeys = trim((string) $app->get('MetaKeys', ''));

if ($configuredMetaKeys !== '') {
    $metaKeys[] = $configuredMetaKeys;
}

$projectId = $input->getInt('p');

if ($projectId > 0) {
    if (!class_exists(ResultsDataModel::class)) {
        require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
        require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
        require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsDataModel.php';
    }

    $projectModel = new ResultsDataModel();
    $projectModel->setDatabaseSelector($input->getInt('cfg_which_database', 0));
    $projectModel->setProjectId($projectId);

    foreach ($projectModel->getProjectTeams(0) as $team) {
        $teamName = trim((string) ($team->name ?? ''));
        if ($teamName !== '') {
            $metaKeys[] = $teamName;
        }
    }
}

$document->setMetaData('author', 'Dieter Ploeger');
$document->setMetaData('revisit-after', '2 days');
$document->setMetaData('robots', 'index,follow');
$document->setMetaData('keywords', implode(',', array_unique($metaKeys)));
$document->setMetaData('generator', 'JSM - Sports Management');

$command = $input->get('task', 'display');
$filter = InputFilter::getInstance();

if (is_array($command)) {
    $keys = array_keys($command);
    $command = $filter->clean((string) array_pop($keys), 'cmd');
} else {
    $command = $filter->clean((string) $command, 'cmd');
}

$type = '';
$task = $command !== '' ? $command : 'display';

if (str_contains($command, '.')) {
    [$type, $task] = array_pad(explode('.', $command, 2), 2, '');
}

$component = $app->bootComponent('com_sportsmanagement');
$mvcFactory = $component->getMVCFactory();
$controllerName = $type !== '' ? ucfirst($type) : 'Display';
$controllerConfig = [
    'base_path' => JPATH_SITE . '/components/com_sportsmanagement',
];

$controller = $mvcFactory->createController(
    $controllerName,
    'Site',
    $controllerConfig,
    $app,
    $input
);

if ($controller === null && $controllerName !== 'Display') {
    $controller = $mvcFactory->createController(
        'Display',
        'Site',
        $controllerConfig,
        $app,
        $input
    );
}

if ($controller === null) {
    throw new \RuntimeException('SportsManagement site controller not found.', 500);
}

$controller->execute($task !== '' ? $task : 'display');
$controller->redirect();
