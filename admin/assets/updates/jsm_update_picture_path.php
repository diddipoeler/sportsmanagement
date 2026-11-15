<?php
/**
 * CSRF-protected Joomla 5/6 administrator form for the legacy image-path update.
 *
 * @version    5.6.0
 * @author     diddipoeler, SportsManagement contributors
 * @copyright  Copyright (C) 2013-2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ImagePathMigrationHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

// These metadata values remain available to existing administrator update views.
$version = '1.0.68';
$minor = 0;
$major = 0;
$build = 0;
$revision = '';
$updateFileDate = '2018-09-03';
$updateFileTime = '00:05';
$updateDescription = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UPDATES_PICTURE_PATH');
$excludeFile = 'false';

// Legacy update templates may be included before the component PSR-4 namespace
// has been registered. Load required first-party classes from fixed paths.
$dependencies = [
    SportsManagementAdministratorApplicationResolver::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Service/SportsManagementAdministratorApplicationResolver.php',
    SportsManagementDatabaseResolver::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Helper/SportsManagementDatabaseResolver.php',
    ImagePathMigrationHelper::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Helper/ImagePathMigrationHelper.php',
];

foreach ($dependencies as $class => $file) {
    if (class_exists($class, false)) {
        continue;
    }

    if (!is_file($file)) {
        throw new \RuntimeException('SportsManagement image-path migration dependency is missing: ' . basename($file), 500);
    }

    require_once $file;
}

$app = SportsManagementAdministratorApplicationResolver::resolve();

// The legacy view contains a destructive database migration: requiring
// core.admin is intentional rather than treating core.manage as sufficient.
if (!$app->getIdentity()->authorise('core.admin', 'com_sportsmanagement')) {
    throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

$runMigration = $app->getInput()->post->getInt('run_image_path_migration', 0) === 1;

if ($runMigration) {
    if (!$app->checkToken('post')) {
        throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
    }

    /** @var DatabaseInterface $defaultDatabase */
    $defaultDatabase = Factory::getContainer()->get(DatabaseInterface::class);
    $database = (new SportsManagementDatabaseResolver())->resolve(null, $defaultDatabase);
    $summary = ImagePathMigrationHelper::migrate($database);

    $app->enqueueMessage(
        $updateDescription . ': ' . $summary['updatedRows'],
        'message'
    );
}
?>
<form action="" method="post" id="jsm-image-path-migration">
    <p><?php echo htmlspecialchars($updateDescription, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
    <button type="submit" class="btn btn-warning" name="run_image_path_migration" value="1">
        <?php echo htmlspecialchars(Text::_('JAPPLY'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
    </button>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
