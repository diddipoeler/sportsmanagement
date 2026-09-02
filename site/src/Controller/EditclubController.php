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
        $app = $this->getApplication();
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

        $app = $this->getApplication();
        $post = $app->getInput()->post->getArray();
        $post['merge_teams'] = $this->normalizeMergeTeams($post['merge_teams'] ?? null);

        $this->editClubModel()->updItem($post);

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
}
