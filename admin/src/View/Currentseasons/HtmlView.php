<?php
/**
 * SportsManagement Joomla 5/6 migration.
 *
 * @version    5.6.0 sportsmanagement
 * @author     diddipoeler <diddipoeler@gmx.de>
 * @copyright  Copyright (C) diddipoeler. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Currentseasons;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\CurrentseasonsModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Current-season project dashboard for Joomla 5/6.
 */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof CurrentseasonsModel) {
            throw new \RuntimeException('Currentseasons view requires CurrentseasonsModel.', 500);
        }

        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $this->getDocument()->getWebAssetManager()->useScript('bootstrap.collapse');
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_TITLE'), 'calendar');

        parent::display($tpl);
    }
}
