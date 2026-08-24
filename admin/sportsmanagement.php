<?php
/**
 * SportsManagement legacy administrator entry point for Joomla 5/6.
 *
 * Legacy requests are routed through the component MVCFactory so that controllers,
 * models and views receive Joomla's normal dependency injection even when their
 * implementation still lives in the compatibility tree.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtensionLanguageHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

$app = Factory::getApplication();
$identity = $app->getIdentity();

if ($identity === null || !$identity->authorise('core.manage', 'com_sportsmanagement')) {
    Log::add(Text::_('JERROR_ALERTNOAUTHOR'), Log::WARNING, 'jsmerror');
    throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

LegacyBootstrap::boot();

$input = $app->getInput();
$command = $input->get('task', 'display');
$language = $app->getLanguage();
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

$extensions = ExtensionLanguageHelper::forView($input->getCmd('view', ''));

foreach ($extensions as $extensionName) {
    $extension = preg_replace('/[^A-Z0-9_-]/i', '', (string) $extensionName);

    if ($extension === '') {
        continue;
    }

    $basePath = JPATH_SITE
        . '/components/com_sportsmanagement/extensions/'
        . $extension
        . '/admin';

    if (is_dir($basePath)) {
        $language->load('com_sportsmanagement_' . $extension, $basePath);
    }
}

$component = $app->bootComponent('com_sportsmanagement');
$mvcFactory = $component->getMVCFactory();
$controllerName = $type !== '' ? ucfirst($type) : 'Display';
$controllerConfig = [
    'base_path' => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement',
];

$controller = $mvcFactory->createController(
    $controllerName,
    'Administrator',
    $controllerConfig,
    $app,
    $input
);

if ($controller === null && $controllerName !== 'Display') {
    $controller = $mvcFactory->createController(
        'Display',
        'Administrator',
        $controllerConfig,
        $app,
        $input
    );
}

if ($controller === null) {
    throw new RuntimeException('SportsManagement administrator controller not found.', 500);
}

$controller->execute($task !== '' ? $task : 'display');

// Give every normal administrator list/default view a consistent way back to
// the SportsManagement control panel. The toolbar has been assembled by the
// view at this point, but Joomla's administrator template has not rendered it
// yet, so this works for both native PSR-4 views and legacy compatibility views.
$viewName = strtolower($input->getCmd('view', 'cpanel'));
$layout = strtolower($input->getCmd('layout', 'default'));
$format = strtolower($input->getCmd('format', 'html'));
$tmpl = strtolower($input->getCmd('tmpl', ''));

if (
    $task === 'display'
    && $format === 'html'
    && $tmpl !== 'component'
    && !in_array($viewName, ['cpanel', 'sportsmanagement'], true)
    && !in_array($layout, ['edit', 'edit_3', 'edit_4', 'panel'], true)
) {
    ToolbarHelper::back(
        'JSM Panel',
        Route::_('index.php?option=com_sportsmanagement&view=cpanel', false)
    );
}

$controller->redirect();
