<?php
/** Joomla 5/6 compatibility entry point for the random quotes module. */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementRquotes\Site\Helper\RquotesHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

if (!class_exists(RquotesHelper::class)) {
    require_once __DIR__ . '/src/Helper/RquotesHelper.php';
}

$app = Factory::getApplication();
$app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);
/** @var DatabaseInterface $database */
$database = Factory::getContainer()->get(DatabaseInterface::class);
$result = (new RquotesHelper())->getData(
    $params,
    ComponentHelper::getParams('com_sportsmanagement'),
    $app,
    $database
);

$source = $result['source'];
$quoteStyle = $result['style'];
$list = $result['list'];
$textLine = $result['textLine'];
$pictureServer = $result['pictureServer'];

$app->getDocument()
    ->getWebAssetManager()
    ->registerAndUseStyle(
        'mod_sportsmanagement_rquotes',
        'modules/mod_sportsmanagement_rquotes/assets/rquote.css'
    );

if ($source === 'text') {
    require ModuleHelper::getLayoutPath($module->module, 'textfile');
    return;
}

if ($source !== 'db') {
    echo Text::_('MOD_SPORTSMANAGEMENT_RQUOTES_SAVE_DISPLAY_INFORMATION');
    return;
}

require ModuleHelper::getLayoutPath($module->module, $quoteStyle);
