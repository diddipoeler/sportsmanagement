<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 administrator form controller for tournament trees. */
final class TreetoController extends SportsManagementFormController
{
    /** Save division assignments edited directly in the tree list. */
    public function saveshort(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $ids = array_values(array_unique(array_filter(array_map(
            'intval',
            (array) $input->post->get('cid', [], 'array')
        ))));
        $data = $input->post->getArray();
        $model = $this->getModel('Treetos', 'Administrator', ['ignore_request' => false]);
        $result = $model !== false && $model->storeshort($ids, $data);

        $this->setRedirect(
            $this->treetosUrl($input->getInt('pid')),
            Text::_($result
                ? 'COM_SPORTSMANAGEMENT_ADMIN_TREETO_CTRL_SAVED'
                : 'COM_SPORTSMANAGEMENT_ADMIN_TREETO_CTRL_ERROR_SAVED'
            ),
            $result ? 'message' : 'error'
        );
    }

    /** Generate all nodes and continue to the existing node editor. */
    public function generatenode(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $model = $this->getModel('Treeto', 'Administrator', ['ignore_request' => false]);
        $result = $model !== false && $model->setGenerateNode();
        $treeId = $input->post->getInt('id');
        $projectId = $input->post->getInt('pid');

        if ($result) {
            $url = Route::_(
                'index.php?option=com_sportsmanagement&view=treetonodes&task=treetonode.display'
                . '&tid=' . $treeId . '&pid=' . $projectId,
                false
            );
            $message = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_CTRL_GENERATE_NODE');
        } else {
            $url = $this->treetosUrl($projectId);
            $message = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_CTRL_ERROR_GENERATE_NODE')
                . ($model !== false ? $model->getError() : '');
        }

        $this->setRedirect($url, $message, $result ? 'message' : 'error');
    }

    /** Delete selected trees. */
    public function remove(): void
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $ids = array_values(array_unique(array_filter(array_map(
            'intval',
            (array) $input->post->get('cid', [], 'array')
        ))));
        $projectId = $input->getInt('pid');

        if (!$ids) {
            $this->setRedirect(
                $this->treetosUrl($projectId),
                Text::_('COM_SPORTSMANAGEMENT_GLOBAL_ISELECT_TO_DELETE'),
                'warning'
            );

            return;
        }

        $model = $this->getModel('Treeto', 'Administrator', ['ignore_request' => false]);
        $result = $model !== false && $model->delete($ids);
        $message = $result ? '' : ($model !== false ? $model->getError() : Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
        $this->setRedirect($this->treetosUrl($projectId), $message, $result ? 'message' : 'error');
    }

    public function cancel($key = null)
    {
        $projectId = $this->app->getInput()->getInt('pid');
        $this->setRedirect($this->treetosUrl($projectId));

        return true;
    }

    public function getModel($name = 'Treeto', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    private function treetosUrl(int $projectId): string
    {
        return Route::_(
            'index.php?option=com_sportsmanagement&view=treetos'
            . ($projectId > 0 ? '&pid=' . $projectId : ''),
            false
        );
    }
}
