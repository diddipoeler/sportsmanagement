<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage club
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Environment\Browser;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\Registry\Registry;

/**
 * sportsmanagementViewClub
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewClub extends sportsmanagementView
{

	/**
	 * sportsmanagementViewClub::init()
	 *
	 * @return
	 */
	public function init()
	{
		$your_array = array();
		//$this->document->addScript('https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js');
/**
//$extended           = sportsmanagementHelper::getExtended($this->item->extended, 'club');
$this->extended     = sportsmanagementHelper::getExtended($this->item->extended, 'club');;
//$extendeduser       = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'club');
$this->extendeduser = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'club');;
*/
      /**
      foreach ($this->extended->getFieldsets() as $fieldset)
			{
$fields = $this->extended->getFieldset($fieldset->name);


echo '<pre>'.print_r($fieldset->name,true).'</pre>';
              

              
					foreach ($fields as $field)
					{ 
                     // echo 'name '.$field->name.'<br>';
        //echo 'label '.$field->label.'<br>';
                      
                     $field->value = 'test';
          //            echo 'value '.$field->value.'<br>'; 
                      
            //          echo 'input '.$field->input.'<br>';
                      
                      
                      
                      
                    }
        
      }
      */
/**
Parameter 	Value
amenity 	name and/or type of POI
street 	housenumber and streetname
city 	city
county 	county
state 	state
country 	country
postalcode 	postal code
*/


if ( $this->item->id )
{
$this->logohistory = $this->model->getlogohistory($this->item->id,0);
}
		
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
$data = json_decode($getresult->body);
      
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
  
      if ( $this->item->extended )
      {
$your_array = json_decode($this->item->extended,true); 
      }

		
 

		
if (isset($data[0]->address->county))
{
$your_array["COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME"] = $data[0]->address->county;
}
if (isset($data[0]->address->state_district))
{
$your_array["COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_SHORT_NAME"] = $data[0]->address->state_district;
}
if (isset($data[0]->address->suburb))
{
$your_array["COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME"] = $data[0]->address->suburb;
}
if (isset($data[0]->address->quarter))
{
$your_array["COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_SHORT_NAME"] = $data[0]->address->quarter;   
} 
if (isset($data[0]->address->region))
{
$your_array["COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_LONG_NAME"] = $data[0]->address->region;
}
if (isset($data[0]->address->city_district))
{
$your_array["COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_SHORT_NAME"] = $data[0]->address->city_district;         
}
if (isset($data[0]->address->leisure))
{    
$your_array["COM_SPORTSMANAGEMENT_OSM_LEISURE"] = $data[0]->address->leisure;    
}
if (isset($data[0]->address->house_number))
{
$your_array["COM_SPORTSMANAGEMENT_OSM_HOUSE_NUMBER"] = $data[0]->address->house_number; 
}
if (isset($data[0]->address->road))
{
$your_array["COM_SPORTSMANAGEMENT_OSM_ROAD"] = $data[0]->address->road;      
}
if (isset($data[0]->address->village))
{
$your_array["COM_SPORTSMANAGEMENT_OSM_VILLAGE"] = $data[0]->address->village;   
}
if (isset($data[0]->address->municipality))
{
$your_array["COM_SPORTSMANAGEMENT_OSM_MUNICIPALITY"] = $data[0]->address->municipality;   
}   
if (isset($data[0]->address->county))
{
$your_array["COM_SPORTSMANAGEMENT_OSM_COUNTY"] = $data[0]->address->county;  
}
if (isset($data[0]->address->state))
{
$your_array["COM_SPORTSMANAGEMENT_OSM_STATE"] = $data[0]->address->state;   
}
if (isset($data[0]->address->industrial))
{
$your_array["COM_SPORTSMANAGEMENT_OSM_INDUSTRIAL"] = $data[0]->address->industrial;   
}
if (isset($data[0]->address->building))
{
$your_array["COM_SPORTSMANAGEMENT_OSM_BUILDING"] = $data[0]->address->building; 
}
if (isset($data[0]->address->quarter)) 
{    
$your_array["COM_SPORTSMANAGEMENT_OSM_QUARTER"] = $data[0]->address->quarter;   
}
if (isset($data[0]->address->suburb))
{
$your_array["COM_SPORTSMANAGEMENT_OSM_SUBURB"] = $data[0]->address->suburb; 
}
if (isset($data[0]->address->city_district))
{     
$your_array["COM_SPORTSMANAGEMENT_OSM_CITY_DISTRICT"] = $data[0]->address->city_district;
}
if (isset($data[0]->address->city))
{    
$your_array["COM_SPORTSMANAGEMENT_OSM_CITY"] = $data[0]->address->city;
}
if (isset($data[0]->address->town))
{
$your_array["COM_SPORTSMANAGEMENT_OSM_TOWN"] = $data[0]->address->town; 
}

if (isset($data[0]->address->hamlet))
{
$your_array["COM_SPORTSMANAGEMENT_OSM_HAMLET"] = $data[0]->address->hamlet;
}
if (isset($data[0]->address->neighbourhood))
{
$your_array["COM_SPORTSMANAGEMENT_OSM_NEIGHBOURHOOD"] = $data[0]->address->neighbourhood;
}
		
//echo ' your_array <br><pre>'.print_r($your_array ,true).'</pre><br>';
$parameter = new Registry;
$parameter->loadArray($your_array);
$this->item->extended = (string) $parameter;      
//echo ' dataextended <br><pre>'.print_r($dataextended ,true).'</pre><br>';
      
      
      
      
      /**
$jRegistry = new Registry;
//$jRegistry->loadString($this->item->extended);   
$jRegistry->toArray($this->item->extended);      
$jRegistry->set("COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME", $data[0]->address->county);
      
      
  
echo ' jRegistry <br><pre>'.print_r($jRegistry ,true).'</pre><br>'; 

$parameter = new Registry;
$parameter->loadArray($jRegistry);
$dataextended = (string) $parameter;      
echo ' dataextended <br><pre>'.print_r($dataextended ,true).'</pre><br>';       
*/      
      
//$this->form->setValue('COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME','extended', $this->item->county);      
      

//echo ' head <br><pre>'.print_r($this->item->extended->COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME  ,true).'</pre><br>'; 
//echo ' link <br><pre>'.print_r($link ,true).'</pre><br>'; 
      
//echo ' item extended <br><pre>'.print_r($this->item->extended ,true).'</pre><br>';   
      
//echo ' item <br><pre>'.print_r($this->item ,true).'</pre><br>'; 
      
//echo ' link <br><pre>'.print_r($link ,true).'</pre><br>';      
//echo ' data <br><pre>'.print_r($data ,true).'</pre><br>';       

		
		$starttime  = microtime();
		$this->tmpl = $this->jinput->get('tmpl');

		if ($this->item->latitude != 255)
		{
			$this->googlemap = true;
		}
		else
		{
			$this->googlemap = false;
		}

if ( $this->item->logo_big == 'images/com_sportsmanagement/database/clubs/large/placeholder_150.png' )
{
$this->form->setValue('logo_big','',ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_big',''));	
$this->item->logo_big = ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_big','');  
}
if ( $this->item->logo_middle == 'images/com_sportsmanagement/database/clubs/medium/placeholder_50.png' )
{
$this->form->setValue('logo_middle','',ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_medium',''));	
$this->item->logo_middle = ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_medium','');  
}		
if ( $this->item->logo_small == 'images/com_sportsmanagement/database/clubs/small/placeholder_small.gif' )
{
$this->form->setValue('logo_small','',ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_small',''));	
$this->item->logo_small = ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_small','');  
}		
		
		if ($this->item->id)
		{
			/** Alles ok */
			if ($this->item->founded == '0000-00-00')
			{
				$this->item->founded = '';
				$this->form->setValue('founded',null, '');
			}

			if ($this->item->dissolved == '0000-00-00')
			{
				$this->item->dissolved = '';
				$this->form->setValue('dissolved',null, '');
			}
		}
		else
		{
			$this->form->setValue('founded',null, '');
			$this->form->setValue('dissolved',null, '');
            $this->form->setValue('country',null, Factory::getApplication()->getUserState("com_sportsmanagement.clubnation", ''));
		}

		if ($this->item->latitude == 255)
		{
			$this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_NO_GEOCODE'), 'Error');
			$this->map = false;
		}
		else
		{
			$this->map = true;
		}

		//$extended           = sportsmanagementHelper::getExtended($this->item->extended, 'club');
		$this->extended     = sportsmanagementHelper::getExtended($this->item->extended, 'club');;
		//$extendeduser       = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'club');
		$this->extendeduser = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'club');;

		$this->checkextrafields = sportsmanagementHelper::checkUserExtraFields('backend',0,Factory::getApplication()->input->get('view'));
		$lists                  = array();

		if ($this->checkextrafields)
		{
			$lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($this->item->id,'backend',0,Factory::getApplication()->input->get('view'));
		}

		/** die mannschaften zum verein */
		if ($this->item->id)
		{
			$this->teamsofclub = $this->model->teamsofclub($this->item->id);
		}

		$this->lists = $lists;

		//$this->document->addScript('https://maps.googleapis.com/maps/api/js?libraries=places&language=de');
		//$this->document->addScript(Uri::base() . 'components/' . $this->option . '/assets/js/geocomplete.js');

		if (version_compare(JVERSION, '4.0.0', 'ge'))
		{
			$this->document->addScript(Uri::base() . 'components/' . $this->option . '/assets/js/editgeocode.js');
		}
		else
		{
			$this->document->addScript(Uri::base() . 'components/' . $this->option . '/assets/js/editgeocode.js');
		}

		if (PluginHelper::isEnabled('system', 'jsm_soccerway'))
		{
			$this->document->addScript(Uri::base() . 'components/' . $this->option . '/views/club/tmpl/soccerway.js');
		}

		$params          = ComponentHelper::getParams($this->option);
		$opencagedataapi = $params->get('opencagedata_api_clientid');
        $auto_completion_club_name = $params->get('auto_completion_club_name');
        
        if ( $auto_completion_club_name && $this->item->founded_year && is_numeric($this->item->founded_year) )
		{
		if (preg_match("/".$this->item->founded_year."/i", $this->item->name ))
		{
		/** Es wurde eine Übereinstimmung gefunden */
		}
        else
        {
        $this->item->name = $this->item->name.' '.$this->item->founded_year.' e.V.'; 
        $this->form->setValue('name','', $this->item->name);   
        }  
        }
        
        if ( !$this->item->founded_year )
        {
            $this->item->founded_year = 'kein';
            $this->form->setValue('founded_year','', 'kein');
        }

//$this->app->enqueueMessage(Text::_($this->item->name), 'Error');
//$this->app->enqueueMessage(Text::_($this->item->founded_year), 'Error');

		if ($opencagedataapi)
		{
			$javascript = "\n";
			$javascript .= 'var opencage = "https://api.opencagedata.com/geocode/v1/json?key=' . $opencagedataapi . '&pretty=1&no_annotations=1&q=";' . "\n";
			$javascript .= 'var opencagekey = "' . $opencagedataapi . '";' . "\n";
			$javascript .= "\n";
			$this->document->addScriptDeclaration($javascript);
		}
		else
		{
			$javascript = "\n";
			$javascript .= 'var opencage = "";' . "\n";
			$javascript .= 'var opencagekey = "";' . "\n";
			$javascript .= "\n";
			$this->document->addScriptDeclaration($javascript);
		}


		
	}

	/**
	 * sportsmanagementViewClub::addToolBar()
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		$this->jinput->set('hidemainmenu', true);
		$isNew      = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_ADD_NEW');
		$this->icon = 'club';
		parent::addToolbar();
	}

}
