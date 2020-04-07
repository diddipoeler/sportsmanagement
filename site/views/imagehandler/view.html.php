<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage imagehandler
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * sportsmanagementViewImagehandler
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2019
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewImagehandler extends HtmlView
{

	/**
	 * Image selection List
	 *
	 * @since 0.9
	 */
	function display($tpl = null)
	{
		$app = Factory::getApplication();
		$document = Factory::getDocument();

		if (version_compare(JSM_JVERSION, '4', 'eq'))
		{
			$uri = Uri::getInstance();
		}
		else
		{
			$uri = Factory::getURI();
		}

		if ($this->getLayout() == 'upload')
		{
			$this->_displayupload($tpl);

			return;
		}

		/**
*
 * get vars
*/
		$type = Factory::getApplication()->input->getVar('type');
		$folder = ImageSelectSM::getfolder($type);
		$field = Factory::getApplication()->input->getVar('field');
		$fieldid = Factory::getApplication()->input->getVar('fieldid');
		$search = $app->getUserStateFromRequest('com_sportsmanagement.imageselect', 'search', '', 'string');
		$search = trim(StringHelper::strtolower($search));

		Factory::getApplication()->input->setVar('folder', $folder);

		/**
*
 * Do not allow cache
*/
		JResponse::allowCache(false);

		/**
*
 * get images
*/
		$images = $this->get('Images');
		$pageNav = $this->get('Pagination');

		$this->request_url = $uri->toString();

		if (count($images) > 0 || $search)
		{
			$this->images = $images;
			$this->type = $type;
			$this->folder = $folder;
			$this->search = $search;
			$this->state = $this->get('state');
			$this->pageNav = $pageNav;
			$this->field = $field;
			$this->fieldid = $fieldid;
			parent::display($tpl);
		}
		else
		{
			/**
*
 * no images in the folder, redirect to uploadscreen and raise notice
*/
			Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_NO_IMAGES'), Log::INFO, 'jsmerror');
			$this->setLayout('upload');
			$this->form = $this->get('form');
			$this->_displayupload($tpl);

			return;
		}
	}

	function setImage($index = 0)
	{
		if (isset($this->images[$index]))
		{
			$this->_tmp_img = &$this->images[$index];
		}
		else
		{
			$this->_tmp_img = new JObject;
		}
	}

	/**
	 * Prepares the upload image screen
	 *
	 * @param $tpl
	 *
	 * @since 0.9
	 */
	function _displayupload($tpl = null)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();

		/**
*
 * initialise variables
*/
		$document = Factory::getDocument();
		$uri = Factory::getURI();
		$params = ComponentHelper::getParams($option);
		$type = Factory::getApplication()->input->getVar('type');
		$folder = ImageSelectSM::getfolder($type);
		$field = Factory::getApplication()->input->getVar('field');
		$fieldid = Factory::getApplication()->input->getVar('fieldid');
		$menu = Factory::getApplication()->input->setVar('hidemainmenu', 1);
		/**
*
 * get vars
*/
		$task = Factory::getApplication()->input->getVar('task');

		jimport('joomla.client.helper');
		$ftp = ClientHelper::setCredentialsFromRequest('ftp');

		/**
*
 * assign data to template
*/
		$this->params = $params;
		$this->request_url = $uri->toString();
		$this->ftp = $ftp;
		$this->folder = $folder;
		$this->field = $field;
		$this->fieldid = $fieldid;
		$this->menu = $menu;
		parent::display($tpl);
	}

}

