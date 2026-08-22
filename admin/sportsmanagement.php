<?php
/**
 * SportsManagement legacy administrator entry point for Joomla 5/6.
 *
 * The component dispatcher reaches this file only for administrator requests that
 * still depend on the legacy controller/view surface. Shared compatibility setup is
 * centralised in Administrator\Legacy\LegacyBootstrap.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;

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

$controller = null;
$type = '';
$task = '';
$extensions = class_exists('sportsmanagementHelper')
    ? sportsmanagementHelper::getExtensions()
    : [];
$modelPaths = [];
$viewPaths = [];
$templatePaths = [];

$filter = InputFilter::getInstance();

if (is_array($command)) {
    $keys = array_keys($command);
    $command = $filter->clean((string) array_pop($keys), 'cmd');
} else {
    $command = $filter->clean((string) $command, 'cmd');
}

if (str_contains($command, '.')) {
    [$type, $task] = array_pad(explode('.', $command, 2), 2, '');
} else {
    $task = $command;
}

foreach ($extensions as $extensionName) {
    $extension = (string) $extensionName;
    $basePath = JPATH_SITE
        . '/components/com_sportsmanagement/extensions/'
        . $extension
        . '/admin';

    if (is_dir($basePath)) {
        $language->load('com_sportsmanagement_' . $extension, $basePath);
        $controllerConfig = ['base_path' => $basePath];
    } else {
        $controllerConfig = [];
    }

    if (!is_file($basePath . '/controller.php') || !is_file($basePath . '/' . $extension . '.php')) {
        if ($type !== $extension) {
            $controllerConfig = [];
        }

        $extension = 'sportsmanagement';
    }

    try {
        $controller = BaseController::getInstance(ucfirst($extension), $controllerConfig);
    } catch (Throwable) {
        $controller = BaseController::getInstance('sportsmanagement');
    }

    if (is_dir($basePath . '/models')) {
        $modelPaths[] = $basePath . '/models';
    }

    if (is_dir($basePath . '/views')) {
        $viewPaths[] = $basePath . '/views';
        $templatePaths[] = $basePath . '/views/' . $extensionName . '/tmpl';
    }
}

$controller = BaseController::getInstance('sportsmanagement');

if (!$controller instanceof BaseController) {
    throw new RuntimeException('SportsManagement legacy administrator controller not found.', 500);
}

foreach ($modelPaths as $path) {
    $controller->addModelPath($path, 'sportsmanagementModel');
}

foreach ($viewPaths as $path) {
    $controller->addViewPath($path, 'sportsmanagementView');
}

foreach ($extensions as $extensionName) {
    foreach ($templatePaths as $path) {
        if ($path === '' || !is_dir($path)) {
            continue;
        }

        $view = $controller->getView((string) $extensionName, 'html', 'sportsmanagementView');
        $view->addTemplatePath($path);
    }
}

$controller->execute($task);
$controller->redirect();
