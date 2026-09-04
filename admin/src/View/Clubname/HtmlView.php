<?php
/**
 * Joomla 5/6 administrator edit view for an alternative club name.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    SportsManagement
 * @subpackage com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\View\Clubname;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator edit view for an alternative club name. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;

    public function display($tpl = null)
    {
        Factory::getContainer()->get(AdministratorApplication::class)->getInput()->set('hidemainmenu', true);

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->form) {
            throw new \RuntimeException('Club name form could not be loaded.', 500);
        }

        $isNew = empty($this->item->id);
        ToolbarHelper::title(
            Text::_($isNew ? 'COM_SPORTSMANAGEMENT_ADMIN_CLUBNAME_NEW' : 'COM_SPORTSMANAGEMENT_ADMIN_CLUBNAME_EDIT'),
            'clubname'
        );
        ToolbarHelper::apply('clubname.apply');
        ToolbarHelper::save('clubname.save');
        ToolbarHelper::save2new('clubname.save2new');
        ToolbarHelper::save2copy('clubname.save2copy');
        ToolbarHelper::cancel('clubname.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }
}
