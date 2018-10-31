<?php
/** SportsManagement ein Programm zur Verwaltung f�r Sportarten
 * @version   1.0.05
 * @file      googletimezones.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;

JLoader::import('joomla.html.html');
JLoader::import('joomla.form.formfield');
JLoader::import('joomla.form.helper');
FormHelper::loadFieldClass('list');

/**
 * FormFieldGoogletimezones
 * 
 * @package 
 * @author Dieter Pl�ger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class JFormFieldGoogletimezones extends \JFormFieldList
{
	protected $type  = 'Googletimezones';

	/**
	 * FormFieldGoogletimezones::getOptions()
	 * 
	 * @return
	 */
	function getOptions()
	{
		$timezones = array (
		HTMLHelper::_('select.option','', Text::_('')),
		HTMLHelper::_('select.option','Pacific/Apia', Text::_('(GMT-11:00) Apia')),
		HTMLHelper::_('select.option','Pacific/Midway', Text::_('(GMT-11:00) Midway')),
		HTMLHelper::_('select.option','Pacific/Niue', Text::_('(GMT-11:00) Niue')),
		HTMLHelper::_('select.option','Pacific/Pago_Pago', Text::_('(GMT-11:00) Pago Pago')),
		HTMLHelper::_('select.option','Pacific/Fakaofo', Text::_('(GMT-10:00) Fakaofo')),
		HTMLHelper::_('select.option','Pacific/Honolulu', Text::_('(GMT-10:00) Hawaii Time')),
		HTMLHelper::_('select.option','Pacific/Johnston', Text::_('(GMT-10:00) Johnston')),
		HTMLHelper::_('select.option','Pacific/Rarotonga', Text::_('(GMT-10:00) Rarotonga')),
		HTMLHelper::_('select.option','Pacific/Tahiti', Text::_('(GMT-10:00) Tahiti')),
		HTMLHelper::_('select.option','Pacific/Marquesas', Text::_('(GMT-09:30) Marquesas')),
		HTMLHelper::_('select.option','America/Anchorage', Text::_('(GMT-09:00) Alaska Time')),
		HTMLHelper::_('select.option','Pacific/Gambier', Text::_('(GMT-09:00) Gambier')),
		HTMLHelper::_('select.option','America/Los_Angeles', Text::_('(GMT-08:00) Pacific Time')),
		HTMLHelper::_('select.option','America/Tijuana', Text::_('(GMT-08:00) Pacific Time - Tijuana')),
		HTMLHelper::_('select.option','America/Vancouver', Text::_('(GMT-08:00) Pacific Time - Vancouver')),
		HTMLHelper::_('select.option','America/Whitehorse', Text::_('(GMT-08:00) Pacific Time - Whitehorse')),
		HTMLHelper::_('select.option','Pacific/Pitcairn', Text::_('(GMT-08:00) Pitcairn')),
		HTMLHelper::_('select.option','America/Dawson_Creek', Text::_('(GMT-07:00) Mountain Time - Dawson Creek')),
		HTMLHelper::_('select.option','America/Denver', Text::_('(GMT-07:00) Mountain Time (America/Denver)')),
		HTMLHelper::_('select.option','America/Edmonton', Text::_('(GMT-07:00) Mountain Time - Edmonton')),
		HTMLHelper::_('select.option','America/Hermosillo', Text::_('(GMT-07:00) Mountain Time - Hermosillo')),
		HTMLHelper::_('select.option','America/Mazatlan', Text::_('(GMT-07:00) Mountain Time - Chihuahua, Mazatlan')),
		HTMLHelper::_('select.option','America/Phoenix', Text::_('(GMT-07:00) Mountain Time - Arizona')),
		HTMLHelper::_('select.option','America/Yellowknife', Text::_('(GMT-07:00) Mountain Time - Yellowknife')),
		HTMLHelper::_('select.option','America/Belize', Text::_('(GMT-06:00) Belize')),
		HTMLHelper::_('select.option','America/Chicago', Text::_('(GMT-06:00) Central Time')),
		HTMLHelper::_('select.option','America/Costa_Rica', Text::_('(GMT-06:00) Costa Rica')),
		HTMLHelper::_('select.option','America/El_Salvador', Text::_('(GMT-06:00) El Salvador')),
		HTMLHelper::_('select.option','America/Guatemala', Text::_('(GMT-06:00) Guatemala')),
		HTMLHelper::_('select.option','America/Managua', Text::_('(GMT-06:00) Managua')),
		HTMLHelper::_('select.option','America/Mexico_City', Text::_('(GMT-06:00) Central Time - Mexico City')),
		HTMLHelper::_('select.option','America/Regina', Text::_('(GMT-06:00) Central Time - Regina')),
		HTMLHelper::_('select.option','America/Tegucigalpa', Text::_('(GMT-06:00) Central Time (America/Tegucigalpa)')),
		HTMLHelper::_('select.option','America/Winnipeg', Text::_('(GMT-06:00) Central Time - Winnipeg')),
		HTMLHelper::_('select.option','Pacific/Easter', Text::_('(GMT-06:00) Easter Island')),
		HTMLHelper::_('select.option','Pacific/Galapagos', Text::_('(GMT-06:00) Galapagos')),
		HTMLHelper::_('select.option','America/Bogota', Text::_('(GMT-05:00) Bogota')),
		HTMLHelper::_('select.option','America/Cayman', Text::_('(GMT-05:00) Cayman')),
		HTMLHelper::_('select.option','America/Grand_Turk', Text::_('(GMT-05:00) Grand Turk')),
		HTMLHelper::_('select.option','America/Guayaquil', Text::_('(GMT-05:00) Guayaquil')),
		HTMLHelper::_('select.option','America/Havana', Text::_('(GMT-05:00) Havana')),
		HTMLHelper::_('select.option','America/Iqaluit', Text::_('(GMT-05:00) Eastern Time - Iqaluit')),
		HTMLHelper::_('select.option','America/Jamaica', Text::_('(GMT-05:00) Jamaica')),
		HTMLHelper::_('select.option','America/Lima', Text::_('(GMT-05:00) Lima')),
		HTMLHelper::_('select.option','America/Montreal', Text::_('(GMT-05:00) Eastern Time - Montreal')),
		HTMLHelper::_('select.option','America/Nassau', Text::_('(GMT-05:00) Nassau')),
		HTMLHelper::_('select.option','America/New_York', Text::_('(GMT-05:00) Eastern Time')),
		HTMLHelper::_('select.option','America/Panama', Text::_('(GMT-05:00) Panama')),
		HTMLHelper::_('select.option','America/Port-au-Prince', Text::_('(GMT-05:00) Port-au-Prince')),
		HTMLHelper::_('select.option','America/Toronto', Text::_('(GMT-05:00) Eastern Time - Toronto')),
		HTMLHelper::_('select.option','America/Caracas', Text::_('(GMT-04:30) Caracas')),
		HTMLHelper::_('select.option','America/Anguilla', Text::_('(GMT-04:00) Anguilla')),
		HTMLHelper::_('select.option','America/Antigua', Text::_('(GMT-04:00) Antigua')),
		HTMLHelper::_('select.option','America/Aruba', Text::_('(GMT-04:00) Aruba')),
		HTMLHelper::_('select.option','America/Asuncion', Text::_('(GMT-04:00) Asuncion')),
		HTMLHelper::_('select.option','America/Barbados', Text::_('(GMT-04:00) Barbados')),
		HTMLHelper::_('select.option','America/Boa_Vista', Text::_('(GMT-04:00) Boa Vista')),
		HTMLHelper::_('select.option','America/Campo_Grande', Text::_('(GMT-04:00) Campo Grande')),
		HTMLHelper::_('select.option','America/Cuiaba', Text::_('(GMT-04:00) Cuiaba')),
		HTMLHelper::_('select.option','America/Curacao', Text::_('(GMT-04:00) Curacao')),
		HTMLHelper::_('select.option','America/Dominica', Text::_('(GMT-04:00) Dominica')),
		HTMLHelper::_('select.option','America/Grenada', Text::_('(GMT-04:00) Grenada')),
		HTMLHelper::_('select.option','America/Guadeloupe', Text::_('(GMT-04:00) Guadeloupe')),
		HTMLHelper::_('select.option','America/Guyana', Text::_('(GMT-04:00) Guyana')),
		HTMLHelper::_('select.option','America/Halifax', Text::_('(GMT-04:00) Atlantic Time - Halifax')),
		HTMLHelper::_('select.option','America/La_Paz', Text::_('(GMT-04:00) La Paz')),
		HTMLHelper::_('select.option','America/Manaus', Text::_('(GMT-04:00) Manaus')),
		HTMLHelper::_('select.option','America/Martinique', Text::_('(GMT-04:00) Martinique')),
		HTMLHelper::_('select.option','America/Montserrat', Text::_('(GMT-04:00) Montserrat')),
		HTMLHelper::_('select.option','America/Port_of_Spain', Text::_('(GMT-04:00) Port of Spain')),
		HTMLHelper::_('select.option','America/Porto_Velho', Text::_('(GMT-04:00) Porto Velho')),
		HTMLHelper::_('select.option','America/Puerto_Rico', Text::_('(GMT-04:00) Puerto Rico')),
		HTMLHelper::_('select.option','America/Rio_Branco', Text::_('(GMT-04:00) Rio Branco')),
		HTMLHelper::_('select.option','America/Santiago', Text::_('(GMT-04:00) Santiago')),
		HTMLHelper::_('select.option','America/Santo_Domingo', Text::_('(GMT-04:00) Santo Domingo')),
		HTMLHelper::_('select.option','America/St_Kitts', Text::_('(GMT-04:00) St. Kitts')),
		HTMLHelper::_('select.option','America/St_Lucia', Text::_('(GMT-04:00) St. Lucia')),
		HTMLHelper::_('select.option','America/St_Thomas', Text::_('(GMT-04:00) St. Thomas')),
		HTMLHelper::_('select.option','America/St_Vincent', Text::_('(GMT-04:00) St. Vincent')),
		HTMLHelper::_('select.option','America/Thule', Text::_('(GMT-04:00) Thule')),
		HTMLHelper::_('select.option','America/Tortola', Text::_('(GMT-04:00) Tortola')),
		HTMLHelper::_('select.option','Antarctica/Palmer', Text::_('(GMT-04:00) Palmer')),
		HTMLHelper::_('select.option','Atlantic/Bermuda', Text::_('(GMT-04:00) Bermuda')),
		HTMLHelper::_('select.option','Atlantic/Stanley', Text::_('(GMT-04:00) Stanley')),
		HTMLHelper::_('select.option','America/St_Johns', Text::_('(GMT-03:30) Newfoundland Time - St. Johns')),
		HTMLHelper::_('select.option','America/Araguaina', Text::_('(GMT-03:00) Araguaina')),
		HTMLHelper::_('select.option','America/Argentina/Buenos_Aires', Text::_('(GMT-03:00) Buenos Aires')),
		HTMLHelper::_('select.option','America/Bahia', Text::_('(GMT-03:00) Salvador')),
		HTMLHelper::_('select.option','America/Belem', Text::_('(GMT-03:00) Belem')),
		HTMLHelper::_('select.option','America/Cayenne', Text::_('(GMT-03:00) Cayenne')),
		HTMLHelper::_('select.option','America/Fortaleza', Text::_('(GMT-03:00) Fortaleza')),
		HTMLHelper::_('select.option','America/Godthab', Text::_('(GMT-03:00) Godthab')),
		HTMLHelper::_('select.option','America/Maceio', Text::_('(GMT-03:00) Maceio')),
		HTMLHelper::_('select.option','America/Miquelon', Text::_('(GMT-03:00) Miquelon')),
		HTMLHelper::_('select.option','America/Montevideo', Text::_('(GMT-03:00) Montevideo')),
		HTMLHelper::_('select.option','America/Paramaribo', Text::_('(GMT-03:00) Paramaribo')),
		HTMLHelper::_('select.option','America/Recife', Text::_('(GMT-03:00) Recife')),
		HTMLHelper::_('select.option','America/Sao_Paulo', Text::_('(GMT-03:00) Sao Paulo')),
		HTMLHelper::_('select.option','Antarctica/Rothera', Text::_('(GMT-03:00) Rothera')),
		HTMLHelper::_('select.option','America/Noronha', Text::_('(GMT-02:00) Noronha')),
		HTMLHelper::_('select.option','Atlantic/South_Georgia', Text::_('(GMT-02:00) South Georgia')),
		HTMLHelper::_('select.option','America/Scoresbysund', Text::_('(GMT-01:00) Scoresbysund')),
		HTMLHelper::_('select.option','Atlantic/Azores', Text::_('(GMT-01:00) Azores')),
		HTMLHelper::_('select.option','Atlantic/Cape_Verde', Text::_('(GMT-01:00) Cape Verde')),
		HTMLHelper::_('select.option','Africa/Abidjan', Text::_('(GMT+00:00) Abidjan')),
		HTMLHelper::_('select.option','Africa/Accra', Text::_('(GMT+00:00) Accra')),
		HTMLHelper::_('select.option','Africa/Bamako', Text::_('(GMT+00:00) Bamako')),
		HTMLHelper::_('select.option','Africa/Banjul', Text::_('(GMT+00:00) Banjul')),
		HTMLHelper::_('select.option','Africa/Bissau', Text::_('(GMT+00:00) Bissau')),
		HTMLHelper::_('select.option','Africa/Casablanca', Text::_('(GMT+00:00) Casablanca')),
		HTMLHelper::_('select.option','Africa/Conakry', Text::_('(GMT+00:00) Conakry')),
		HTMLHelper::_('select.option','Africa/Dakar', Text::_('(GMT+00:00) Dakar')),
		HTMLHelper::_('select.option','Africa/El_Aaiun', Text::_('(GMT+00:00) El Aaiun')),
		HTMLHelper::_('select.option','Africa/Freetown', Text::_('(GMT+00:00) Freetown')),
		HTMLHelper::_('select.option','Africa/Lome', Text::_('(GMT+00:00) Lome')),
		HTMLHelper::_('select.option','Africa/Monrovia', Text::_('(GMT+00:00) Monrovia')),
		HTMLHelper::_('select.option','Africa/Nouakchott', Text::_('(GMT+00:00) Nouakchott')),
		HTMLHelper::_('select.option','Africa/Ouagadougou', Text::_('(GMT+00:00) Ouagadougou')),
		HTMLHelper::_('select.option','Africa/Sao_Tome', Text::_('(GMT+00:00) Sao Tome')),
		HTMLHelper::_('select.option','America/Danmarkshavn', Text::_('(GMT+00:00) Danmarkshavn')),
		HTMLHelper::_('select.option','Atlantic/Canary', Text::_('(GMT+00:00) Canary Islands')),
		HTMLHelper::_('select.option','Atlantic/Faroe', Text::_('(GMT+00:00) Faeroe')),
		HTMLHelper::_('select.option','Atlantic/Reykjavik', Text::_('(GMT+00:00) Reykjavik')),
		HTMLHelper::_('select.option','Atlantic/St_Helena', Text::_('(GMT+00:00) St Helena')),
		HTMLHelper::_('select.option','Etc/GMT', Text::_('(GMT+00:00) GMT (no daylight saving)')),
		HTMLHelper::_('select.option','Europe/Dublin', Text::_('(GMT+00:00) Dublin')),
		HTMLHelper::_('select.option','Europe/Lisbon', Text::_('(GMT+00:00) Lisbon')),
		HTMLHelper::_('select.option','Europe/London', Text::_('(GMT+00:00) London')),
		HTMLHelper::_('select.option','Africa/Algiers', Text::_('(GMT+01:00) Algiers')),
		HTMLHelper::_('select.option','Africa/Bangui', Text::_('(GMT+01:00) Bangui')),
		HTMLHelper::_('select.option','Africa/Brazzaville', Text::_('(GMT+01:00) Brazzaville')),
		HTMLHelper::_('select.option','Africa/Ceuta', Text::_('(GMT+01:00) Ceuta')),
		HTMLHelper::_('select.option','Africa/Douala', Text::_('(GMT+01:00) Douala')),
		HTMLHelper::_('select.option','Africa/Kinshasa', Text::_('(GMT+01:00) Kinshasa')),
		HTMLHelper::_('select.option','Africa/Lagos', Text::_('(GMT+01:00) Lagos')),
		HTMLHelper::_('select.option','Africa/Libreville', Text::_('(GMT+01:00) Libreville')),
		HTMLHelper::_('select.option','Africa/Luanda', Text::_('(GMT+01:00) Luanda')),
		HTMLHelper::_('select.option','Africa/Malabo', Text::_('(GMT+01:00) Malabo')),
		HTMLHelper::_('select.option','Africa/Ndjamena', Text::_('(GMT+01:00) Ndjamena')),
		HTMLHelper::_('select.option','Africa/Niamey', Text::_('(GMT+01:00) Niamey')),
		HTMLHelper::_('select.option','Africa/Porto-Novo', Text::_('(GMT+01:00) Porto-Novo')),
		HTMLHelper::_('select.option','Africa/Tunis', Text::_('(GMT+01:00) Tunis')),
		HTMLHelper::_('select.option','Africa/Windhoek', Text::_('(GMT+01:00) Windhoek')),
		HTMLHelper::_('select.option','Europe/Amsterdam', Text::_('(GMT+01:00) Amsterdam')),
		HTMLHelper::_('select.option','Europe/Andorra', Text::_('(GMT+01:00) Andorra')),
		HTMLHelper::_('select.option','Europe/Belgrade', Text::_('(GMT+01:00) Central European Time (Europe/Belgrade)')),
		HTMLHelper::_('select.option','Europe/Berlin', Text::_('(GMT+01:00) Berlin')),
		HTMLHelper::_('select.option','Europe/Brussels', Text::_('(GMT+01:00) Brussels')),
		HTMLHelper::_('select.option','Europe/Budapest', Text::_('(GMT+01:00) Budapest')),
		HTMLHelper::_('select.option','Europe/Copenhagen', Text::_('(GMT+01:00) Copenhagen')),
		HTMLHelper::_('select.option','Europe/Gibraltar', Text::_('(GMT+01:00) Gibraltar')),
		HTMLHelper::_('select.option','Europe/Luxembourg', Text::_('(GMT+01:00) Luxembourg')),
		HTMLHelper::_('select.option','Europe/Madrid', Text::_('(GMT+01:00) Madrid')),
		HTMLHelper::_('select.option','Europe/Malta', Text::_('(GMT+01:00) Malta')),
		HTMLHelper::_('select.option','Europe/Monaco', Text::_('(GMT+01:00) Monaco')),
		HTMLHelper::_('select.option','Europe/Oslo', Text::_('(GMT+01:00) Oslo')),
		HTMLHelper::_('select.option','Europe/Paris', Text::_('(GMT+01:00) Paris')),
		HTMLHelper::_('select.option','Europe/Prague', Text::_('(GMT+01:00) Central European Time (Europe/Prague)')),
		HTMLHelper::_('select.option','Europe/Rome', Text::_('(GMT+01:00) Rome')),
		HTMLHelper::_('select.option','Europe/Stockholm', Text::_('(GMT+01:00) Stockholm')),
		HTMLHelper::_('select.option','Europe/Tirane', Text::_('(GMT+01:00) Tirane')),
		HTMLHelper::_('select.option','Europe/Vaduz', Text::_('(GMT+01:00) Vaduz')),
		HTMLHelper::_('select.option','Europe/Vienna', Text::_('(GMT+01:00) Vienna')),
		HTMLHelper::_('select.option','Europe/Warsaw', Text::_('(GMT+01:00) Warsaw')),
		HTMLHelper::_('select.option','Europe/Zurich', Text::_('(GMT+01:00) Zurich')),
		HTMLHelper::_('select.option','Africa/Blantyre', Text::_('(GMT+02:00) Blantyre')),
		HTMLHelper::_('select.option','Africa/Bujumbura', Text::_('(GMT+02:00) Bujumbura')),
		HTMLHelper::_('select.option','Africa/Cairo', Text::_('(GMT+02:00) Cairo')),
		HTMLHelper::_('select.option','Africa/Gaborone', Text::_('(GMT+02:00) Gaborone')),
		HTMLHelper::_('select.option','Africa/Harare', Text::_('(GMT+02:00) Harare')),
		HTMLHelper::_('select.option','Africa/Johannesburg', Text::_('(GMT+02:00) Johannesburg')),
		HTMLHelper::_('select.option','Africa/Kigali', Text::_('(GMT+02:00) Kigali')),
		HTMLHelper::_('select.option','Africa/Lubumbashi', Text::_('(GMT+02:00) Lubumbashi')),
		HTMLHelper::_('select.option','Africa/Lusaka', Text::_('(GMT+02:00) Lusaka')),
		HTMLHelper::_('select.option','Africa/Maputo', Text::_('(GMT+02:00) Maputo')),
		HTMLHelper::_('select.option','Africa/Maseru', Text::_('(GMT+02:00) Maseru')),
		HTMLHelper::_('select.option','Africa/Mbabane', Text::_('(GMT+02:00) Mbabane')),
		HTMLHelper::_('select.option','Africa/Tripoli', Text::_('(GMT+02:00) Tripoli')),
		HTMLHelper::_('select.option','Asia/Amman', Text::_('(GMT+02:00) Amman')),
		HTMLHelper::_('select.option','Asia/Beirut', Text::_('(GMT+02:00) Beirut')),
		HTMLHelper::_('select.option','Asia/Damascus', Text::_('(GMT+02:00) Damascus')),
		HTMLHelper::_('select.option','Asia/Gaza', Text::_('(GMT+02:00) Gaza')),
		HTMLHelper::_('select.option','Asia/Jerusalem', Text::_('(GMT+02:00) Jerusalem')),
		HTMLHelper::_('select.option','Asia/Nicosia', Text::_('(GMT+02:00) Nicosia')),
		HTMLHelper::_('select.option','Europe/Athens', Text::_('(GMT+02:00) Athens')),
		HTMLHelper::_('select.option','Europe/Bucharest', Text::_('(GMT+02:00) Bucharest')),
		HTMLHelper::_('select.option','Europe/Chisinau', Text::_('(GMT+02:00) Chisinau')),
		HTMLHelper::_('select.option','Europe/Helsinki', Text::_('(GMT+02:00) Helsinki')),
		HTMLHelper::_('select.option','Europe/Istanbul', Text::_('(GMT+02:00) Istanbul')),
		HTMLHelper::_('select.option','Europe/Kaliningrad', Text::_('(GMT+02:00) Moscow-01 - Kaliningrad')),
		HTMLHelper::_('select.option','Europe/Kiev', Text::_('(GMT+02:00) Kiev')),
		HTMLHelper::_('select.option','Europe/Minsk', Text::_('(GMT+02:00) Minsk')),
		HTMLHelper::_('select.option','Europe/Riga', Text::_('(GMT+02:00) Riga')),
		HTMLHelper::_('select.option','Europe/Sofia', Text::_('(GMT+02:00) Sofia')),
		HTMLHelper::_('select.option','Europe/Tallinn', Text::_('(GMT+02:00) Tallinn')),
		HTMLHelper::_('select.option','Europe/Vilnius', Text::_('(GMT+02:00) Vilnius')),
		HTMLHelper::_('select.option','Africa/Addis_Ababa', Text::_('(GMT+03:00) Addis Ababa')),
		HTMLHelper::_('select.option','Africa/Asmara', Text::_('(GMT+03:00) Asmera')),
		HTMLHelper::_('select.option','Africa/Dar_es_Salaam', Text::_('(GMT+03:00) Dar es Salaam')),
		HTMLHelper::_('select.option','Africa/Djibouti', Text::_('(GMT+03:00) Djibouti')),
		HTMLHelper::_('select.option','Africa/Kampala', Text::_('(GMT+03:00) Kampala')),
		HTMLHelper::_('select.option','Africa/Khartoum', Text::_('(GMT+03:00) Khartoum')),
		HTMLHelper::_('select.option','Africa/Mogadishu', Text::_('(GMT+03:00) Mogadishu')),
		HTMLHelper::_('select.option','Africa/Nairobi', Text::_('(GMT+03:00) Nairobi')),
		HTMLHelper::_('select.option','Antarctica/Syowa', Text::_('(GMT+03:00) Syowa')),
		HTMLHelper::_('select.option','Asia/Aden', Text::_('(GMT+03:00) Aden')),
		HTMLHelper::_('select.option','Asia/Baghdad', Text::_('(GMT+03:00) Baghdad')),
		HTMLHelper::_('select.option','Asia/Bahrain', Text::_('(GMT+03:00) Bahrain')),
		HTMLHelper::_('select.option','Asia/Kuwait', Text::_('(GMT+03:00) Kuwait')),
		HTMLHelper::_('select.option','Asia/Qatar', Text::_('(GMT+03:00) Qatar')),
		HTMLHelper::_('select.option','Asia/Riyadh', Text::_('(GMT+03:00) Riyadh')),
		HTMLHelper::_('select.option','Europe/Moscow', Text::_('(GMT+03:00) Moscow+00')),
		HTMLHelper::_('select.option','Indian/Antananarivo', Text::_('(GMT+03:00) Antananarivo')),
		HTMLHelper::_('select.option','Indian/Comoro', Text::_('(GMT+03:00) Comoro')),
		HTMLHelper::_('select.option','Indian/Mayotte', Text::_('(GMT+03:00) Mayotte')),
		HTMLHelper::_('select.option','Asia/Tehran', Text::_('(GMT+03:30) Tehran')),
		HTMLHelper::_('select.option','Asia/Baku', Text::_('(GMT+04:00) Baku')),
		HTMLHelper::_('select.option','Asia/Dubai', Text::_('(GMT+04:00) Dubai')),
		HTMLHelper::_('select.option','Asia/Muscat', Text::_('(GMT+04:00) Muscat')),
		HTMLHelper::_('select.option','Asia/Tbilisi', Text::_('(GMT+04:00) Tbilisi')),
		HTMLHelper::_('select.option','Asia/Yerevan', Text::_('(GMT+04:00) Yerevan')),
		HTMLHelper::_('select.option','Europe/Samara', Text::_('(GMT+04:00) Moscow+01 - Samara')),
		HTMLHelper::_('select.option','Indian/Mahe', Text::_('(GMT+04:00) Mahe')),
		HTMLHelper::_('select.option','Indian/Mauritius', Text::_('(GMT+04:00) Mauritius')),
		HTMLHelper::_('select.option','Indian/Reunion', Text::_('(GMT+04:00) Reunion')),
		HTMLHelper::_('select.option','Asia/Kabul', Text::_('(GMT+04:30) Kabul')),
		HTMLHelper::_('select.option','Asia/Aqtau', Text::_('(GMT+05:00) Aqtau')),
		HTMLHelper::_('select.option','Asia/Aqtobe', Text::_('(GMT+05:00) Aqtobe')),
		HTMLHelper::_('select.option','Asia/Ashgabat', Text::_('(GMT+05:00) Ashgabat')),
		HTMLHelper::_('select.option','Asia/Dushanbe', Text::_('(GMT+05:00) Dushanbe')),
		HTMLHelper::_('select.option','Asia/Karachi', Text::_('(GMT+05:00) Karachi')),
		HTMLHelper::_('select.option','Asia/Tashkent', Text::_('(GMT+05:00) Tashkent')),
		HTMLHelper::_('select.option','Asia/Yekaterinburg', Text::_('(GMT+05:00) Moscow+02 - Yekaterinburg')),
		HTMLHelper::_('select.option','Indian/Kerguelen', Text::_('(GMT+05:00) Kerguelen')),
		HTMLHelper::_('select.option','Indian/Maldives', Text::_('(GMT+05:00) Maldives')),
		HTMLHelper::_('select.option','Asia/Calcutta', Text::_('(GMT+05:30) India Standard Time')),
		HTMLHelper::_('select.option','Asia/Colombo', Text::_('(GMT+05:30) Colombo')),
		HTMLHelper::_('select.option','Asia/Katmandu', Text::_('(GMT+05:45) Katmandu')),
		HTMLHelper::_('select.option','Antarctica/Mawson', Text::_('(GMT+06:00) Mawson')),
		HTMLHelper::_('select.option','Antarctica/Vostok', Text::_('(GMT+06:00) Vostok')),
		HTMLHelper::_('select.option','Asia/Almaty', Text::_('(GMT+06:00) Almaty')),
		HTMLHelper::_('select.option','Asia/Bishkek', Text::_('(GMT+06:00) Bishkek')),
		HTMLHelper::_('select.option','Asia/Dhaka', Text::_('(GMT+06:00) Dhaka')),
		HTMLHelper::_('select.option','Asia/Omsk', Text::_('(GMT+06:00) Moscow+03 - Omsk, Novosibirsk')),
		HTMLHelper::_('select.option','Asia/Thimphu', Text::_('(GMT+06:00) Thimphu')),
		HTMLHelper::_('select.option','Indian/Chagos', Text::_('(GMT+06:00) Chagos')),
		HTMLHelper::_('select.option','Asia/Rangoon', Text::_('(GMT+06:30) Rangoon')),
		HTMLHelper::_('select.option','Indian/Cocos', Text::_('(GMT+06:30) Cocos')),
		HTMLHelper::_('select.option','Antarctica/Davis', Text::_('(GMT+07:00) Davis')),
		HTMLHelper::_('select.option','Asia/Bangkok', Text::_('(GMT+07:00) Bangkok')),
		HTMLHelper::_('select.option','Asia/Hovd', Text::_('(GMT+07:00) Hovd')),
		HTMLHelper::_('select.option','Asia/Jakarta', Text::_('(GMT+07:00) Jakarta')),
		HTMLHelper::_('select.option','Asia/Krasnoyarsk', Text::_('(GMT+07:00) Moscow+04 - Krasnoyarsk')),
		HTMLHelper::_('select.option','Asia/Phnom_Penh', Text::_('(GMT+07:00) Phnom Penh')),
		HTMLHelper::_('select.option','Asia/Saigon', Text::_('(GMT+07:00) Hanoi')),
		HTMLHelper::_('select.option','Asia/Vientiane', Text::_('(GMT+07:00) Vientiane')),
		HTMLHelper::_('select.option','Indian/Christmas', Text::_('(GMT+07:00) Christmas')),
		HTMLHelper::_('select.option','Antarctica/Casey', Text::_('(GMT+08:00) Casey')),
		HTMLHelper::_('select.option','Asia/Brunei', Text::_('(GMT+08:00) Brunei')),
		HTMLHelper::_('select.option','Asia/Choibalsan', Text::_('(GMT+08:00) Choibalsan')),
		HTMLHelper::_('select.option','Asia/Hong_Kong', Text::_('(GMT+08:00) Hong Kong')),
		HTMLHelper::_('select.option','Asia/Irkutsk', Text::_('(GMT+08:00) Moscow+05 - Irkutsk')),
		HTMLHelper::_('select.option','Asia/Kuala_Lumpur', Text::_('(GMT+08:00) Kuala Lumpur')),
		HTMLHelper::_('select.option','Asia/Macau', Text::_('(GMT+08:00) Macau')),
		HTMLHelper::_('select.option','Asia/Makassar', Text::_('(GMT+08:00) Makassar')),
		HTMLHelper::_('select.option','Asia/Manila', Text::_('(GMT+08:00) Manila')),
		HTMLHelper::_('select.option','Asia/Shanghai', Text::_('(GMT+08:00) China Time - Beijing')),
		HTMLHelper::_('select.option','Asia/Singapore', Text::_('(GMT+08:00) Singapore')),
		HTMLHelper::_('select.option','Asia/Taipei', Text::_('(GMT+08:00) Taipei')),
		HTMLHelper::_('select.option','Asia/Ulaanbaatar', Text::_('(GMT+08:00) Ulaanbaatar')),
		HTMLHelper::_('select.option','Australia/Perth', Text::_('(GMT+08:00) Western Time - Perth')),
		HTMLHelper::_('select.option','Asia/Dili', Text::_('(GMT+09:00) Dili')),
		HTMLHelper::_('select.option','Asia/Jayapura', Text::_('(GMT+09:00) Jayapura')),
		HTMLHelper::_('select.option','Asia/Pyongyang', Text::_('(GMT+09:00) Pyongyang')),
		HTMLHelper::_('select.option','Asia/Seoul', Text::_('(GMT+09:00) Seoul')),
		HTMLHelper::_('select.option','Asia/Tokyo', Text::_('(GMT+09:00) Tokyo')),
		HTMLHelper::_('select.option','Asia/Yakutsk', Text::_('(GMT+09:00) Moscow+06 - Yakutsk')),
		HTMLHelper::_('select.option','Pacific/Palau', Text::_('(GMT+09:00) Palau')),
		HTMLHelper::_('select.option','Australia/Adelaide', Text::_('(GMT+09:30) Central Time - Adelaide')),
		HTMLHelper::_('select.option','Australia/Darwin', Text::_('(GMT+09:30) Central Time - Darwin')),
		HTMLHelper::_('select.option','Antarctica/DumontDUrville', Text::_('(GMT+10:00) Dumont D\'Urville')),
		HTMLHelper::_('select.option','Asia/Vladivostok', Text::_('(GMT+10:00) Moscow+07 - Yuzhno-Sakhalinsk')),
		HTMLHelper::_('select.option','Australia/Brisbane', Text::_('(GMT+10:00) Eastern Time - Brisbane')),
		HTMLHelper::_('select.option','Australia/Hobart', Text::_('(GMT+10:00) Eastern Time - Hobart')),
		HTMLHelper::_('select.option','Australia/Sydney', Text::_('(GMT+10:00) Eastern Time - Melbourne, Sydney')),
		HTMLHelper::_('select.option','Pacific/Guam', Text::_('(GMT+10:00) Guam')),
		HTMLHelper::_('select.option','Pacific/Port_Moresby', Text::_('(GMT+10:00) Port Moresby')),
		HTMLHelper::_('select.option','Pacific/Saipan', Text::_('(GMT+10:00) Saipan')),
		HTMLHelper::_('select.option','Pacific/Truk', Text::_('(GMT+10:00) Truk')),
		HTMLHelper::_('select.option','Asia/Magadan', Text::_('(GMT+11:00) Moscow+08 - Magadan')),
		HTMLHelper::_('select.option','Pacific/Efate', Text::_('(GMT+11:00) Efate')),
		HTMLHelper::_('select.option','Pacific/Guadalcanal', Text::_('(GMT+11:00) Guadalcanal')),
		HTMLHelper::_('select.option','Pacific/Kosrae', Text::_('(GMT+11:00) Kosrae')),
		HTMLHelper::_('select.option','Pacific/Noumea', Text::_('(GMT+11:00) Noumea')),
		HTMLHelper::_('select.option','Pacific/Ponape', Text::_('(GMT+11:00) Ponape')),
		HTMLHelper::_('select.option','Pacific/Norfolk', Text::_('(GMT+11:30) Norfolk')),
		HTMLHelper::_('select.option','Asia/Kamchatka', Text::_('(GMT+12:00) Moscow+09 - Petropavlovsk-Kamchatskiy')),
		HTMLHelper::_('select.option','Pacific/Auckland', Text::_('(GMT+12:00) Auckland')),
		HTMLHelper::_('select.option','Pacific/Fiji', Text::_('(GMT+12:00) Fiji')),
		HTMLHelper::_('select.option','Pacific/Funafuti', Text::_('(GMT+12:00) Funafuti')),
		HTMLHelper::_('select.option','Pacific/Kwajalein', Text::_('(GMT+12:00) Kwajalein')),
		HTMLHelper::_('select.option','Pacific/Majuro', Text::_('(GMT+12:00) Majuro')),
		HTMLHelper::_('select.option','Pacific/Nauru', Text::_('(GMT+12:00) Nauru')),
		HTMLHelper::_('select.option','Pacific/Tarawa', Text::_('(GMT+12:00) Tarawa')),
		HTMLHelper::_('select.option','Pacific/Wake', Text::_('(GMT+12:00) Wake')),
		HTMLHelper::_('select.option','Pacific/Wallis', Text::_('(GMT+12:00) Wallis')),
		HTMLHelper::_('select.option','Pacific/Enderbury', Text::_('(GMT+13:00) Enderbury')),
		HTMLHelper::_('select.option','Pacific/Tongatapu', Text::_('(GMT+13:00) Tongatapu')),
		HTMLHelper::_('select.option','Pacific/Kiritimati', Text::_('(GMT+14:00) Kiritimati')));

		/*
		if(empty($value)){
			$conf =& Factory::getConfig();
			$globalTz = $conf->getValue('config.offset');
			switch ($globalTz) {
				case -11:
					$value = 'Pacific/Midway';
					break;
				case -10:
					$value = 'Pacific/Honolulu';
					break;
				case -9.5:
					$value = 'Pacific/Marquesas';
					break;
				case -9:
					$value = 'America/Anchorage';
					break;
				case -8:
					$value = 'America/Los_Angeles';
					break;
				case -7:
					$value = 'America/Dawson_Creek';
					break;
				case -6:
					$value = 'America/Chicago';
					break;
				case -5:
					$value = 'America/New_York';
					break;
				case -4.5:
					$value = 'America/Caracas';
					break;
				case -4:
					$value = 'America/Halifax';
					break;
				case -3.5:
					$value = 'America/St_Johns';
					break;
				case -3:
					$value = 'America/Montevideo';
					break;
				case -2:
					$value = 'America/Noronha';
					break;
				case -1:
					$value = 'Atlantic/Azores';
					break;
				case 0:
					$value = 'Europe/London';
					break;
				case 1:
					$value = 'Europe/Belgrade';
					break;
				case 2:
					$value = 'Europe/Istanbul';
					break;
				case 3:
					$value = 'Europe/Moscow';
					break;
				case 3.5:
					$value = 'Asia/Tehran';
					break;
				case 4:
					$value = 'Asia/Dubai';
					break;
				case 4.5:
					$value = 'Asia/Kabul';
					break;
				case 5:
					$value = 'Asia/Yekaterinburg';
					break;
				case 5.5:
					$value = 'Asia/Calcutta';
					break;
				case 5.75:
					$value = 'Asia/Katmandu';
					break;
				case 6:
					$value = 'Asia/Almaty';
					break;
				case 6.5:
					$value = 'Asia/Rangoon';
					break;
				case 7:
					$value = 'Asia/Bangkok';
					break;
				case 8:
					$value = 'Asia/Shanghai';
					break;
				case 9:
					$value = 'Asia/Tokyo';
					break;
				case 9.5:
					$value = 'Australia/Adelaide';
					break;
				case 10:
					$value = 'Australia/Brisbane';
					break;
				case 11:
					$value = 'Pacific/Kosrae';
					break;
				case 11.5:
					$value = 'Pacific/Norfolk';
					break;
				case 12:
					$value = 'Pacific/Auckland';
					break;
				case 13:
					$value = 'Pacific/Tongatapu';
					break;
				case 14:
					$value = 'Pacific/Kiritimati';
					break;
				default:
					$value = 'Europe/London';
			}
		}
		*/
		return $timezones;
	}
}
?>