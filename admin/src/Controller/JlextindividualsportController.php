<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchAdminService;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/** Joomla 5/6 administrator controller for adding an individual match row. */
final class JlextindividualsportController extends SportsManagementAdminController
{
    public function addmatch(): void
    {
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

    private function editorUrl(array $post): string
    {
        return 'index.php?option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component'
            . '&rid=' . (int) ($post['round_id'] ?? 0)
            . '&id=' . (int) ($post['match_id'] ?? 0)
            . '&team1=' . (int) ($post['projectteam1_id'] ?? 0)
            . '&team2=' . (int) ($post['projectteam2_id'] ?? 0);
    }

    private function adminService(): IndividualMatchAdminService
    {
        return new IndividualMatchAdminService($this->database());
    }

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

        return \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);
    }
}
