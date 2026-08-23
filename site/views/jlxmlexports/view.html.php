<?php
/** SportsManagement frontend XML export view compatibility layer. */
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
