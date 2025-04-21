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
use Joomla\CMS\Http\HttpFactory;
use Joomla\Registry\Registry;

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
		$lists	= array();

		if ($this->checkextrafields)
		{
			$lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($this->item->id,'backend',0,Factory::getApplication()->input->get('view'));
		}
        
        $this->lists = $lists;


$country = JSMCountries::getCountryName($this->item->country) ;
$headers = array();
$query = $this->item->address;
$query .=  ', '.$this->item->location;
$query .=  ', '.$this->item->zipcode;
$query .=  ', '.$country;        
//$link = 'http://nominatim.openstreetmap.org/search?format=geojson&addressdetails=1&limit=1&q='.$this->item->address.', '.$this->item->location;
      
$link = 'http://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=1&q=';
      
$link .= urlencode($query);
      
$http = HttpFactory::getHttp();
$getresult = $http->get($link);
$data = json_decode($getresult->body, true);
	if ( $data[0]->address->state )
      {
        
        if ( $data[0]->address->country_code == 'gb' )
      {
        $this->item->state = $data[0]->address->county;
        }
        else
        {
$this->item->state = $data[0]->address->state;
        }
        
        
      }

 if ( $data[0]->address->state_district && !$this->item->state )
      {
$this->item->state = $data[0]->address->state_district;
      }
      
      
$this->form->setValue('state',null, $this->item->state);
      
      if ( $data[0]->lat )
      {
        $this->item->latitude = $data[0]->lat;
        $this->form->setValue('latitude',null, $this->item->latitude);

        $this->item->longitude = $data[0]->lon;  
        $this->form->setValue('longitude',null, $this->item->longitude);
      }
      
		/**
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
      */

	if ( $this->item->id )
        {
        $this->playgroundnotic = $this->model->getPlaygroundNotic($this->item->id);
        $this->logohistory = $this->model->getlogohistoryPlayground($this->item->id,0);
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
