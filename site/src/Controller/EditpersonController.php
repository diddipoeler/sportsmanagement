<?php
/**
 * Joomla 5/6 frontend controller for person editing.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditpersonModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;

/** Joomla 5/6 frontend controller for person editing. */
final class EditpersonController extends FormController
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
        $saved = $this->editPersonModel()->updItem($data);

        if (!$saved) {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=editperson&tmpl=component&id=' . $id
                . '&pid=' . $id
                . '&p=' . (int) ($data['p'] ?? 0)
                . '&tid=' . (int) ($data['tid'] ?? 0),
                Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                'error'
            );
            return false;
        }

        if ($this->getTask() === 'apply') {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=editperson&tmpl=component&id=' . $id
                . '&pid=' . $id
                . '&p=' . (int) ($data['p'] ?? 0)
                . '&tid=' . (int) ($data['tid'] ?? 0),
                Text::_('COM_SPORTSMANAGEMENT_EDIT_PERSON_SAVED')
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

    private function editPersonModel(): EditpersonModel
    {
        $model = $this->getModel('Editperson');

        if (!$model instanceof EditpersonModel) {
            throw new \RuntimeException('EditpersonModel is unavailable.', 500);
        }

        return $model;
    }
}
