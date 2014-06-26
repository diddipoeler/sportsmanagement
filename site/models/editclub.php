<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

//jimport('joomla.application.component.model');
//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

//require_once(JPATH_COMPONENT.DS.'models'.DS.'item.php');
//require_once(JPATH_COMPONENT.DS.'helpers'.DS.'jltoolbar.php');

// Include dependancy of the main model form
jimport('joomla.application.component.modelform');


class sportsmanagementModelEditClub extends JModelForm
{
	
  /* interfaces */
	var $latitude	= null;
	var $longitude	= null;
	var $projectid = 0;
	var $clubid = 0;
	var $club = null;
  
  function __construct()
	{
	   $mainframe = JFactory::getApplication();
		parent::__construct();

		$this->projectid = JRequest::getInt( 'p', 0 );
		$this->clubid = JRequest::getInt( 'cid', 0 );
        $this->name = 'club';
        
	}
    
  function getClub()
	{
	   $mainframe = JFactory::getApplication();
		if ( is_null( $this->club  ) )
		{
			$this->club = $this->getTable( 'Club', 'sportsmanagementTable' );
			$this->club->load( $this->clubid );
		}
		return $this->club;
	}  


/**
         * Get the data for a new qualification
         */
        public function getForm($data = array(), $loadData = true)
        {
 
        $app = JFactory::getApplication('site');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' name<br><pre>'.print_r($this->name,true).'</pre>'),'Notice');
 
        // Get the form.
        JForm::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/forms');
        JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');
		$form = $this->loadForm('com_sportsmanagement.'.$this->name, $this->name,
				array('load_data' => $loadData) );
		if (empty($form))
		{
			return false;
		}
		return $form;
 
        } 

/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.7
	 */
	protected function loadFormData()
	{
	   $mainframe = JFactory::getApplication();
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.'.$this->name.'.data', array());
		if (empty($data))
		{
			$data = $this->getClub();
		}
		return $data;
	}		

	
	
	
	
//	/**
//	 * Fetch google map data refere to
//	 * http://code.google.com/apis/maps/documentation/geocoding/#Geocoding	 
//	 */	 	
//	public function getAddressData($address)
//	{
//
//		$url = 'http://maps.google.com/maps/api/geocode/json?' . 'address='.urlencode($address) .'&sensor=false&language=de';
//		$content = $this->getContent($url);
//		
//		$status = null;	
//		if(!empty($content))
//		{
//			$json = new Services_JSON();
//			$status = $json->decode($content);
//		}
//
//		return $status;
//	}
	
//	public function resolveLocation($address)
//	{
//		$mainframe = JFactory::getApplication();
//    $coords = array();
//		$data = $this->getAddressData($address);
//		//$mainframe->enqueueMessage(JText::_('google -> '.'<pre>'.print_r($data,true).'</pre>' ),'');
//		if($data){
//			if($data->status == 'OK')
//			{
//				$this->latitude  = $data->results[0]->geometry->location->lat;
//				$coords['latitude'] = $data->results[0]->geometry->location->lat; 
//				$this->longitude = $data->results[0]->geometry->location->lng;
//				$coords['longitude'] = $data->results[0]->geometry->location->lng;
//				
//				for ($a=0; $a < sizeof($data->results[0]->address_components); $a++ )
//				{
//        switch($data->results[0]->address_components[$a]->types[0])
//        {
//        case 'administrative_area_level_1':
//        $coords['COM_JOOMLEAGUE_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
//        $coords['COM_JOOMLEAGUE_ADMINISTRATIVE_AREA_LEVEL_1_SHORT_NAME'] = $data->results[0]->address_components[$a]->short_name;
//        break;
//        
//        case 'administrative_area_level_2':
//        $coords['COM_JOOMLEAGUE_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
//        $coords['COM_JOOMLEAGUE_ADMINISTRATIVE_AREA_LEVEL_2_SHORT_NAME'] = $data->results[0]->address_components[$a]->short_name;
//        break;
//        
//        case 'administrative_area_level_3':
//        $coords['COM_JOOMLEAGUE_ADMINISTRATIVE_AREA_LEVEL_3_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
//        $coords['COM_JOOMLEAGUE_ADMINISTRATIVE_AREA_LEVEL_3_SHORT_NAME'] = $data->results[0]->address_components[$a]->short_name;
//        break;
//
//        case 'locality':
//        $coords['COM_JOOMLEAGUE_LOCALITY_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
//        break;
//        
//        case 'sublocality':
//        $coords['COM_JOOMLEAGUE_SUBLOCALITY_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
//        break;
//                        
//        }
//                
//        
//        }
//				
//				
//				return $coords;
//			}
//		}
//	}
	
//		// Return content of the given url
//	static public function getContent($url , $raw = false , $headerOnly = false)
//	{
//		if (!$url)
//			return false;
//		
//		if (function_exists('curl_init'))
//		{
//			$ch			= curl_init();
//			curl_setopt($ch, CURLOPT_URL, $url);
//			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//			curl_setopt($ch, CURLOPT_HEADER, true );
//			
//			if($raw){
//				curl_setopt($ch, CURLOPT_BINARYTRANSFER, true );
//			}
//
//			$response	= curl_exec($ch);
//			
//			$curl_errno	= curl_errno($ch);
//			$curl_error	= curl_error($ch);
//			
//			if ($curl_errno!=0)
//			{
//				$mainframe	= JFactory::getApplication();
//				$err		= 'CURL error : '.$curl_errno.' '.$curl_error;
//				$mainframe->enqueueMessage($err, 'error');
//			}
//			
//			$code		= curl_getinfo( $ch , CURLINFO_HTTP_CODE );
//
//			// For redirects, we need to handle this properly instead of using CURLOPT_FOLLOWLOCATION
//			// as it doesn't work with safe_mode or openbase_dir set.
//			if( $code == 301 || $code == 302 )
//			{
//				list( $headers , $body ) = explode( "\r\n\r\n" , $response , 2 );
//				
//				preg_match( "/(Location:|URI:)(.*?)\n/" , $headers , $matches );
//				
//				if( !empty( $matches ) && isset( $matches[2] ) )
//				{
//					$url	= JString::trim( $matches[2] );
//					curl_setopt( $ch , CURLOPT_URL , $url );
//					curl_setopt( $ch , CURLOPT_RETURNTRANSFER, 1);
//					curl_setopt( $ch , CURLOPT_HEADER, true );
//					$response	= curl_exec( $ch );
//				}
//			}
//			
//			
//			if(!$raw){
//				list( $headers , $body )	= explode( "\r\n\r\n" , $response , 2 );
//			}
//			
//			$ret	= $raw ? $response : $body;
//			$ret	= $headerOnly ? $headers : $ret;
//			
//			curl_close($ch);
//			return $ret;
//		}
//	
//		// CURL unavailable on this install
//		return false;
//	}
	
	
	
	
	
	
}
?>