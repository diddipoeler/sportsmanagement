<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage updates
 * @file       jsm_update_db_tables.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\InstallSchemaStatementParser;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SchemaAdditiveMigrationHelper;
use Joomla\CMS\Log\Log;

// The standalone update entry point can run before component autoloading.
if (!class_exists(SportsManagementAdministratorApplicationResolver::class)) {
    $applicationResolverFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Service/SportsManagementAdministratorApplicationResolver.php';

    if (!is_file($applicationResolverFile)) {
        throw new \RuntimeException('SportsManagement administrator application resolver is missing.', 500);
    }

    require_once $applicationResolverFile;
}

// Older migration pages can be included before the administrator PSR-4 map
// is registered. Resolve these classes from fixed component paths.
foreach ([
    SportsManagementDatabaseResolver::class =>
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Helper/SportsManagementDatabaseResolver.php',
    InstallSchemaStatementParser::class =>
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Helper/InstallSchemaStatementParser.php',
    SchemaAdditiveMigrationHelper::class =>
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Helper/SchemaAdditiveMigrationHelper.php',
] as $helperClass => $helperFile) {
    if (!class_exists($helperClass)) {
        if (!is_file($helperFile)) {
            throw new \RuntimeException('SportsManagement database upgrade dependency is missing.', 500);
        }

        require_once $helperFile;
    }
}

$app = SportsManagementAdministratorApplicationResolver::resolve();

