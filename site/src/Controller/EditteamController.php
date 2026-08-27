<?php
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditteamModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;

/** Joomla 5/6 frontend controller for team editing. */
final class EditteamController extends FormController
{
    public function apply($key = null, $urlVar = null)
    {
        return $this->save($key, $urlVar);
    }

    public function submit()
    {
        return true;
    }

    public function save($key = null, $urlVar = null)
    {
        $this->checkToken();

        $input = $this->getApplication()->getInput();
        $data = $input->post->getArray();
        $id = $input->getInt('id', 0);
        $saved = $this->editTeamModel()->updItem($data);

        if (!$saved) {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=editteam&tmpl=component&id=' . $id
                . '&ptid=' . (int) ($data['ptid'] ?? 0)
                . '&p=' . (int) ($data['p'] ?? 0)
                . '&tid=' . (int) ($data['tid'] ?? 0),
                Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                'error'
            );

            return false;
        }

        if ($this->getTask() === 'apply') {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=editteam&tmpl=component&id=' . $id
                . '&ptid=' . (int) ($data['ptid'] ?? 0)
                . '&p=' . (int) ($data['p'] ?? 0)
                . '&tid=' . (int) ($data['tid'] ?? 0),
                Text::_('COM_SPORTSMANAGEMENT_EDIT_TEAM_SAVED')
            );

            return true;
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');

        return true;
    }

    public function cancel($key = null)
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', 'cancel');

        return true;
    }

    private function editTeamModel(): EditteamModel
    {
        $model = $this->getModel('Editteam');

        if (!$model instanceof EditteamModel) {
            throw new \RuntimeException('EditteamModel is unavailable.', 500);
        }

        return $model;
    }
}
