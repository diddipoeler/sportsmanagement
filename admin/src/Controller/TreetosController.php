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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 administrator list controller for tournament trees. */
final class TreetosController extends SportsManagementAdminController
{
    /**
     * Open the node-generation form for the selected tournament tree.
     *
     * Reads the tree and optional project identifiers as filtered integers
     * and redirects to the historical generation layout without writing
     * records or changing the selected tournament.
     *
     * @return void
     */
    public function genNode(): void
    {
        $input = $this->app->getInput();
        $id = $input->getInt('id');
        $projectId = $input->getInt('pid');

        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=treeto&layout=gennode&id=' . $id
                . ($projectId > 0 ? '&pid=' . $projectId : ''),
                false
            )
        );
    }

    /**
     * Create a blank tournament tree for the selected project.
     *
     * Validates Joomla's form token, requires component create access
     * and reads the requested project ID. Delegates persistence to the
     * native Treeto model, then redirects to the tree overview with
     * translated success or failure feedback.
     *
     * @return void
     * @throws \RuntimeException If component create permission is denied.
     */
    public function save(): void
    {
        $this->checkToken();

        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise('core.create', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $input = $this->app->getInput();
        $projectId = $input->post->getInt('project_id') ?: $input->getInt('pid');
        $model = $this->getModel('Treeto', 'Administrator', ['ignore_request' => false]);
        $result = $projectId > 0 && $model !== false && $model->save(['project_id' => $projectId]);

        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=treetos'
                . ($projectId > 0 ? '&pid=' . $projectId : ''),
                false
            ),
            Text::_($result
                ? 'COM_SPORTSMANAGEMENT_ADMIN_TREETO_CTRL_SAVED'
                : 'COM_SPORTSMANAGEMENT_ADMIN_TREETO_CTRL_ERROR_SAVED'
            ),
            $result ? 'message' : 'error'
        );
    }

    /**
     * Resolve a tournament tree model through Joomla's MVC factory.
     *
     * Passes the requested administrator model name and factory
     * configuration through to the native parent list controller.
     *
     * @param string $name Requested tournament tree model name.
     * @param string $prefix Administrator MVC model namespace.
     * @param array $config Optional model factory configuration.
     * @return mixed Joomla model or the parent's failure result.
     */
    public function getModel($name = 'Treeto', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
