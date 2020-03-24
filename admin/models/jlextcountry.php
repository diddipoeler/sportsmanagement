<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jlextcountry.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage models
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper; 
use Joomla\CMS\Filesystem\File;
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');

/**
 * sportsmanagementModeljlextcountry
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModeljlextcountry extends JSMModelAdmin
{
    
    /**
     * sportsmanagementModeljlextcountry::importplz()
     * 
     * @return void
     */
    function importplz()
    {
    $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);    
        // Get the input
        $pks = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
        $base_Dir = JPATH_SITE .DIRECTORY_SEPARATOR. 'tmp' . DIRECTORY_SEPARATOR ;
        $cfg_plz_server = ComponentHelper::getParams($option)->get('cfg_plz_server','');
       
        for ($x=0; $x < count($pks); $x++)
		{
			$tblCountry = $this->getTable();
			$tblCountry->load($pks[$x]);
            $alpha2 = $tblCountry->alpha2;
            $filename = $alpha2.'.zip';
            $linkaddress = $cfg_plz_server.$filename;
            $filepath = $base_Dir . $filename;

if ( !copy($linkaddress,$filepath) )
{
$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_COPY_PLZ_ERROR'),'Error');
}
else
{
$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_COPY_PLZ_SUCCESS'),'Notice'); 
$result = JArchive::extract($filepath,$base_Dir);  

if ( $result )
{
$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_COPY_PLZ_ZIP_SUCCESS'),'Notice'); 

$file = $base_Dir.$alpha2.'.txt';



$source	= File::read($file);



//# tab delimited, and encoding conversion
	$csv = new JSMparseCSV();
	//$csv->encoding('UTF-16', 'UTF-8');
	$csv->delimiter = "\t";
    $csv->heading = false;
    $csv->parse($source);
    
    $diddipoeler = 0;
    
    foreach ($csv->data as $key => $row)
	{
	   $temp = new stdClass();
		$temp->id = $key;
	    
        if ( !$diddipoeler )
        {

        }
		
        for ($a=0; $a < count($row); $a++)
		{
		switch ($a)
        {
            case 0:
            $temp->country_code = $row[$a];
            break;
            case 1:
            $temp->postal_code = $row[$a];
            break;
            case 2:
            $temp->place_name = $row[$a];
            break;
            
            case 3:
            $temp->admin_name1 = $row[$a];
            break;
            case 4:
            $temp->admin_code1 = $row[$a];
            break;
            case 5:
            $temp->admin_name2 = $row[$a];
            break;
            
            case 9:
            $temp->latitude = $row[$a];
            break;
            case 10:
            $temp->longitude = $row[$a];
            break;
            case 11:
            $temp->accuracy = $row[$a];
            break;
        }  
        }  

	    $exportplayer[] = $temp;
        
        $diddipoeler++;
	}
    
    foreach ($exportplayer as $value)
		{
        $profile = new stdClass();
        $profile->country_code = $value->country_code;
            $profile->postal_code = $value->postal_code;
            $profile->place_name = $value->place_name;
            $profile->admin_name1 = $value->admin_name1;
            $profile->admin_code1 = $value->admin_code1;
            $profile->admin_name2 = $value->admin_name2;
            $profile->latitude = $value->latitude;
            $profile->longitude = $value->longitude;
            $profile->accuracy = $value->accuracy;
        // Insert the object into the table.
        $result = Factory::getDbo()->insertObject('#__sportsmanagement_countries_plz', $profile);  
        }  
}
else
{
$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_COPY_PLZ_ZIP_ERROR'),'Error');    
}


 
}         
		}
        
    }  
	
}
