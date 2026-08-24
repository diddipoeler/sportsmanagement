<?php
/** SportsManagement administrator countries list view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewjlextcountries extends sportsmanagementView
{
    public function init()
    {
        $federations = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_FEDERATION')),
        ];

        if ($res = $this->model->getFederation()) {
            $federations = array_merge($federations, $res);
            $this->federation = $res;
        }

        $this->lists = [
            'federation' => HTMLHelper::_(
                'select.genericlist',
                $federations,
                'filter_federation',
                'class="inputbox" style="width:140px;" onchange="this.form.submit();"',
                'value',
                'text',
                $this->state->get('filter.federation')
            ),
        ];
    }

    protected function addToolbar()
    {
        ToolbarHelper::addNew('jlextcountry.add');
        ToolbarHelper::editList('jlextcountry.edit');
        ToolbarHelper::custom('jlextcountry.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::custom(
            'jlextcountries.importplz',
            'upload',
            'upload',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_IMPORT_PLZ'),
            true
        );
        ToolbarHelper::archiveList('jlextcountry.export', Text::_('JTOOLBAR_EXPORT'));
        parent::addToolbar();
    }
}
