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

use Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchAdminService;
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
     * returns to the individual match editing popup.
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
            $message = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_ADD_MATCH') . ': ' . $e->getMessage();
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
     * Loads the compatibility database resolver if necessary, reads the
     * integer-filtered database selector and attempts the legacy connection.
     * Falls back to Joomla's database container only when it provides a
     * valid DatabaseInterface implementation.
     *
     * @return DatabaseInterface Effective Joomla database connection.
     * @throws \RuntimeException If the database service is unavailable.
     */
    private function database(): DatabaseInterface
    {
        if (!class_exists('sportsmanagementHelper', false)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';
        }

        $app = $this->app;
        $selector = $app->getInput()->getInt(
            'cfg_which_database',
            (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );

        try {
            $db = \sportsmanagementHelper::getDBConnection(true, $selector);
            if ($db instanceof DatabaseInterface) {
                return $db;
            }
        } catch (\Throwable) {
        }

        $database = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement individual match database is unavailable.', 500);
        }

        return $database;
    }
}
