<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage smquotes
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewsmquotes
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewsmquotes extends sportsmanagementView
{
	/**
	 * A \JForm instance with filter fields.
	 *
	 * @var    \JForm
	 * @since  3.6.3
	 */
	public $filterForm;

	/**
	 * An array with active filters.
	 *
	 * @var    array
	 * @since  3.6.3
	 */
	public $activeFilters;

	/**
	 * sportsmanagementViewsmquotes::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$this->table = Table::getInstance('smquote', 'sportsmanagementTable');
		
try
{		
$this->filterForm    = $this->model->getFilterForm();
$this->activeFilters = $this->model->getActiveFilters();
}
catch (Exception $e)
{
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');	
}	
		
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_QUOTES_TITLE');
		ToolbarHelper::addNew('smquote.add');
		ToolbarHelper::editList('smquote.edit');
		ToolbarHelper::custom('smquote.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
		ToolbarHelper::custom('smquotes.edittxt', 'featured.png', 'featured_f2.png', Text::_('JTOOLBAR_EDIT'), false);
		$bar = Toolbar::getInstance('toolbar');
		$bar->appendButton('Link', 'info', 'Kategorie', 'index.php?option=com_categories&extension=com_sportsmanagement');
		ToolbarHelper::archiveList('smquote.export', Text::_('JTOOLBAR_EXPORT'));
		parent::addToolbar();
	}
}