// This legacy screen can update database schema objects; only component
// administrators may access the migration action.
if (!$app->getIdentity()->authorise('core.admin', 'com_sportsmanagement')) {
    throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

HTMLHelper::_('bootstrap.framework');

$version           = '4.20.00';

$minor = 0;
$major = 0;
$build = 0;
$revision = '';


$updateFileDate    = '2024-03-11';
$updateFileTime    = '00:05';
$updateDescription = '<span style="color:orange">'.Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UPDATES_DB_TABLES').'</span>';
$excludeFile       = 'false';
$option            = 'com_sportsmanagement';

$maxImportTime = ComponentHelper::getParams($option)->get('max_import_time', 0);

if (empty($maxImportTime))
{
	$maxImportTime = 880;
}


if ((int) ini_get('max_execution_time') < $maxImportTime)
{
	@set_time_limit($maxImportTime);
}

$maxImportMemory = ComponentHelper::getParams($option)->get('max_import_memory', 0);

if (empty($maxImportMemory))
{
	$maxImportMemory = '150M';
}


if ((int) ini_get('memory_limit') < (int) $maxImportMemory)
{
	ini_set('memory_limit', $maxImportMemory);
}

/**
 * Read the current database-upgrade stage from Joomla user state.
 *
 * Uses the fixed SportsManagement component state key so a request cannot
 * choose a different extension's state namespace.
 *
 * @return int Valid stage in the range zero through two.
 */
function getUpdatePart(): int
{
    $app = SportsManagementAdministratorApplicationResolver::resolve();
    $stage = $app->getUserState('com_sportsmanagementupdate_part', 0);

    // Legacy sessions may contain empty or malformed stage values. Avoid
    // incrementing unbounded or nonnumeric user state to execute SQL.
    return in_array($stage, [0, 1, 2, '0', '1', '2'], true)
        ? (int) $stage
        : 0;
}

/**
 * Advance or reset the database-upgrade stage in the Joomla user session.
 *
 * Increments the persisted stage for positive values or resets it to zero
 * after the last migration step has finished.
 *
 * @param int $val Positive to advance by one stage, zero to reset.
 * @return void
 */
function setUpdatePart(int $val = 1): void
{
    $app = SportsManagementAdministratorApplicationResolver::resolve();
    $stage = $val === 0 ? 0 : min(2, getUpdatePart() + 1);
    $app->setUserState('com_sportsmanagementupdate_part', $stage);
}

/**
 * Reconcile installed SportsManagement tables without destructive SQL changes.
 *
 * The validated first-party schema declares tables, columns and indexes.
 * The Joomla 5/6 helper first creates missing tables and then issues only
 * ADD COLUMN or ADD INDEX for absent structures; existing fields and keys
 * are preserved, even when their definitions differ from the bundled SQL.
 * This update view does not perform DROP, CHANGE or MODIFY operations.
 *
 * A failed SQL operation stops processing, records a sanitized Joomla log
 * entry and leaves the migration stage pending rather than reporting success.
 *
 * @return string Escaped report of processed tables and additive operations.
 * @throws \RuntimeException When schema reading or reconciliation fails.
 */
function ImportTables(): string
{
    /** @var DatabaseInterface $defaultDatabase */
    $defaultDatabase = Factory::getContainer()->get(DatabaseInterface::class);

    if (!$defaultDatabase instanceof DatabaseInterface) {
        throw new \RuntimeException('Joomla database service is unavailable.', 500);
    }

    $database = (new SportsManagementDatabaseResolver())->resolve(null, $defaultDatabase);
    $file = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/sql/install.mysql.utf8.sql';
    $source = @file_get_contents($file);

    if (!is_string($source) || trim($source) === '') {
        throw new \RuntimeException('SportsManagement SQL installation schema is missing.', 500);
    }

    try {
        // The SQL parser prevents comment markers or quoted semicolons from
        // becoming unintended statements in this privileged update screen.
        $statements = InstallSchemaStatementParser::split($source);
        $summary = SchemaAdditiveMigrationHelper::migrate($database, $statements);
    } catch (\Throwable $exception) {
        // Database errors can contain private table names or configuration
        // data; don't render exception messages directly into administrator HTML.
        Log::add(
            'SportsManagement schema upgrade stopped (' . get_class($exception) . ').',
            Log::ERROR,
            'jsmerror'
        );

        throw new \RuntimeException(
            'SportsManagement schema upgrade stopped. Check the Joomla error log and database backup.',
            500,
            $exception
        );
    }

    return '<div class="alert alert-success" role="status">'
        . htmlspecialchars(
            sprintf(
                'SportsManagement: %d tables checked, %d columns and %d indexes added.',
                $summary['tables'],
                $summary['columns'],
                $summary['indexes']
            ),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        )
        . '</div>';
}

?>
    <hr/>
<?php
$mtime     = microtime();
$mtime     = explode(" ", $mtime);
$mtime     = $mtime[1] + $mtime[0];
$starttime = $mtime;

ToolbarHelper::title(Text::_('JSM Sportsmanagement - Database update process'));
echo '<h2>' . Text::sprintf(
		'JSM Sportsmanagement v%1$s - %2$s - Filedate: %3$s / %4$s',
		$version, $updateDescription, $updateFileDate, $updateFileTime
	) . '</h2>';
// Never mutate the schema on a plain GET request. Each upgrade stage must
// be explicitly submitted with a valid Joomla session token.
if ($app->getInput()->post->getInt('run_update', 0) !== 1) {
    echo '<form method="post" id="adminForm" action="">';
    echo '<button class="btn btn-warning" type="submit" name="run_update" value="1">';
    echo htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UPDATES_DB_TABLES'), ENT_QUOTES, 'UTF-8');
    echo '</button>';
    echo HTMLHelper::_('form.token');
    echo '</form>';

    return;
}

if (!$app->checkToken('post')) {
    throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
}

$totalUpdateParts = 2;
setUpdatePart();

if (getUpdatePart() < $totalUpdateParts)
{
	echo '<p><b>';
	echo Text::sprintf('Please remember that this update routine has totally %1$s update steps!', $totalUpdateParts) . '</b><br />';
	echo Text::_('So please go to the bottom of this page to check if there are errors and more update steps to do!');
	echo '</p>';
	echo '<p style="color:red; font-weight:bold; ">';
	echo Text::_('It is recommended that you make a backup of your Database before!!!') . '<br />';
	echo '</p>';
	echo '<hr>';
}

if (getUpdatePart() == $totalUpdateParts)
{
	echo '<hr />';
	echo ImportTables();
	echo '<br /><center><hr />';
	echo Text::sprintf('Memory Limit is %1$s', ini_get('memory_limit')) . '<br />';
	echo Text::sprintf('Memory Peak Usage was %1$s Bytes', number_format(memory_get_peak_usage(true), 0, '', '.')) . '<br />';
	echo Text::sprintf('Time Limit is %1$s seconds', ini_get('max_execution_time')) . '<br />';
	$mtime     = microtime();
	$mtime     = explode(" ", $mtime);
	$mtime     = $mtime[1] + $mtime[0];
	$endtime   = $mtime;
	$totaltime = ($endtime - $starttime);
	echo Text::sprintf('This page was created in %1$s seconds', $totaltime);
	echo '<hr /></center>';
	setUpdatePart(0);
}
else
{
    // The previous reload(true) button converted the second stage to a GET.
    // Requiring a new explicit POST with its own CSRF token keeps the second
    // schema migration stage reachable without relaxing the write guards.
    echo '<form method="post" id="adminForm" action="">';
    echo '<input type="hidden" name="run_update" value="1">';
    echo '<button class="btn btn-warning" type="submit">';
    echo htmlspecialchars(
        Text::sprintf(
            'Click here to do step %1$s of %2$s steps to finish the update.',
            getUpdatePart() + 1,
            $totalUpdateParts
        ),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
    echo '</button>';
    echo HTMLHelper::_('form.token');
    echo '</form>';
}
