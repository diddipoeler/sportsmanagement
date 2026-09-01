<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 controller for tournament-tree node editing and bracket actions. */
final class TreetonodeController extends SportsManagementFormController
{
    protected $view_list = 'treetonodes';

    public function saveshort(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $ids = $this->normaliseIds((array) $input->post->get('cid', [], 'array'));
        $data = $input->post->getArray();
        $model = $this->getModel('Treetonodes', 'Administrator', ['ignore_request' => false]);
        $ok = $model !== false && $model->storeshort($ids, $data);

        $this->setRedirect(
            $this->treeUrl($input->post->getInt('treeto_id'), $input->post->getInt('project_id')),
            Text::_($ok
                ? 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_SAVED'
                : 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_SAVED'
            ) . (!$ok && $model !== false ? $model->getError() : ''),
            $ok ? 'message' : 'error'
        );
    }

    /** Save selected leaves and show the bracket once before final confirmation. */
    public function saveallleaf(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $ids = $this->normaliseIds((array) $input->post->get('cid', [], 'array'));
        $data = $input->post->getArray();
        $model = $this->getModel('Treetonodes', 'Administrator', ['ignore_request' => false]);
        $ok = $model !== false && $model->storeshortleaf($ids, $data);

        $this->setRedirect(
            $this->treeUrl((int) ($data['treeto_id'] ?? 0), (int) ($data['project_id'] ?? 0)),
            $ok ? '' : ($model !== false ? $model->getError() : Text::_('JERROR_AN_ERROR_HAS_OCCURRED')),
            $ok ? 'message' : 'error'
        );
    }

    /** Finalise the leaf selection after the preview state. */
    public function savefinishleaf(): void
    {
        $this->checkToken();

        $data = $this->app->getInput()->post->getArray();
        $model = $this->getModel('Treetonodes', 'Administrator', ['ignore_request' => false]);
        $ok = $model !== false && $model->storefinishleaf($data);

        $this->setRedirect(
            $this->treeUrl((int) ($data['treeto_id'] ?? 0), (int) ($data['project_id'] ?? 0)),
            $ok ? '' : ($model !== false ? $model->getError() : Text::_('JERROR_AN_ERROR_HAS_OCCURRED')),
            $ok ? 'message' : 'error'
        );
    }

    public function removenode(): void
    {
        $this->checkToken();

        $data = $this->app->getInput()->post->getArray();
        $projectId = (int) ($data['project_id'] ?? 0);
        $model = $this->getModel('Treetonodes', 'Administrator', ['ignore_request' => false]);
        $ok = $model !== false && $model->setRemoveNode($data);

        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=treetos'
                . ($projectId > 0 ? '&pid=' . $projectId : ''),
                false
            ),
            Text::_($ok
                ? 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_SAVED'
                : 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_SAVED'
            ) . (!$ok && $model !== false ? $model->getError() : ''),
            $ok ? 'message' : 'error'
        );
    }

    public function cancel($key = null)
    {
        $this->checkToken();
        $input = $this->app->getInput();
        $this->setRedirect($this->treeUrl($input->getInt('tid'), $input->getInt('pid')));

        return true;
    }

    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
    {
        return parent::getRedirectToItemAppend($recordId, $urlVar) . $this->contextAppend();
    }

    protected function getRedirectToListAppend()
    {
        return parent::getRedirectToListAppend() . $this->contextAppend();
    }

    public function getModel($name = 'Treetonode', $prefix = 'Administrator', $config = ['ignore_request' => false])
    {
        return parent::getModel($name, $prefix, $config);
    }

    private function contextAppend(): string
    {
        $input = $this->app->getInput();
        $treeId = $input->getInt('tid');
        $projectId = $input->getInt('pid');

        return ($treeId > 0 ? '&tid=' . $treeId : '')
            . ($projectId > 0 ? '&pid=' . $projectId : '');
    }

    private function treeUrl(int $treeId, int $projectId): string
    {
        return Route::_(
            'index.php?option=com_sportsmanagement&view=treetonodes'
            . ($treeId > 0 ? '&tid=' . $treeId : '')
            . ($projectId > 0 ? '&pid=' . $projectId : ''),
            false
        );
    }

    private function normaliseIds(array $ids): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn (int $id): bool => $id > 0
        )));
    }
}
