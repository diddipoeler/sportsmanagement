<?php
/**
 * SportsManagement legacy site entry point for Joomla 5/6.
 *
 * The component dispatcher only reaches this file for requests which have not yet
 * been migrated to native namespaced MVC classes. Bootstrap work shared by these
 * legacy views lives in Site\Legacy\LegacyBootstrap.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
use Diddipoeler\Component\SportsManagement\Site\Model\ResultsDataModel;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;

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

$controllerFile = JPATH_SITE . '/components/com_sportsmanagement/controller.php';

if (!is_file($controllerFile)) {
    throw new \RuntimeException('SportsManagement legacy site controller not found.', 500);
}

require_once $controllerFile;

if (!class_exists('sportsmanagementController')) {
    throw new \RuntimeException('SportsManagement legacy site controller class not found.', 500);
}

$controller = new sportsmanagementController([
    'base_path' => JPATH_SITE . '/components/com_sportsmanagement',
]);
$controller->execute($input->getCmd('task'));
$controller->redirect();
