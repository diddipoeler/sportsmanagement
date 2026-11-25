<?php
/**
 * Joomla 5/6 SportsManagement administrator controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\LegacyModelDatabaseHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchAdminService;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/** Joomla 5/6 administrator controller for adding an individual match row. */
final class JlextindividualsportController extends SportsManagementAdminController
{
    /**
     * Create an individual sport match using the native administrator service.
     *
     * Requires a valid Joomla form token and component create permission,
     * obtains the selected project and round from Joomla user state and
     * sends the form values to the match service. Reports the result and
     * returns to the individual match editing popup. Service failures use a
     * translated error message and log only the exception class.
     *
     * @return void
     */
    public function addmatch(): void
    {
        $this->checkToken();

        // This legacy add-match task bypasses Joomla's standard save routine.
        $this->assertWritePermission('core.create');

        $app = $this->app;
        $post = $app->getInput()->post->getArray();
        $post['project_id'] = (int) $app->getUserState('com_sportsmanagement.pid', $post['project_id'] ?? 0);
        $post['round_id'] = (int) $app->getUserState('com_sportsmanagement.rid', $post['round_id'] ?? 0);

        try {
            $saved = $this->adminService()->addMatch($post);
            $message = Text::_($saved
                ? 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_MATCH'
                : 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_ADD_MATCH');
            $type = $saved ? 'message' : 'error';
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ': individual match creation failed (' . get_class($e) . ').', Log::ERROR, 'com_sportsmanagement');
            $message = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_ADD_MATCH');
            $type = 'error';
        }

        $this->setRedirect($this->editorUrl($post), $message, $type);
    }

    /**
     * Build the return URL for the individual match editing popup.
     *
     * Converts submitted match, round and team identifiers to integers
     * before including them in the component view route.
     *
     * @param array $post Posted individual match selection fields.
     * @return string URL for the individual sport editor.
     */
    private function editorUrl(array $post): string
    {
        return 'index.php?option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component'
            . '&rid=' . (int) ($post['round_id'] ?? 0)
            . '&id=' . (int) ($post['match_id'] ?? 0)
            . '&team1=' . (int) ($post['projectteam1_id'] ?? 0)
            . '&team2=' . (int) ($post['projectteam2_id'] ?? 0);
    }

    /**
     * Construct the administrator service responsible for match creation.
     *
     * Supplies the effective SportsManagement database connection so
     * writes use the configured database rather than a global default.
     *
     * @return IndividualMatchAdminService Native individual match service.
     */
    private function adminService(): IndividualMatchAdminService
    {
        return new IndividualMatchAdminService($this->database());
    }

    /**
     * Resolve the SportsManagement database for administrator match tasks.
     *
     * Uses the shared namespaced Joomla 5/6 database bridge to validate the
     * raw request or session selector before opening a connection. Retains
     * the central resolver's external-storage entitlement checks without
     * loading the obsolete global SportsManagement helper.
     *
     * @return DatabaseInterface Effective Joomla database connection.
     * @throws \InvalidArgumentException If the database selector is malformed.
     * @throws \RuntimeException If the required Joomla services are unavailable.
     */
    private function database(): DatabaseInterface
    {
        return LegacyModelDatabaseHelper::resolve();
    }
}
