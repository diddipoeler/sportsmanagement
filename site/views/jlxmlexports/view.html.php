<?php
/**
 * SportsManagement frontend XML export view compatibility layer.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView;

class sportsmanagementViewjlxmlexports extends HtmlView
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
