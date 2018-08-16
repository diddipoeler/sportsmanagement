<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package		GCalendar
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2007 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
JLoader::import('joomla.html.html');
JLoader::import('joomla.form.formfield');
JLoader::import('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldGoogletimezones extends JFormFieldList
{
	protected $type  = 'Googletimezones';

	function getOptions()
	{
		$timezones = array (
		JHTML::_('select.option','', Text::_('')),
		JHTML::_('select.option','Pacific/Apia', Text::_('(GMT-11:00) Apia')),
		JHTML::_('select.option','Pacific/Midway', Text::_('(GMT-11:00) Midway')),
		JHTML::_('select.option','Pacific/Niue', Text::_('(GMT-11:00) Niue')),
		JHTML::_('select.option','Pacific/Pago_Pago', Text::_('(GMT-11:00) Pago Pago')),
		JHTML::_('select.option','Pacific/Fakaofo', Text::_('(GMT-10:00) Fakaofo')),
		JHTML::_('select.option','Pacific/Honolulu', Text::_('(GMT-10:00) Hawaii Time')),
		JHTML::_('select.option','Pacific/Johnston', Text::_('(GMT-10:00) Johnston')),
		JHTML::_('select.option','Pacific/Rarotonga', Text::_('(GMT-10:00) Rarotonga')),
		JHTML::_('select.option','Pacific/Tahiti', Text::_('(GMT-10:00) Tahiti')),
		JHTML::_('select.option','Pacific/Marquesas', Text::_('(GMT-09:30) Marquesas')),
		JHTML::_('select.option','America/Anchorage', Text::_('(GMT-09:00) Alaska Time')),
		JHTML::_('select.option','Pacific/Gambier', Text::_('(GMT-09:00) Gambier')),
		JHTML::_('select.option','America/Los_Angeles', Text::_('(GMT-08:00) Pacific Time')),
		JHTML::_('select.option','America/Tijuana', Text::_('(GMT-08:00) Pacific Time - Tijuana')),
		JHTML::_('select.option','America/Vancouver', Text::_('(GMT-08:00) Pacific Time - Vancouver')),
		JHTML::_('select.option','America/Whitehorse', Text::_('(GMT-08:00) Pacific Time - Whitehorse')),
		JHTML::_('select.option','Pacific/Pitcairn', Text::_('(GMT-08:00) Pitcairn')),
		JHTML::_('select.option','America/Dawson_Creek', Text::_('(GMT-07:00) Mountain Time - Dawson Creek')),
		JHTML::_('select.option','America/Denver', Text::_('(GMT-07:00) Mountain Time (America/Denver)')),
		JHTML::_('select.option','America/Edmonton', Text::_('(GMT-07:00) Mountain Time - Edmonton')),
		JHTML::_('select.option','America/Hermosillo', Text::_('(GMT-07:00) Mountain Time - Hermosillo')),
		JHTML::_('select.option','America/Mazatlan', Text::_('(GMT-07:00) Mountain Time - Chihuahua, Mazatlan')),
		JHTML::_('select.option','America/Phoenix', Text::_('(GMT-07:00) Mountain Time - Arizona')),
		JHTML::_('select.option','America/Yellowknife', Text::_('(GMT-07:00) Mountain Time - Yellowknife')),
		JHTML::_('select.option','America/Belize', Text::_('(GMT-06:00) Belize')),
		JHTML::_('select.option','America/Chicago', Text::_('(GMT-06:00) Central Time')),
		JHTML::_('select.option','America/Costa_Rica', Text::_('(GMT-06:00) Costa Rica')),
		JHTML::_('select.option','America/El_Salvador', Text::_('(GMT-06:00) El Salvador')),
		JHTML::_('select.option','America/Guatemala', Text::_('(GMT-06:00) Guatemala')),
		JHTML::_('select.option','America/Managua', Text::_('(GMT-06:00) Managua')),
		JHTML::_('select.option','America/Mexico_City', Text::_('(GMT-06:00) Central Time - Mexico City')),
		JHTML::_('select.option','America/Regina', Text::_('(GMT-06:00) Central Time - Regina')),
		JHTML::_('select.option','America/Tegucigalpa', Text::_('(GMT-06:00) Central Time (America/Tegucigalpa)')),
		JHTML::_('select.option','America/Winnipeg', Text::_('(GMT-06:00) Central Time - Winnipeg')),
		JHTML::_('select.option','Pacific/Easter', Text::_('(GMT-06:00) Easter Island')),
		JHTML::_('select.option','Pacific/Galapagos', Text::_('(GMT-06:00) Galapagos')),
		JHTML::_('select.option','America/Bogota', Text::_('(GMT-05:00) Bogota')),
		JHTML::_('select.option','America/Cayman', Text::_('(GMT-05:00) Cayman')),
		JHTML::_('select.option','America/Grand_Turk', Text::_('(GMT-05:00) Grand Turk')),
		JHTML::_('select.option','America/Guayaquil', Text::_('(GMT-05:00) Guayaquil')),
		JHTML::_('select.option','America/Havana', Text::_('(GMT-05:00) Havana')),
		JHTML::_('select.option','America/Iqaluit', Text::_('(GMT-05:00) Eastern Time - Iqaluit')),
		JHTML::_('select.option','America/Jamaica', Text::_('(GMT-05:00) Jamaica')),
		JHTML::_('select.option','America/Lima', Text::_('(GMT-05:00) Lima')),
		JHTML::_('select.option','America/Montreal', Text::_('(GMT-05:00) Eastern Time - Montreal')),
		JHTML::_('select.option','America/Nassau', Text::_('(GMT-05:00) Nassau')),
		JHTML::_('select.option','America/New_York', Text::_('(GMT-05:00) Eastern Time')),
		JHTML::_('select.option','America/Panama', Text::_('(GMT-05:00) Panama')),
		JHTML::_('select.option','America/Port-au-Prince', Text::_('(GMT-05:00) Port-au-Prince')),
		JHTML::_('select.option','America/Toronto', Text::_('(GMT-05:00) Eastern Time - Toronto')),
		JHTML::_('select.option','America/Caracas', Text::_('(GMT-04:30) Caracas')),
		JHTML::_('select.option','America/Anguilla', Text::_('(GMT-04:00) Anguilla')),
		JHTML::_('select.option','America/Antigua', Text::_('(GMT-04:00) Antigua')),
		JHTML::_('select.option','America/Aruba', Text::_('(GMT-04:00) Aruba')),
		JHTML::_('select.option','America/Asuncion', Text::_('(GMT-04:00) Asuncion')),
		JHTML::_('select.option','America/Barbados', Text::_('(GMT-04:00) Barbados')),
		JHTML::_('select.option','America/Boa_Vista', Text::_('(GMT-04:00) Boa Vista')),
		JHTML::_('select.option','America/Campo_Grande', Text::_('(GMT-04:00) Campo Grande')),
		JHTML::_('select.option','America/Cuiaba', Text::_('(GMT-04:00) Cuiaba')),
		JHTML::_('select.option','America/Curacao', Text::_('(GMT-04:00) Curacao')),
		JHTML::_('select.option','America/Dominica', Text::_('(GMT-04:00) Dominica')),
		JHTML::_('select.option','America/Grenada', Text::_('(GMT-04:00) Grenada')),
		JHTML::_('select.option','America/Guadeloupe', Text::_('(GMT-04:00) Guadeloupe')),
		JHTML::_('select.option','America/Guyana', Text::_('(GMT-04:00) Guyana')),
		JHTML::_('select.option','America/Halifax', Text::_('(GMT-04:00) Atlantic Time - Halifax')),
		JHTML::_('select.option','America/La_Paz', Text::_('(GMT-04:00) La Paz')),
		JHTML::_('select.option','America/Manaus', Text::_('(GMT-04:00) Manaus')),
		JHTML::_('select.option','America/Martinique', Text::_('(GMT-04:00) Martinique')),
		JHTML::_('select.option','America/Montserrat', Text::_('(GMT-04:00) Montserrat')),
		JHTML::_('select.option','America/Port_of_Spain', Text::_('(GMT-04:00) Port of Spain')),
		JHTML::_('select.option','America/Porto_Velho', Text::_('(GMT-04:00) Porto Velho')),
		JHTML::_('select.option','America/Puerto_Rico', Text::_('(GMT-04:00) Puerto Rico')),
		JHTML::_('select.option','America/Rio_Branco', Text::_('(GMT-04:00) Rio Branco')),
		JHTML::_('select.option','America/Santiago', Text::_('(GMT-04:00) Santiago')),
		JHTML::_('select.option','America/Santo_Domingo', Text::_('(GMT-04:00) Santo Domingo')),
		JHTML::_('select.option','America/St_Kitts', Text::_('(GMT-04:00) St. Kitts')),
		JHTML::_('select.option','America/St_Lucia', Text::_('(GMT-04:00) St. Lucia')),
		JHTML::_('select.option','America/St_Thomas', Text::_('(GMT-04:00) St. Thomas')),
		JHTML::_('select.option','America/St_Vincent', Text::_('(GMT-04:00) St. Vincent')),
		JHTML::_('select.option','America/Thule', Text::_('(GMT-04:00) Thule')),
		JHTML::_('select.option','America/Tortola', Text::_('(GMT-04:00) Tortola')),
		JHTML::_('select.option','Antarctica/Palmer', Text::_('(GMT-04:00) Palmer')),
		JHTML::_('select.option','Atlantic/Bermuda', Text::_('(GMT-04:00) Bermuda')),
		JHTML::_('select.option','Atlantic/Stanley', Text::_('(GMT-04:00) Stanley')),
		JHTML::_('select.option','America/St_Johns', Text::_('(GMT-03:30) Newfoundland Time - St. Johns')),
		JHTML::_('select.option','America/Araguaina', Text::_('(GMT-03:00) Araguaina')),
		JHTML::_('select.option','America/Argentina/Buenos_Aires', Text::_('(GMT-03:00) Buenos Aires')),
		JHTML::_('select.option','America/Bahia', Text::_('(GMT-03:00) Salvador')),
		JHTML::_('select.option','America/Belem', Text::_('(GMT-03:00) Belem')),
		JHTML::_('select.option','America/Cayenne', Text::_('(GMT-03:00) Cayenne')),
		JHTML::_('select.option','America/Fortaleza', Text::_('(GMT-03:00) Fortaleza')),
		JHTML::_('select.option','America/Godthab', Text::_('(GMT-03:00) Godthab')),
		JHTML::_('select.option','America/Maceio', Text::_('(GMT-03:00) Maceio')),
		JHTML::_('select.option','America/Miquelon', Text::_('(GMT-03:00) Miquelon')),
		JHTML::_('select.option','America/Montevideo', Text::_('(GMT-03:00) Montevideo')),
		JHTML::_('select.option','America/Paramaribo', Text::_('(GMT-03:00) Paramaribo')),
		JHTML::_('select.option','America/Recife', Text::_('(GMT-03:00) Recife')),
		JHTML::_('select.option','America/Sao_Paulo', Text::_('(GMT-03:00) Sao Paulo')),
		JHTML::_('select.option','Antarctica/Rothera', Text::_('(GMT-03:00) Rothera')),
		JHTML::_('select.option','America/Noronha', Text::_('(GMT-02:00) Noronha')),
		JHTML::_('select.option','Atlantic/South_Georgia', Text::_('(GMT-02:00) South Georgia')),
		JHTML::_('select.option','America/Scoresbysund', Text::_('(GMT-01:00) Scoresbysund')),
		JHTML::_('select.option','Atlantic/Azores', Text::_('(GMT-01:00) Azores')),
		JHTML::_('select.option','Atlantic/Cape_Verde', Text::_('(GMT-01:00) Cape Verde')),
		JHTML::_('select.option','Africa/Abidjan', Text::_('(GMT+00:00) Abidjan')),
		JHTML::_('select.option','Africa/Accra', Text::_('(GMT+00:00) Accra')),
		JHTML::_('select.option','Africa/Bamako', Text::_('(GMT+00:00) Bamako')),
		JHTML::_('select.option','Africa/Banjul', Text::_('(GMT+00:00) Banjul')),
		JHTML::_('select.option','Africa/Bissau', Text::_('(GMT+00:00) Bissau')),
		JHTML::_('select.option','Africa/Casablanca', Text::_('(GMT+00:00) Casablanca')),
		JHTML::_('select.option','Africa/Conakry', Text::_('(GMT+00:00) Conakry')),
		JHTML::_('select.option','Africa/Dakar', Text::_('(GMT+00:00) Dakar')),
		JHTML::_('select.option','Africa/El_Aaiun', Text::_('(GMT+00:00) El Aaiun')),
		JHTML::_('select.option','Africa/Freetown', Text::_('(GMT+00:00) Freetown')),
		JHTML::_('select.option','Africa/Lome', Text::_('(GMT+00:00) Lome')),
		JHTML::_('select.option','Africa/Monrovia', Text::_('(GMT+00:00) Monrovia')),
		JHTML::_('select.option','Africa/Nouakchott', Text::_('(GMT+00:00) Nouakchott')),
		JHTML::_('select.option','Africa/Ouagadougou', Text::_('(GMT+00:00) Ouagadougou')),
		JHTML::_('select.option','Africa/Sao_Tome', Text::_('(GMT+00:00) Sao Tome')),
		JHTML::_('select.option','America/Danmarkshavn', Text::_('(GMT+00:00) Danmarkshavn')),
		JHTML::_('select.option','Atlantic/Canary', Text::_('(GMT+00:00) Canary Islands')),
		JHTML::_('select.option','Atlantic/Faroe', Text::_('(GMT+00:00) Faeroe')),
		JHTML::_('select.option','Atlantic/Reykjavik', Text::_('(GMT+00:00) Reykjavik')),
		JHTML::_('select.option','Atlantic/St_Helena', Text::_('(GMT+00:00) St Helena')),
		JHTML::_('select.option','Etc/GMT', Text::_('(GMT+00:00) GMT (no daylight saving)')),
		JHTML::_('select.option','Europe/Dublin', Text::_('(GMT+00:00) Dublin')),
		JHTML::_('select.option','Europe/Lisbon', Text::_('(GMT+00:00) Lisbon')),
		JHTML::_('select.option','Europe/London', Text::_('(GMT+00:00) London')),
		JHTML::_('select.option','Africa/Algiers', Text::_('(GMT+01:00) Algiers')),
		JHTML::_('select.option','Africa/Bangui', Text::_('(GMT+01:00) Bangui')),
		JHTML::_('select.option','Africa/Brazzaville', Text::_('(GMT+01:00) Brazzaville')),
		JHTML::_('select.option','Africa/Ceuta', Text::_('(GMT+01:00) Ceuta')),
		JHTML::_('select.option','Africa/Douala', Text::_('(GMT+01:00) Douala')),
		JHTML::_('select.option','Africa/Kinshasa', Text::_('(GMT+01:00) Kinshasa')),
		JHTML::_('select.option','Africa/Lagos', Text::_('(GMT+01:00) Lagos')),
		JHTML::_('select.option','Africa/Libreville', Text::_('(GMT+01:00) Libreville')),
		JHTML::_('select.option','Africa/Luanda', Text::_('(GMT+01:00) Luanda')),
		JHTML::_('select.option','Africa/Malabo', Text::_('(GMT+01:00) Malabo')),
		JHTML::_('select.option','Africa/Ndjamena', Text::_('(GMT+01:00) Ndjamena')),
		JHTML::_('select.option','Africa/Niamey', Text::_('(GMT+01:00) Niamey')),
		JHTML::_('select.option','Africa/Porto-Novo', Text::_('(GMT+01:00) Porto-Novo')),
		JHTML::_('select.option','Africa/Tunis', Text::_('(GMT+01:00) Tunis')),
		JHTML::_('select.option','Africa/Windhoek', Text::_('(GMT+01:00) Windhoek')),
		JHTML::_('select.option','Europe/Amsterdam', Text::_('(GMT+01:00) Amsterdam')),
		JHTML::_('select.option','Europe/Andorra', Text::_('(GMT+01:00) Andorra')),
		JHTML::_('select.option','Europe/Belgrade', Text::_('(GMT+01:00) Central European Time (Europe/Belgrade)')),
		JHTML::_('select.option','Europe/Berlin', Text::_('(GMT+01:00) Berlin')),
		JHTML::_('select.option','Europe/Brussels', Text::_('(GMT+01:00) Brussels')),
		JHTML::_('select.option','Europe/Budapest', Text::_('(GMT+01:00) Budapest')),
		JHTML::_('select.option','Europe/Copenhagen', Text::_('(GMT+01:00) Copenhagen')),
		JHTML::_('select.option','Europe/Gibraltar', Text::_('(GMT+01:00) Gibraltar')),
		JHTML::_('select.option','Europe/Luxembourg', Text::_('(GMT+01:00) Luxembourg')),
		JHTML::_('select.option','Europe/Madrid', Text::_('(GMT+01:00) Madrid')),
		JHTML::_('select.option','Europe/Malta', Text::_('(GMT+01:00) Malta')),
		JHTML::_('select.option','Europe/Monaco', Text::_('(GMT+01:00) Monaco')),
		JHTML::_('select.option','Europe/Oslo', Text::_('(GMT+01:00) Oslo')),
		JHTML::_('select.option','Europe/Paris', Text::_('(GMT+01:00) Paris')),
		JHTML::_('select.option','Europe/Prague', Text::_('(GMT+01:00) Central European Time (Europe/Prague)')),
		JHTML::_('select.option','Europe/Rome', Text::_('(GMT+01:00) Rome')),
		JHTML::_('select.option','Europe/Stockholm', Text::_('(GMT+01:00) Stockholm')),
		JHTML::_('select.option','Europe/Tirane', Text::_('(GMT+01:00) Tirane')),
		JHTML::_('select.option','Europe/Vaduz', Text::_('(GMT+01:00) Vaduz')),
		JHTML::_('select.option','Europe/Vienna', Text::_('(GMT+01:00) Vienna')),
		JHTML::_('select.option','Europe/Warsaw', Text::_('(GMT+01:00) Warsaw')),
		JHTML::_('select.option','Europe/Zurich', Text::_('(GMT+01:00) Zurich')),
		JHTML::_('select.option','Africa/Blantyre', Text::_('(GMT+02:00) Blantyre')),
		JHTML::_('select.option','Africa/Bujumbura', Text::_('(GMT+02:00) Bujumbura')),
		JHTML::_('select.option','Africa/Cairo', Text::_('(GMT+02:00) Cairo')),
		JHTML::_('select.option','Africa/Gaborone', Text::_('(GMT+02:00) Gaborone')),
		JHTML::_('select.option','Africa/Harare', Text::_('(GMT+02:00) Harare')),
		JHTML::_('select.option','Africa/Johannesburg', Text::_('(GMT+02:00) Johannesburg')),
		JHTML::_('select.option','Africa/Kigali', Text::_('(GMT+02:00) Kigali')),
		JHTML::_('select.option','Africa/Lubumbashi', Text::_('(GMT+02:00) Lubumbashi')),
		JHTML::_('select.option','Africa/Lusaka', Text::_('(GMT+02:00) Lusaka')),
		JHTML::_('select.option','Africa/Maputo', Text::_('(GMT+02:00) Maputo')),
		JHTML::_('select.option','Africa/Maseru', Text::_('(GMT+02:00) Maseru')),
		JHTML::_('select.option','Africa/Mbabane', Text::_('(GMT+02:00) Mbabane')),
		JHTML::_('select.option','Africa/Tripoli', Text::_('(GMT+02:00) Tripoli')),
		JHTML::_('select.option','Asia/Amman', Text::_('(GMT+02:00) Amman')),
		JHTML::_('select.option','Asia/Beirut', Text::_('(GMT+02:00) Beirut')),
		JHTML::_('select.option','Asia/Damascus', Text::_('(GMT+02:00) Damascus')),
		JHTML::_('select.option','Asia/Gaza', Text::_('(GMT+02:00) Gaza')),
		JHTML::_('select.option','Asia/Jerusalem', Text::_('(GMT+02:00) Jerusalem')),
		JHTML::_('select.option','Asia/Nicosia', Text::_('(GMT+02:00) Nicosia')),
		JHTML::_('select.option','Europe/Athens', Text::_('(GMT+02:00) Athens')),
		JHTML::_('select.option','Europe/Bucharest', Text::_('(GMT+02:00) Bucharest')),
		JHTML::_('select.option','Europe/Chisinau', Text::_('(GMT+02:00) Chisinau')),
		JHTML::_('select.option','Europe/Helsinki', Text::_('(GMT+02:00) Helsinki')),
		JHTML::_('select.option','Europe/Istanbul', Text::_('(GMT+02:00) Istanbul')),
		JHTML::_('select.option','Europe/Kaliningrad', Text::_('(GMT+02:00) Moscow-01 - Kaliningrad')),
		JHTML::_('select.option','Europe/Kiev', Text::_('(GMT+02:00) Kiev')),
		JHTML::_('select.option','Europe/Minsk', Text::_('(GMT+02:00) Minsk')),
		JHTML::_('select.option','Europe/Riga', Text::_('(GMT+02:00) Riga')),
		JHTML::_('select.option','Europe/Sofia', Text::_('(GMT+02:00) Sofia')),
		JHTML::_('select.option','Europe/Tallinn', Text::_('(GMT+02:00) Tallinn')),
		JHTML::_('select.option','Europe/Vilnius', Text::_('(GMT+02:00) Vilnius')),
		JHTML::_('select.option','Africa/Addis_Ababa', Text::_('(GMT+03:00) Addis Ababa')),
		JHTML::_('select.option','Africa/Asmara', Text::_('(GMT+03:00) Asmera')),
		JHTML::_('select.option','Africa/Dar_es_Salaam', Text::_('(GMT+03:00) Dar es Salaam')),
		JHTML::_('select.option','Africa/Djibouti', Text::_('(GMT+03:00) Djibouti')),
		JHTML::_('select.option','Africa/Kampala', Text::_('(GMT+03:00) Kampala')),
		JHTML::_('select.option','Africa/Khartoum', Text::_('(GMT+03:00) Khartoum')),
		JHTML::_('select.option','Africa/Mogadishu', Text::_('(GMT+03:00) Mogadishu')),
		JHTML::_('select.option','Africa/Nairobi', Text::_('(GMT+03:00) Nairobi')),
		JHTML::_('select.option','Antarctica/Syowa', Text::_('(GMT+03:00) Syowa')),
		JHTML::_('select.option','Asia/Aden', Text::_('(GMT+03:00) Aden')),
		JHTML::_('select.option','Asia/Baghdad', Text::_('(GMT+03:00) Baghdad')),
		JHTML::_('select.option','Asia/Bahrain', Text::_('(GMT+03:00) Bahrain')),
		JHTML::_('select.option','Asia/Kuwait', Text::_('(GMT+03:00) Kuwait')),
		JHTML::_('select.option','Asia/Qatar', Text::_('(GMT+03:00) Qatar')),
		JHTML::_('select.option','Asia/Riyadh', Text::_('(GMT+03:00) Riyadh')),
		JHTML::_('select.option','Europe/Moscow', Text::_('(GMT+03:00) Moscow+00')),
		JHTML::_('select.option','Indian/Antananarivo', Text::_('(GMT+03:00) Antananarivo')),
		JHTML::_('select.option','Indian/Comoro', Text::_('(GMT+03:00) Comoro')),
		JHTML::_('select.option','Indian/Mayotte', Text::_('(GMT+03:00) Mayotte')),
		JHTML::_('select.option','Asia/Tehran', Text::_('(GMT+03:30) Tehran')),
		JHTML::_('select.option','Asia/Baku', Text::_('(GMT+04:00) Baku')),
		JHTML::_('select.option','Asia/Dubai', Text::_('(GMT+04:00) Dubai')),
		JHTML::_('select.option','Asia/Muscat', Text::_('(GMT+04:00) Muscat')),
		JHTML::_('select.option','Asia/Tbilisi', Text::_('(GMT+04:00) Tbilisi')),
		JHTML::_('select.option','Asia/Yerevan', Text::_('(GMT+04:00) Yerevan')),
		JHTML::_('select.option','Europe/Samara', Text::_('(GMT+04:00) Moscow+01 - Samara')),
		JHTML::_('select.option','Indian/Mahe', Text::_('(GMT+04:00) Mahe')),
		JHTML::_('select.option','Indian/Mauritius', Text::_('(GMT+04:00) Mauritius')),
		JHTML::_('select.option','Indian/Reunion', Text::_('(GMT+04:00) Reunion')),
		JHTML::_('select.option','Asia/Kabul', Text::_('(GMT+04:30) Kabul')),
		JHTML::_('select.option','Asia/Aqtau', Text::_('(GMT+05:00) Aqtau')),
		JHTML::_('select.option','Asia/Aqtobe', Text::_('(GMT+05:00) Aqtobe')),
		JHTML::_('select.option','Asia/Ashgabat', Text::_('(GMT+05:00) Ashgabat')),
		JHTML::_('select.option','Asia/Dushanbe', Text::_('(GMT+05:00) Dushanbe')),
		JHTML::_('select.option','Asia/Karachi', Text::_('(GMT+05:00) Karachi')),
		JHTML::_('select.option','Asia/Tashkent', Text::_('(GMT+05:00) Tashkent')),
		JHTML::_('select.option','Asia/Yekaterinburg', Text::_('(GMT+05:00) Moscow+02 - Yekaterinburg')),
		JHTML::_('select.option','Indian/Kerguelen', Text::_('(GMT+05:00) Kerguelen')),
		JHTML::_('select.option','Indian/Maldives', Text::_('(GMT+05:00) Maldives')),
		JHTML::_('select.option','Asia/Calcutta', Text::_('(GMT+05:30) India Standard Time')),
		JHTML::_('select.option','Asia/Colombo', Text::_('(GMT+05:30) Colombo')),
		JHTML::_('select.option','Asia/Katmandu', Text::_('(GMT+05:45) Katmandu')),
		JHTML::_('select.option','Antarctica/Mawson', Text::_('(GMT+06:00) Mawson')),
		JHTML::_('select.option','Antarctica/Vostok', Text::_('(GMT+06:00) Vostok')),
		JHTML::_('select.option','Asia/Almaty', Text::_('(GMT+06:00) Almaty')),
		JHTML::_('select.option','Asia/Bishkek', Text::_('(GMT+06:00) Bishkek')),
		JHTML::_('select.option','Asia/Dhaka', Text::_('(GMT+06:00) Dhaka')),
		JHTML::_('select.option','Asia/Omsk', Text::_('(GMT+06:00) Moscow+03 - Omsk, Novosibirsk')),
		JHTML::_('select.option','Asia/Thimphu', Text::_('(GMT+06:00) Thimphu')),
		JHTML::_('select.option','Indian/Chagos', Text::_('(GMT+06:00) Chagos')),
		JHTML::_('select.option','Asia/Rangoon', Text::_('(GMT+06:30) Rangoon')),
		JHTML::_('select.option','Indian/Cocos', Text::_('(GMT+06:30) Cocos')),
		JHTML::_('select.option','Antarctica/Davis', Text::_('(GMT+07:00) Davis')),
		JHTML::_('select.option','Asia/Bangkok', Text::_('(GMT+07:00) Bangkok')),
		JHTML::_('select.option','Asia/Hovd', Text::_('(GMT+07:00) Hovd')),
		JHTML::_('select.option','Asia/Jakarta', Text::_('(GMT+07:00) Jakarta')),
		JHTML::_('select.option','Asia/Krasnoyarsk', Text::_('(GMT+07:00) Moscow+04 - Krasnoyarsk')),
		JHTML::_('select.option','Asia/Phnom_Penh', Text::_('(GMT+07:00) Phnom Penh')),
		JHTML::_('select.option','Asia/Saigon', Text::_('(GMT+07:00) Hanoi')),
		JHTML::_('select.option','Asia/Vientiane', Text::_('(GMT+07:00) Vientiane')),
		JHTML::_('select.option','Indian/Christmas', Text::_('(GMT+07:00) Christmas')),
		JHTML::_('select.option','Antarctica/Casey', Text::_('(GMT+08:00) Casey')),
		JHTML::_('select.option','Asia/Brunei', Text::_('(GMT+08:00) Brunei')),
		JHTML::_('select.option','Asia/Choibalsan', Text::_('(GMT+08:00) Choibalsan')),
		JHTML::_('select.option','Asia/Hong_Kong', Text::_('(GMT+08:00) Hong Kong')),
		JHTML::_('select.option','Asia/Irkutsk', Text::_('(GMT+08:00) Moscow+05 - Irkutsk')),
		JHTML::_('select.option','Asia/Kuala_Lumpur', Text::_('(GMT+08:00) Kuala Lumpur')),
		JHTML::_('select.option','Asia/Macau', Text::_('(GMT+08:00) Macau')),
		JHTML::_('select.option','Asia/Makassar', Text::_('(GMT+08:00) Makassar')),
		JHTML::_('select.option','Asia/Manila', Text::_('(GMT+08:00) Manila')),
		JHTML::_('select.option','Asia/Shanghai', Text::_('(GMT+08:00) China Time - Beijing')),
		JHTML::_('select.option','Asia/Singapore', Text::_('(GMT+08:00) Singapore')),
		JHTML::_('select.option','Asia/Taipei', Text::_('(GMT+08:00) Taipei')),
		JHTML::_('select.option','Asia/Ulaanbaatar', Text::_('(GMT+08:00) Ulaanbaatar')),
		JHTML::_('select.option','Australia/Perth', Text::_('(GMT+08:00) Western Time - Perth')),
		JHTML::_('select.option','Asia/Dili', Text::_('(GMT+09:00) Dili')),
		JHTML::_('select.option','Asia/Jayapura', Text::_('(GMT+09:00) Jayapura')),
		JHTML::_('select.option','Asia/Pyongyang', Text::_('(GMT+09:00) Pyongyang')),
		JHTML::_('select.option','Asia/Seoul', Text::_('(GMT+09:00) Seoul')),
		JHTML::_('select.option','Asia/Tokyo', Text::_('(GMT+09:00) Tokyo')),
		JHTML::_('select.option','Asia/Yakutsk', Text::_('(GMT+09:00) Moscow+06 - Yakutsk')),
		JHTML::_('select.option','Pacific/Palau', Text::_('(GMT+09:00) Palau')),
		JHTML::_('select.option','Australia/Adelaide', Text::_('(GMT+09:30) Central Time - Adelaide')),
		JHTML::_('select.option','Australia/Darwin', Text::_('(GMT+09:30) Central Time - Darwin')),
		JHTML::_('select.option','Antarctica/DumontDUrville', Text::_('(GMT+10:00) Dumont D\'Urville')),
		JHTML::_('select.option','Asia/Vladivostok', Text::_('(GMT+10:00) Moscow+07 - Yuzhno-Sakhalinsk')),
		JHTML::_('select.option','Australia/Brisbane', Text::_('(GMT+10:00) Eastern Time - Brisbane')),
		JHTML::_('select.option','Australia/Hobart', Text::_('(GMT+10:00) Eastern Time - Hobart')),
		JHTML::_('select.option','Australia/Sydney', Text::_('(GMT+10:00) Eastern Time - Melbourne, Sydney')),
		JHTML::_('select.option','Pacific/Guam', Text::_('(GMT+10:00) Guam')),
		JHTML::_('select.option','Pacific/Port_Moresby', Text::_('(GMT+10:00) Port Moresby')),
		JHTML::_('select.option','Pacific/Saipan', Text::_('(GMT+10:00) Saipan')),
		JHTML::_('select.option','Pacific/Truk', Text::_('(GMT+10:00) Truk')),
		JHTML::_('select.option','Asia/Magadan', Text::_('(GMT+11:00) Moscow+08 - Magadan')),
		JHTML::_('select.option','Pacific/Efate', Text::_('(GMT+11:00) Efate')),
		JHTML::_('select.option','Pacific/Guadalcanal', Text::_('(GMT+11:00) Guadalcanal')),
		JHTML::_('select.option','Pacific/Kosrae', Text::_('(GMT+11:00) Kosrae')),
		JHTML::_('select.option','Pacific/Noumea', Text::_('(GMT+11:00) Noumea')),
		JHTML::_('select.option','Pacific/Ponape', Text::_('(GMT+11:00) Ponape')),
		JHTML::_('select.option','Pacific/Norfolk', Text::_('(GMT+11:30) Norfolk')),
		JHTML::_('select.option','Asia/Kamchatka', Text::_('(GMT+12:00) Moscow+09 - Petropavlovsk-Kamchatskiy')),
		JHTML::_('select.option','Pacific/Auckland', Text::_('(GMT+12:00) Auckland')),
		JHTML::_('select.option','Pacific/Fiji', Text::_('(GMT+12:00) Fiji')),
		JHTML::_('select.option','Pacific/Funafuti', Text::_('(GMT+12:00) Funafuti')),
		JHTML::_('select.option','Pacific/Kwajalein', Text::_('(GMT+12:00) Kwajalein')),
		JHTML::_('select.option','Pacific/Majuro', Text::_('(GMT+12:00) Majuro')),
		JHTML::_('select.option','Pacific/Nauru', Text::_('(GMT+12:00) Nauru')),
		JHTML::_('select.option','Pacific/Tarawa', Text::_('(GMT+12:00) Tarawa')),
		JHTML::_('select.option','Pacific/Wake', Text::_('(GMT+12:00) Wake')),
		JHTML::_('select.option','Pacific/Wallis', Text::_('(GMT+12:00) Wallis')),
		JHTML::_('select.option','Pacific/Enderbury', Text::_('(GMT+13:00) Enderbury')),
		JHTML::_('select.option','Pacific/Tongatapu', Text::_('(GMT+13:00) Tongatapu')),
		JHTML::_('select.option','Pacific/Kiritimati', Text::_('(GMT+14:00) Kiritimati')));

		/*
		if(empty($value)){
			$conf =& JFactory::getConfig();
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