<?php
/**
 * Native Joomla 5/6 frontend XML export view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Jlxmlexports;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

final class HtmlView extends BaseHtmlView
{
    public function display($tpl = null): void
    {
        $model = $this->getModel();

        if (!is_object($model) || !method_exists($model, 'exportData')) {
            throw new \RuntimeException('SportsManagement XML export model is unavailable.', 500);
        }

        $model->exportData();
        parent::display($tpl);
    }
}
