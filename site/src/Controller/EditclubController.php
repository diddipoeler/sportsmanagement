<?php
/**
 * Joomla 5/6 frontend controller for club editing.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditclubModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;

/** Joomla 5/6 frontend controller for club editing. */
final class EditclubController extends FormController
{
    public function apply($key = null, $urlVar = null)
    {
        return $this->save($key, $urlVar);
    }

    public function cancel($key = null)
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', 'cancel');

        return true;
    }

    public function load(): void
    {
        $this->assertEditorPermission();

        $app = $this->app;
        $clubId = $app->getInput()->getInt('cid', 0);

        if ($clubId > 0) {
            $table = $this->editClubModel()->getTable('club');

            if ($table->load($clubId)) {
                $table->checkout((int) $app->getIdentity()->id);
            }
        }

        parent::display();
    }

    public function save($key = null, $urlVar = null)
    {
        $this->checkToken();
        $this->assertEditorPermission();

        $app = $this->app;
        $post = $app->getInput()->post->getArray();
        $post['merge_teams'] = $this->normalizeMergeTeams($post['merge_teams'] ?? null);

        $saved = $this->editClubModel()->updItem($post);

        if (!$saved) {
            $clubId = (int) ($post['id'] ?? 0);
            $projectId = (int) ($post['p'] ?? 0);
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&tmpl=component&view=editclub'
                . '&cid=' . $clubId
                . '&id=' . $clubId
                . '&p=' . $projectId,
                Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                'error'
            );

            return false;
        }

        if ($this->getTask() === 'save') {
            $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');

            return true;
        }

        $clubId = (int) ($post['id'] ?? 0);
        $projectId = (int) ($post['p'] ?? 0);

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&tmpl=component&view=editclub'
            . '&cid=' . $clubId
            . '&id=' . $clubId
            . '&p=' . $projectId
        );

        return true;
    }

    private function editClubModel(): EditclubModel
    {
        $model = $this->getModel('Editclub');

        if (!$model instanceof EditclubModel) {
            throw new \RuntimeException('EditclubModel is unavailable.', 500);
        }

        return $model;
    }

    private function normalizeMergeTeams(mixed $mergeTeams): string
    {
        if (!is_array($mergeTeams) || $mergeTeams === []) {
            return '';
        }

        return implode(',', $mergeTeams);
    }
    /** Ensure a frontend editor cannot write using a token without component ACL. */
    private function assertEditorPermission(): void
    {
        $identity = $this->app->getIdentity();
        $allowed = $identity->authorise('core.edit', 'com_sportsmanagement')
            || $identity->authorise('club.edit', 'com_sportsmanagement');

        if (!$allowed) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

}
