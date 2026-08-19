<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 controller for project template settings. */
final class TemplateController extends SportsManagementFormController
{
    /**
     * Persist the dynamic params[] settings form.
     *
     * The shared FormController save path expects jform[], while project
     * template settings intentionally render params[]. Keep that contract here.
     */
    public function save($key = null, $urlVar = null)
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $id = $input->getInt('id');
        $params = $input->post->get('params', [], 'array');
        $model = $this->getModel('Template', 'Administrator', ['ignore_request' => false]);

        if ($model === false || $id <= 0) {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=templates',
                Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'),
                'error'
            );

            return false;
        }

        $projectId = $model->getProjectIdForTemplate($id);
        $result = $model->saveTemplateParams($id, $params);

        if (!$result) {
            $message = $model->getError() ?: Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED');
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_sportsmanagement&view=template&layout=edit&id=' . $id
                    . ($projectId > 0 ? '&pid=' . $projectId : ''),
                    false
                ),
                $message,
                'error'
            );

            return false;
        }

        $message = Text::_('JLIB_APPLICATION_SAVE_SUCCESS');
        $tmpl = $input->getCmd('tmpl');

        if ($this->getTask() === 'apply') {
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_sportsmanagement&view=template&layout=edit&id=' . $id
                    . ($projectId > 0 ? '&pid=' . $projectId : '')
                    . ($tmpl ? '&tmpl=' . $tmpl : ''),
                    false
                ),
                $message
            );

            return true;
        }

        if ($tmpl) {
            $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $message);

            return true;
        }

        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=templates'
                . ($projectId > 0 ? '&pid=' . $projectId : ''),
                false
            ),
            $message
        );

        return true;
    }

    /** Legacy reset task: delete selected non-master template rows. */
    public function reset(): void
    {
        $this->remove();
    }

    public function remove(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $ids = array_values(array_unique(array_filter(array_map(
            'intval',
            (array) $input->post->get('cid', [], 'array')
        ))));
        $isMaster = (array) $input->post->get('isMaster', [], 'array');
        $projectId = $input->getInt('pid');

        if (!$ids) {
            $this->setRedirect(
                $this->templatesUrl($projectId),
                Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TO_DELETE'),
                'warning'
            );

            return;
        }

        foreach ($ids as $id) {
            if (!empty($isMaster[$id])) {
                $this->setRedirect(
                    $this->templatesUrl($projectId),
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_CTRL_DELETE_WARNING'),
                    'warning'
                );

                return;
            }
        }

        $model = $this->getModel('Template', 'Administrator', ['ignore_request' => false]);

        if ($model === false || !$model->delete($ids)) {
            $message = $model !== false && $model->getError()
                ? $model->getError()
                : Text::_('JLIB_APPLICATION_ERROR_DELETE_FAILED');
            $this->setRedirect($this->templatesUrl($projectId), $message, 'error');

            return;
        }

        $this->setRedirect(
            $this->templatesUrl($projectId),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_RESET_SUCCESS')
        );
    }

    /** Add newly introduced XML fields/defaults to selected template settings. */
    public function update(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $ids = array_values(array_unique(array_filter(array_map(
            'intval',
            (array) $input->post->get('cid', [], 'array')
        ))));
        $isMaster = (array) $input->post->get('isMaster', [], 'array');
        $projectId = $input->getInt('pid');

        if (!$ids) {
            $this->setRedirect(
                $this->templatesUrl($projectId),
                Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TO_DELETE'),
                'warning'
            );

            return;
        }

        foreach ($ids as $id) {
            if (!empty($isMaster[$id])) {
                $this->setRedirect(
                    $this->templatesUrl($projectId),
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_CTRL_DELETE_WARNING'),
                    'warning'
                );

                return;
            }
        }

        $model = $this->getModel('Template', 'Administrator', ['ignore_request' => false]);

        if ($model === false || !$model->update($ids)) {
            $message = $model !== false && $model->getError()
                ? $model->getError()
                : Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED');
            $this->setRedirect($this->templatesUrl($projectId), $message, 'error');

            return;
        }

        $this->setRedirect(
            $this->templatesUrl($projectId),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_UPDATE_SUCCESS')
        );
    }

    /** Import one configuration row from the project's master template. */
    public function masterimport(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $templateId = $input->post->getInt('templateid');
        $projectId = $input->post->getInt('pid');
        $model = $this->getModel('Template', 'Administrator', ['ignore_request' => false]);
        $result = $templateId > 0 && $projectId > 0 && $model !== false
            && $model->import($templateId, $projectId);

        $this->setRedirect(
            $this->templatesUrl($projectId),
            Text::_($result
                ? 'COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_CTRL_IMPORTED_TEMPLATE'
                : 'COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_CTRL_ERROR_IMPORT_TEMPLATE'
            ),
            $result ? 'message' : 'error'
        );
    }

    private function templatesUrl(int $projectId): string
    {
        return Route::_(
            'index.php?option=com_sportsmanagement&view=templates'
            . ($projectId > 0 ? '&pid=' . $projectId : ''),
            false
        );
    }
}
