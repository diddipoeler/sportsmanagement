<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage smimageimports
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewsmimageimports
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewsmimageimports extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewsmimageimports::init()
	 * 
	 * @return void
	 */
	public function init()
	{
		$checkimages = $this->model->getimagesxml();
		$this->files = $this->model->getXMLFiles();

		$folders[]        = HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_FOLDER'), 'id', 'name');
		$allfolders       = $this->model->getXMLFolder();
		$folders          = array_merge($folders, $allfolders);
		$lists['folders'] = HTMLHelper::_(
			'select.genericList',
			$folders,
			'filter_image_folder',
			'class="inputbox" onChange="this.form.submit();" style="width:220px"',
			'id',
			'name',
			$this->state->get('filter.image_folder')
		);

		$this->lists = $lists;

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGES_IMPORT');
		$this->icon  = 'images-import';
		ToolbarHelper::custom('smimageimports.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
		ToolbarHelper::divider();
        parent::addToolbar();
	}

}
