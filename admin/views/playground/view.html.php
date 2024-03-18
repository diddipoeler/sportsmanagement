<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage playground
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Environment\Browser;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

/**
 * sportsmanagementViewPlayground
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewPlayground extends sportsmanagementView
{

	/**
	 * sportsmanagementViewPlayground::init()
	 *
	 * @return
	 */
	public function init()
	{
		$this->lists = array();
		//$this->document->addScript('https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js');

		if ($this->item->latitude == 255)
		{
			$this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_NO_GEOCODE'), 'Error');
			$this->map = false;
		}
		else
		{
			$this->map = true;
		}

		$this->extended = sportsmanagementHelper::getExtended($this->item->extended, 'playground');
		$this->extendeduser = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'playground');

$this->checkextrafields = sportsmanagementHelper::checkUserExtraFields('backend',0,Factory::getApplication()->input->get('view'));
		$lists                  = array();

		if ($this->checkextrafields)
		{
			$lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($this->item->id,'backend',0,Factory::getApplication()->input->get('view'));
		}
        
        $this->lists = $lists;



		
		if (version_compare(JVERSION, '4.0.0', 'ge'))
		{
		  $this->document->addScript(Uri::base() . 'components/' . $this->option . '/assets/js/editgeocode.js');
		}
		else
		{
		//$this->document->addScript((Browser::getInstance()->isSSLConnection() ? "https" : "http") . '://maps.googleapis.com/maps/api/js?libraries=places&language=de');
		//$this->document->addScript(Uri::base() . 'components/' . $this->option . '/assets/js/geocomplete.js');
		$this->document->addScript(Uri::base() . 'components/' . $this->option . '/assets/js/editgeocode.js');
		}

	if ( $this->item->id )
        {
        $this->playgroundnotic = $this->model->getPlaygroundNotic($this->item->id);    
        }

		$daysOfWeek = array('NAME' => Text::_('NAME'),
			                    'VISITORS' => Text::_('VISITORS'));
			$dwOptions  = array();

			foreach ($daysOfWeek AS $key => $value)
			{
				$this->namevisitorsoptions[] = HTMLHelper::_('select.option', $key, $value);
			}

	}


	/**
	 * sportsmanagementViewPlayground::addToolBar()
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		$this->jinput->set('hidemainmenu', true);
		parent::addToolbar();
	}


}
