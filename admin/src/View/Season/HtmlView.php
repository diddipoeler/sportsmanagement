<?php
/**
 * Native Joomla 5/6 administrator SportsManagement Season edit view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Season;
\defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
final class HtmlView extends BaseHtmlView
{
    public $form; public $item; public $state;
    /**
     * Render the season editing form and configure its administrator toolbar.
     *
     * Load the editable season, Joomla form and model state, surface reported
     * model errors, and choose the add or edit title according to the record
     * identifier. Register save, apply, copy and cancel toolbar actions before
     * delegating layout rendering to Joomla's base view.
     *
     * @param string|null $tpl Optional layout template suffix.
     * @return void The parent view renders the selected administrator layout.
     * @throws \RuntimeException When the model reports an error.
     */
    public function display($tpl = null)
    {
        $this->form = $this->get('Form'); $this->item = $this->get('Item'); $this->state = $this->get('State');
        if ($errors = $this->get('Errors')) { throw new \RuntimeException(implode("\n", $errors), 500); }
        $isNew = empty($this->item->id);
        ToolbarHelper::title(Text::_($isNew ? 'COM_SPORTSMANAGEMENT_ADMIN_SEASON_ADD_NEW' : 'COM_SPORTSMANAGEMENT_ADMIN_SEASON_EDIT'), 'calendar');
        ToolbarHelper::apply('season.apply'); ToolbarHelper::save('season.save'); ToolbarHelper::save2new('season.save2new'); ToolbarHelper::save2copy('season.save2copy'); ToolbarHelper::cancel('season.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
        parent::display($tpl);
    }
}
