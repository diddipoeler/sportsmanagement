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
		JHTML::_('select.option','', JText::_('')),
		JHTML::_('select.option','Pacific/Apia', JText::_('(GMT-11:00) Apia')),
		JHTML::_('select.option','Pacific/Midway', JText::_('(GMT-11:00) Midway')),
		JHTML::_('select.option','Pacific/Niue', JText::_('(GMT-11:00) Niue')),
		JHTML::_('select.option','Pacific/Pago_Pago', JText::_('(GMT-11:00) Pago Pago')),
		JHTML::_('select.option','Pacific/Fakaofo', JText::_('(GMT-10:00) Fakaofo')),
		JHTML::_('select.option','Pacific/Honolulu', JText::_('(GMT-10:00) Hawaii Time')),
		JHTML::_('select.option','Pacific/Johnston', JText::_('(GMT-10:00) Johnston')),
		JHTML::_('select.option','Pacific/Rarotonga', JText::_('(GMT-10:00) Rarotonga')),
		JHTML::_('select.option','Pacific/Tahiti', JText::_('(GMT-10:00) Tahiti')),
		JHTML::_('select.option','Pacific/Marquesas', JText::_('(GMT-09:30) Marquesas')),
		JHTML::_('select.option','America/Anchorage', JText::_('(GMT-09:00) Alaska Time')),
		JHTML::_('select.option','Pacific/Gambier', JText::_('(GMT-09:00) Gambier')),
		JHTML::_('select.option','America/Los_Angeles', JText::_('(GMT-08:00) Pacific Time')),
		JHTML::_('select.option','America/Tijuana', JText::_('(GMT-08:00) Pacific Time - Tijuana')),
		JHTML::_('select.option','America/Vancouver', JText::_('(GMT-08:00) Pacific Time - Vancouver')),
		JHTML::_('select.option','America/Whitehorse', JText::_('(GMT-08:00) Pacific Time - Whitehorse')),
		JHTML::_('select.option','Pacific/Pitcairn', JText::_('(GMT-08:00) Pitcairn')),
		JHTML::_('select.option','America/Dawson_Creek', JText::_('(GMT-07:00) Mountain Time - Dawson Creek')),
		JHTML::_('select.option','America/Denver', JText::_('(GMT-07:00) Mountain Time (America/Denver)')),
		JHTML::_('select.option','America/Edmonton', JText::_('(GMT-07:00) Mountain Time - Edmonton')),
		JHTML::_('select.option','America/Hermosillo', JText::_('(GMT-07:00) Mountain Time - Hermosillo')),
		JHTML::_('select.option','America/Mazatlan', JText::_('(GMT-07:00) Mountain Time - Chihuahua, Mazatlan')),
		JHTML::_('select.option','America/Phoenix', JText::_('(GMT-07:00) Mountain Time - Arizona')),
		JHTML::_('select.option','America/Yellowknife', JText::_('(GMT-07:00) Mountain Time - Yellowknife')),
		JHTML::_('select.option','America/Belize', JText::_('(GMT-06:00) Belize')),
		JHTML::_('select.option','America/Chicago', JText::_('(GMT-06:00) Central Time')),
		JHTML::_('select.option','America/Costa_Rica', JText::_('(GMT-06:00) Costa Rica')),
		JHTML::_('select.option','America/El_Salvador', JText::_('(GMT-06:00) El Salvador')),
		JHTML::_('select.option','America/Guatemala', JText::_('(GMT-06:00) Guatemala')),
		JHTML::_('select.option','America/Managua', JText::_('(GMT-06:00) Managua')),
		JHTML::_('select.option','America/Mexico_City', JText::_('(GMT-06:00) Central Time - Mexico City')),
		JHTML::_('select.option','America/Regina', JText::_('(GMT-06:00) Central Time - Regina')),
		JHTML::_('select.option','America/Tegucigalpa', JText::_('(GMT-06:00) Central Time (America/Tegucigalpa)')),
		JHTML::_('select.option','America/Winnipeg', JText::_('(GMT-06:00) Central Time - Winnipeg')),
		JHTML::_('select.option','Pacific/Easter', JText::_('(GMT-06:00) Easter Island')),
		JHTML::_('select.option','Pacific/Galapagos', JText::_('(GMT-06:00) Galapagos')),
		JHTML::_('select.option','America/Bogota', JText::_('(GMT-05:00) Bogota')),
		JHTML::_('select.option','America/Cayman', JText::_('(GMT-05:00) Cayman')),
		JHTML::_('select.option','America/Grand_Turk', JText::_('(GMT-05:00) Grand Turk')),
		JHTML::_('select.option','America/Guayaquil', JText::_('(GMT-05:00) Guayaquil')),
		JHTML::_('select.option','America/Havana', JText::_('(GMT-05:00) Havana')),
		JHTML::_('select.option','America/Iqaluit', JText::_('(GMT-05:00) Eastern Time - Iqaluit')),
		JHTML::_('select.option','America/Jamaica', JText::_('(GMT-05:00) Jamaica')),
		JHTML::_('select.option','America/Lima', JText::_('(GMT-05:00) Lima')),
		JHTML::_('select.option','America/Montreal', JText::_('(GMT-05:00) Eastern Time - Montreal')),
		JHTML::_('select.option','America/Nassau', JText::_('(GMT-05:00) Nassau')),
		JHTML::_('select.option','America/New_York', JText::_('(GMT-05:00) Eastern Time')),
		JHTML::_('select.option','America/Panama', JText::_('(GMT-05:00) Panama')),
		JHTML::_('select.option','America/Port-au-Prince', JText::_('(GMT-05:00) Port-au-Prince')),
		JHTML::_('select.option','America/Toronto', JText::_('(GMT-05:00) Eastern Time - Toronto')),
		JHTML::_('select.option','America/Caracas', JText::_('(GMT-04:30) Caracas')),
		JHTML::_('select.option','America/Anguilla', JText::_('(GMT-04:00) Anguilla')),
		JHTML::_('select.option','America/Antigua', JText::_('(GMT-04:00) Antigua')),
		JHTML::_('select.option','America/Aruba', JText::_('(GMT-04:00) Aruba')),
		JHTML::_('select.option','America/Asuncion', JText::_('(GMT-04:00) Asuncion')),
		JHTML::_('select.option','America/Barbados', JText::_('(GMT-04:00) Barbados')),
		JHTML::_('select.option','America/Boa_Vista', JText::_('(GMT-04:00) Boa Vista')),
		JHTML::_('select.option','America/Campo_Grande', JText::_('(GMT-04:00) Campo Grande')),
		JHTML::_('select.option','America/Cuiaba', JText::_('(GMT-04:00) Cuiaba')),
		JHTML::_('select.option','America/Curacao', JText::_('(GMT-04:00) Curacao')),
		JHTML::_('select.option','America/Dominica', JText::_('(GMT-04:00) Dominica')),
		JHTML::_('select.option','America/Grenada', JText::_('(GMT-04:00) Grenada')),
		JHTML::_('select.option','America/Guadeloupe', JText::_('(GMT-04:00) Guadeloupe')),
		JHTML::_('select.option','America/Guyana', JText::_('(GMT-04:00) Guyana')),
		JHTML::_('select.option','America/Halifax', JText::_('(GMT-04:00) Atlantic Time - Halifax')),
		JHTML::_('select.option','America/La_Paz', JText::_('(GMT-04:00) La Paz')),
		JHTML::_('select.option','America/Manaus', JText::_('(GMT-04:00) Manaus')),
		JHTML::_('select.option','America/Martinique', JText::_('(GMT-04:00) Martinique')),
		JHTML::_('select.option','America/Montserrat', JText::_('(GMT-04:00) Montserrat')),
		JHTML::_('select.option','America/Port_of_Spain', JText::_('(GMT-04:00) Port of Spain')),
		JHTML::_('select.option','America/Porto_Velho', JText::_('(GMT-04:00) Porto Velho')),
		JHTML::_('select.option','America/Puerto_Rico', JText::_('(GMT-04:00) Puerto Rico')),
		JHTML::_('select.option','America/Rio_Branco', JText::_('(GMT-04:00) Rio Branco')),
		JHTML::_('select.option','America/Santiago', JText::_('(GMT-04:00) Santiago')),
		JHTML::_('select.option','America/Santo_Domingo', JText::_('(GMT-04:00) Santo Domingo')),
		JHTML::_('select.option','America/St_Kitts', JText::_('(GMT-04:00) St. Kitts')),
		JHTML::_('select.option','America/St_Lucia', JText::_('(GMT-04:00) St. Lucia')),
		JHTML::_('select.option','America/St_Thomas', JText::_('(GMT-04:00) St. Thomas')),
		JHTML::_('select.option','America/St_Vincent', JText::_('(GMT-04:00) St. Vincent')),
		JHTML::_('select.option','America/Thule', JText::_('(GMT-04:00) Thule')),
		JHTML::_('select.option','America/Tortola', JText::_('(GMT-04:00) Tortola')),
		JHTML::_('select.option','Antarctica/Palmer', JText::_('(GMT-04:00) Palmer')),
		JHTML::_('select.option','Atlantic/Bermuda', JText::_('(GMT-04:00) Bermuda')),
		JHTML::_('select.option','Atlantic/Stanley', JText::_('(GMT-04:00) Stanley')),
		JHTML::_('select.option','America/St_Johns', JText::_('(GMT-03:30) Newfoundland Time - St. Johns')),
		JHTML::_('select.option','America/Araguaina', JText::_('(GMT-03:00) Araguaina')),
		JHTML::_('select.option','America/Argentina/Buenos_Aires', JText::_('(GMT-03:00) Buenos Aires')),
		JHTML::_('select.option','America/Bahia', JText::_('(GMT-03:00) Salvador')),
		JHTML::_('select.option','America/Belem', JText::_('(GMT-03:00) Belem')),
		JHTML::_('select.option','America/Cayenne', JText::_('(GMT-03:00) Cayenne')),
		JHTML::_('select.option','America/Fortaleza', JText::_('(GMT-03:00) Fortaleza')),
		JHTML::_('select.option','America/Godthab', JText::_('(GMT-03:00) Godthab')),
		JHTML::_('select.option','America/Maceio', JText::_('(GMT-03:00) Maceio')),
		JHTML::_('select.option','America/Miquelon', JText::_('(GMT-03:00) Miquelon')),
		JHTML::_('select.option','America/Montevideo', JText::_('(GMT-03:00) Montevideo')),
		JHTML::_('select.option','America/Paramaribo', JText::_('(GMT-03:00) Paramaribo')),
		JHTML::_('select.option','America/Recife', JText::_('(GMT-03:00) Recife')),
		JHTML::_('select.option','America/Sao_Paulo', JText::_('(GMT-03:00) Sao Paulo')),
		JHTML::_('select.option','Antarctica/Rothera', JText::_('(GMT-03:00) Rothera')),
		JHTML::_('select.option','America/Noronha', JText::_('(GMT-02:00) Noronha')),
		JHTML::_('select.option','Atlantic/South_Georgia', JText::_('(GMT-02:00) South Georgia')),
		JHTML::_('select.option','America/Scoresbysund', JText::_('(GMT-01:00) Scoresbysund')),
		JHTML::_('select.option','Atlantic/Azores', JText::_('(GMT-01:00) Azores')),
		JHTML::_('select.option','Atlantic/Cape_Verde', JText::_('(GMT-01:00) Cape Verde')),
		JHTML::_('select.option','Africa/Abidjan', JText::_('(GMT+00:00) Abidjan')),
		JHTML::_('select.option','Africa/Accra', JText::_('(GMT+00:00) Accra')),
		JHTML::_('select.option','Africa/Bamako', JText::_('(GMT+00:00) Bamako')),
		JHTML::_('select.option','Africa/Banjul', JText::_('(GMT+00:00) Banjul')),
		JHTML::_('select.option','Africa/Bissau', JText::_('(GMT+00:00) Bissau')),
		JHTML::_('select.option','Africa/Casablanca', JText::_('(GMT+00:00) Casablanca')),
		JHTML::_('select.option','Africa/Conakry', JText::_('(GMT+00:00) Conakry')),
		JHTML::_('select.option','Africa/Dakar', JText::_('(GMT+00:00) Dakar')),
		JHTML::_('select.option','Africa/El_Aaiun', JText::_('(GMT+00:00) El Aaiun')),
		JHTML::_('select.option','Africa/Freetown', JText::_('(GMT+00:00) Freetown')),
		JHTML::_('select.option','Africa/Lome', JText::_('(GMT+00:00) Lome')),
		JHTML::_('select.option','Africa/Monrovia', JText::_('(GMT+00:00) Monrovia')),
		JHTML::_('select.option','Africa/Nouakchott', JText::_('(GMT+00:00) Nouakchott')),
		JHTML::_('select.option','Africa/Ouagadougou', JText::_('(GMT+00:00) Ouagadougou')),
		JHTML::_('select.option','Africa/Sao_Tome', JText::_('(GMT+00:00) Sao Tome')),
		JHTML::_('select.option','America/Danmarkshavn', JText::_('(GMT+00:00) Danmarkshavn')),
		JHTML::_('select.option','Atlantic/Canary', JText::_('(GMT+00:00) Canary Islands')),
		JHTML::_('select.option','Atlantic/Faroe', JText::_('(GMT+00:00) Faeroe')),
		JHTML::_('select.option','Atlantic/Reykjavik', JText::_('(GMT+00:00) Reykjavik')),
		JHTML::_('select.option','Atlantic/St_Helena', JText::_('(GMT+00:00) St Helena')),
		JHTML::_('select.option','Etc/GMT', JText::_('(GMT+00:00) GMT (no daylight saving)')),
		JHTML::_('select.option','Europe/Dublin', JText::_('(GMT+00:00) Dublin')),
		JHTML::_('select.option','Europe/Lisbon', JText::_('(GMT+00:00) Lisbon')),
		JHTML::_('select.option','Europe/London', JText::_('(GMT+00:00) London')),
		JHTML::_('select.option','Africa/Algiers', JText::_('(GMT+01:00) Algiers')),
		JHTML::_('select.option','Africa/Bangui', JText::_('(GMT+01:00) Bangui')),
		JHTML::_('select.option','Africa/Brazzaville', JText::_('(GMT+01:00) Brazzaville')),
		JHTML::_('select.option','Africa/Ceuta', JText::_('(GMT+01:00) Ceuta')),
		JHTML::_('select.option','Africa/Douala', JText::_('(GMT+01:00) Douala')),
		JHTML::_('select.option','Africa/Kinshasa', JText::_('(GMT+01:00) Kinshasa')),
		JHTML::_('select.option','Africa/Lagos', JText::_('(GMT+01:00) Lagos')),
		JHTML::_('select.option','Africa/Libreville', JText::_('(GMT+01:00) Libreville')),
		JHTML::_('select.option','Africa/Luanda', JText::_('(GMT+01:00) Luanda')),
		JHTML::_('select.option','Africa/Malabo', JText::_('(GMT+01:00) Malabo')),
		JHTML::_('select.option','Africa/Ndjamena', JText::_('(GMT+01:00) Ndjamena')),
		JHTML::_('select.option','Africa/Niamey', JText::_('(GMT+01:00) Niamey')),
		JHTML::_('select.option','Africa/Porto-Novo', JText::_('(GMT+01:00) Porto-Novo')),
		JHTML::_('select.option','Africa/Tunis', JText::_('(GMT+01:00) Tunis')),
		JHTML::_('select.option','Africa/Windhoek', JText::_('(GMT+01:00) Windhoek')),
		JHTML::_('select.option','Europe/Amsterdam', JText::_('(GMT+01:00) Amsterdam')),
		JHTML::_('select.option','Europe/Andorra', JText::_('(GMT+01:00) Andorra')),
		JHTML::_('select.option','Europe/Belgrade', JText::_('(GMT+01:00) Central European Time (Europe/Belgrade)')),
		JHTML::_('select.option','Europe/Berlin', JText::_('(GMT+01:00) Berlin')),
		JHTML::_('select.option','Europe/Brussels', JText::_('(GMT+01:00) Brussels')),
		JHTML::_('select.option','Europe/Budapest', JText::_('(GMT+01:00) Budapest')),
		JHTML::_('select.option','Europe/Copenhagen', JText::_('(GMT+01:00) Copenhagen')),
		JHTML::_('select.option','Europe/Gibraltar', JText::_('(GMT+01:00) Gibraltar')),
		JHTML::_('select.option','Europe/Luxembourg', JText::_('(GMT+01:00) Luxembourg')),
		JHTML::_('select.option','Europe/Madrid', JText::_('(GMT+01:00) Madrid')),
		JHTML::_('select.option','Europe/Malta', JText::_('(GMT+01:00) Malta')),
		JHTML::_('select.option','Europe/Monaco', JText::_('(GMT+01:00) Monaco')),
		JHTML::_('select.option','Europe/Oslo', JText::_('(GMT+01:00) Oslo')),
		JHTML::_('select.option','Europe/Paris', JText::_('(GMT+01:00) Paris')),
		JHTML::_('select.option','Europe/Prague', JText::_('(GMT+01:00) Central European Time (Europe/Prague)')),
		JHTML::_('select.option','Europe/Rome', JText::_('(GMT+01:00) Rome')),
		JHTML::_('select.option','Europe/Stockholm', JText::_('(GMT+01:00) Stockholm')),
		JHTML::_('select.option','Europe/Tirane', JText::_('(GMT+01:00) Tirane')),
		JHTML::_('select.option','Europe/Vaduz', JText::_('(GMT+01:00) Vaduz')),
		JHTML::_('select.option','Europe/Vienna', JText::_('(GMT+01:00) Vienna')),
		JHTML::_('select.option','Europe/Warsaw', JText::_('(GMT+01:00) Warsaw')),
		JHTML::_('select.option','Europe/Zurich', JText::_('(GMT+01:00) Zurich')),
		JHTML::_('select.option','Africa/Blantyre', JText::_('(GMT+02:00) Blantyre')),
		JHTML::_('select.option','Africa/Bujumbura', JText::_('(GMT+02:00) Bujumbura')),
		JHTML::_('select.option','Africa/Cairo', JText::_('(GMT+02:00) Cairo')),
		JHTML::_('select.option','Africa/Gaborone', JText::_('(GMT+02:00) Gaborone')),
		JHTML::_('select.option','Africa/Harare', JText::_('(GMT+02:00) Harare')),
		JHTML::_('select.option','Africa/Johannesburg', JText::_('(GMT+02:00) Johannesburg')),
		JHTML::_('select.option','Africa/Kigali', JText::_('(GMT+02:00) Kigali')),
		JHTML::_('select.option','Africa/Lubumbashi', JText::_('(GMT+02:00) Lubumbashi')),
		JHTML::_('select.option','Africa/Lusaka', JText::_('(GMT+02:00) Lusaka')),
		JHTML::_('select.option','Africa/Maputo', JText::_('(GMT+02:00) Maputo')),
		JHTML::_('select.option','Africa/Maseru', JText::_('(GMT+02:00) Maseru')),
		JHTML::_('select.option','Africa/Mbabane', JText::_('(GMT+02:00) Mbabane')),
		JHTML::_('select.option','Africa/Tripoli', JText::_('(GMT+02:00) Tripoli')),
		JHTML::_('select.option','Asia/Amman', JText::_('(GMT+02:00) Amman')),
		JHTML::_('select.option','Asia/Beirut', JText::_('(GMT+02:00) Beirut')),
		JHTML::_('select.option','Asia/Damascus', JText::_('(GMT+02:00) Damascus')),
		JHTML::_('select.option','Asia/Gaza', JText::_('(GMT+02:00) Gaza')),
		JHTML::_('select.option','Asia/Jerusalem', JText::_('(GMT+02:00) Jerusalem')),
		JHTML::_('select.option','Asia/Nicosia', JText::_('(GMT+02:00) Nicosia')),
		JHTML::_('select.option','Europe/Athens', JText::_('(GMT+02:00) Athens')),
		JHTML::_('select.option','Europe/Bucharest', JText::_('(GMT+02:00) Bucharest')),
		JHTML::_('select.option','Europe/Chisinau', JText::_('(GMT+02:00) Chisinau')),
		JHTML::_('select.option','Europe/Helsinki', JText::_('(GMT+02:00) Helsinki')),
		JHTML::_('select.option','Europe/Istanbul', JText::_('(GMT+02:00) Istanbul')),
		JHTML::_('select.option','Europe/Kaliningrad', JText::_('(GMT+02:00) Moscow-01 - Kaliningrad')),
		JHTML::_('select.option','Europe/Kiev', JText::_('(GMT+02:00) Kiev')),
		JHTML::_('select.option','Europe/Minsk', JText::_('(GMT+02:00) Minsk')),
		JHTML::_('select.option','Europe/Riga', JText::_('(GMT+02:00) Riga')),
		JHTML::_('select.option','Europe/Sofia', JText::_('(GMT+02:00) Sofia')),
		JHTML::_('select.option','Europe/Tallinn', JText::_('(GMT+02:00) Tallinn')),
		JHTML::_('select.option','Europe/Vilnius', JText::_('(GMT+02:00) Vilnius')),
		JHTML::_('select.option','Africa/Addis_Ababa', JText::_('(GMT+03:00) Addis Ababa')),
		JHTML::_('select.option','Africa/Asmara', JText::_('(GMT+03:00) Asmera')),
		JHTML::_('select.option','Africa/Dar_es_Salaam', JText::_('(GMT+03:00) Dar es Salaam')),
		JHTML::_('select.option','Africa/Djibouti', JText::_('(GMT+03:00) Djibouti')),
		JHTML::_('select.option','Africa/Kampala', JText::_('(GMT+03:00) Kampala')),
		JHTML::_('select.option','Africa/Khartoum', JText::_('(GMT+03:00) Khartoum')),
		JHTML::_('select.option','Africa/Mogadishu', JText::_('(GMT+03:00) Mogadishu')),
		JHTML::_('select.option','Africa/Nairobi', JText::_('(GMT+03:00) Nairobi')),
		JHTML::_('select.option','Antarctica/Syowa', JText::_('(GMT+03:00) Syowa')),
		JHTML::_('select.option','Asia/Aden', JText::_('(GMT+03:00) Aden')),
		JHTML::_('select.option','Asia/Baghdad', JText::_('(GMT+03:00) Baghdad')),
		JHTML::_('select.option','Asia/Bahrain', JText::_('(GMT+03:00) Bahrain')),
		JHTML::_('select.option','Asia/Kuwait', JText::_('(GMT+03:00) Kuwait')),
		JHTML::_('select.option','Asia/Qatar', JText::_('(GMT+03:00) Qatar')),
		JHTML::_('select.option','Asia/Riyadh', JText::_('(GMT+03:00) Riyadh')),
		JHTML::_('select.option','Europe/Moscow', JText::_('(GMT+03:00) Moscow+00')),
		JHTML::_('select.option','Indian/Antananarivo', JText::_('(GMT+03:00) Antananarivo')),
		JHTML::_('select.option','Indian/Comoro', JText::_('(GMT+03:00) Comoro')),
		JHTML::_('select.option','Indian/Mayotte', JText::_('(GMT+03:00) Mayotte')),
		JHTML::_('select.option','Asia/Tehran', JText::_('(GMT+03:30) Tehran')),
		JHTML::_('select.option','Asia/Baku', JText::_('(GMT+04:00) Baku')),
		JHTML::_('select.option','Asia/Dubai', JText::_('(GMT+04:00) Dubai')),
		JHTML::_('select.option','Asia/Muscat', JText::_('(GMT+04:00) Muscat')),
		JHTML::_('select.option','Asia/Tbilisi', JText::_('(GMT+04:00) Tbilisi')),
		JHTML::_('select.option','Asia/Yerevan', JText::_('(GMT+04:00) Yerevan')),
		JHTML::_('select.option','Europe/Samara', JText::_('(GMT+04:00) Moscow+01 - Samara')),
		JHTML::_('select.option','Indian/Mahe', JText::_('(GMT+04:00) Mahe')),
		JHTML::_('select.option','Indian/Mauritius', JText::_('(GMT+04:00) Mauritius')),
		JHTML::_('select.option','Indian/Reunion', JText::_('(GMT+04:00) Reunion')),
		JHTML::_('select.option','Asia/Kabul', JText::_('(GMT+04:30) Kabul')),
		JHTML::_('select.option','Asia/Aqtau', JText::_('(GMT+05:00) Aqtau')),
		JHTML::_('select.option','Asia/Aqtobe', JText::_('(GMT+05:00) Aqtobe')),
		JHTML::_('select.option','Asia/Ashgabat', JText::_('(GMT+05:00) Ashgabat')),
		JHTML::_('select.option','Asia/Dushanbe', JText::_('(GMT+05:00) Dushanbe')),
		JHTML::_('select.option','Asia/Karachi', JText::_('(GMT+05:00) Karachi')),
		JHTML::_('select.option','Asia/Tashkent', JText::_('(GMT+05:00) Tashkent')),
		JHTML::_('select.option','Asia/Yekaterinburg', JText::_('(GMT+05:00) Moscow+02 - Yekaterinburg')),
		JHTML::_('select.option','Indian/Kerguelen', JText::_('(GMT+05:00) Kerguelen')),
		JHTML::_('select.option','Indian/Maldives', JText::_('(GMT+05:00) Maldives')),
		JHTML::_('select.option','Asia/Calcutta', JText::_('(GMT+05:30) India Standard Time')),
		JHTML::_('select.option','Asia/Colombo', JText::_('(GMT+05:30) Colombo')),
		JHTML::_('select.option','Asia/Katmandu', JText::_('(GMT+05:45) Katmandu')),
		JHTML::_('select.option','Antarctica/Mawson', JText::_('(GMT+06:00) Mawson')),
		JHTML::_('select.option','Antarctica/Vostok', JText::_('(GMT+06:00) Vostok')),
		JHTML::_('select.option','Asia/Almaty', JText::_('(GMT+06:00) Almaty')),
		JHTML::_('select.option','Asia/Bishkek', JText::_('(GMT+06:00) Bishkek')),
		JHTML::_('select.option','Asia/Dhaka', JText::_('(GMT+06:00) Dhaka')),
		JHTML::_('select.option','Asia/Omsk', JText::_('(GMT+06:00) Moscow+03 - Omsk, Novosibirsk')),
		JHTML::_('select.option','Asia/Thimphu', JText::_('(GMT+06:00) Thimphu')),
		JHTML::_('select.option','Indian/Chagos', JText::_('(GMT+06:00) Chagos')),
		JHTML::_('select.option','Asia/Rangoon', JText::_('(GMT+06:30) Rangoon')),
		JHTML::_('select.option','Indian/Cocos', JText::_('(GMT+06:30) Cocos')),
		JHTML::_('select.option','Antarctica/Davis', JText::_('(GMT+07:00) Davis')),
		JHTML::_('select.option','Asia/Bangkok', JText::_('(GMT+07:00) Bangkok')),
		JHTML::_('select.option','Asia/Hovd', JText::_('(GMT+07:00) Hovd')),
		JHTML::_('select.option','Asia/Jakarta', JText::_('(GMT+07:00) Jakarta')),
		JHTML::_('select.option','Asia/Krasnoyarsk', JText::_('(GMT+07:00) Moscow+04 - Krasnoyarsk')),
		JHTML::_('select.option','Asia/Phnom_Penh', JText::_('(GMT+07:00) Phnom Penh')),
		JHTML::_('select.option','Asia/Saigon', JText::_('(GMT+07:00) Hanoi')),
		JHTML::_('select.option','Asia/Vientiane', JText::_('(GMT+07:00) Vientiane')),
		JHTML::_('select.option','Indian/Christmas', JText::_('(GMT+07:00) Christmas')),
		JHTML::_('select.option','Antarctica/Casey', JText::_('(GMT+08:00) Casey')),
		JHTML::_('select.option','Asia/Brunei', JText::_('(GMT+08:00) Brunei')),
		JHTML::_('select.option','Asia/Choibalsan', JText::_('(GMT+08:00) Choibalsan')),
		JHTML::_('select.option','Asia/Hong_Kong', JText::_('(GMT+08:00) Hong Kong')),
		JHTML::_('select.option','Asia/Irkutsk', JText::_('(GMT+08:00) Moscow+05 - Irkutsk')),
		JHTML::_('select.option','Asia/Kuala_Lumpur', JText::_('(GMT+08:00) Kuala Lumpur')),
		JHTML::_('select.option','Asia/Macau', JText::_('(GMT+08:00) Macau')),
		JHTML::_('select.option','Asia/Makassar', JText::_('(GMT+08:00) Makassar')),
		JHTML::_('select.option','Asia/Manila', JText::_('(GMT+08:00) Manila')),
		JHTML::_('select.option','Asia/Shanghai', JText::_('(GMT+08:00) China Time - Beijing')),
		JHTML::_('select.option','Asia/Singapore', JText::_('(GMT+08:00) Singapore')),
		JHTML::_('select.option','Asia/Taipei', JText::_('(GMT+08:00) Taipei')),
		JHTML::_('select.option','Asia/Ulaanbaatar', JText::_('(GMT+08:00) Ulaanbaatar')),
		JHTML::_('select.option','Australia/Perth', JText::_('(GMT+08:00) Western Time - Perth')),
		JHTML::_('select.option','Asia/Dili', JText::_('(GMT+09:00) Dili')),
		JHTML::_('select.option','Asia/Jayapura', JText::_('(GMT+09:00) Jayapura')),
		JHTML::_('select.option','Asia/Pyongyang', JText::_('(GMT+09:00) Pyongyang')),
		JHTML::_('select.option','Asia/Seoul', JText::_('(GMT+09:00) Seoul')),
		JHTML::_('select.option','Asia/Tokyo', JText::_('(GMT+09:00) Tokyo')),
		JHTML::_('select.option','Asia/Yakutsk', JText::_('(GMT+09:00) Moscow+06 - Yakutsk')),
		JHTML::_('select.option','Pacific/Palau', JText::_('(GMT+09:00) Palau')),
		JHTML::_('select.option','Australia/Adelaide', JText::_('(GMT+09:30) Central Time - Adelaide')),
		JHTML::_('select.option','Australia/Darwin', JText::_('(GMT+09:30) Central Time - Darwin')),
		JHTML::_('select.option','Antarctica/DumontDUrville', JText::_('(GMT+10:00) Dumont D\'Urville')),
		JHTML::_('select.option','Asia/Vladivostok', JText::_('(GMT+10:00) Moscow+07 - Yuzhno-Sakhalinsk')),
		JHTML::_('select.option','Australia/Brisbane', JText::_('(GMT+10:00) Eastern Time - Brisbane')),
		JHTML::_('select.option','Australia/Hobart', JText::_('(GMT+10:00) Eastern Time - Hobart')),
		JHTML::_('select.option','Australia/Sydney', JText::_('(GMT+10:00) Eastern Time - Melbourne, Sydney')),
		JHTML::_('select.option','Pacific/Guam', JText::_('(GMT+10:00) Guam')),
		JHTML::_('select.option','Pacific/Port_Moresby', JText::_('(GMT+10:00) Port Moresby')),
		JHTML::_('select.option','Pacific/Saipan', JText::_('(GMT+10:00) Saipan')),
		JHTML::_('select.option','Pacific/Truk', JText::_('(GMT+10:00) Truk')),
		JHTML::_('select.option','Asia/Magadan', JText::_('(GMT+11:00) Moscow+08 - Magadan')),
		JHTML::_('select.option','Pacific/Efate', JText::_('(GMT+11:00) Efate')),
		JHTML::_('select.option','Pacific/Guadalcanal', JText::_('(GMT+11:00) Guadalcanal')),
		JHTML::_('select.option','Pacific/Kosrae', JText::_('(GMT+11:00) Kosrae')),
		JHTML::_('select.option','Pacific/Noumea', JText::_('(GMT+11:00) Noumea')),
		JHTML::_('select.option','Pacific/Ponape', JText::_('(GMT+11:00) Ponape')),
		JHTML::_('select.option','Pacific/Norfolk', JText::_('(GMT+11:30) Norfolk')),
		JHTML::_('select.option','Asia/Kamchatka', JText::_('(GMT+12:00) Moscow+09 - Petropavlovsk-Kamchatskiy')),
		JHTML::_('select.option','Pacific/Auckland', JText::_('(GMT+12:00) Auckland')),
		JHTML::_('select.option','Pacific/Fiji', JText::_('(GMT+12:00) Fiji')),
		JHTML::_('select.option','Pacific/Funafuti', JText::_('(GMT+12:00) Funafuti')),
		JHTML::_('select.option','Pacific/Kwajalein', JText::_('(GMT+12:00) Kwajalein')),
		JHTML::_('select.option','Pacific/Majuro', JText::_('(GMT+12:00) Majuro')),
		JHTML::_('select.option','Pacific/Nauru', JText::_('(GMT+12:00) Nauru')),
		JHTML::_('select.option','Pacific/Tarawa', JText::_('(GMT+12:00) Tarawa')),
		JHTML::_('select.option','Pacific/Wake', JText::_('(GMT+12:00) Wake')),
		JHTML::_('select.option','Pacific/Wallis', JText::_('(GMT+12:00) Wallis')),
		JHTML::_('select.option','Pacific/Enderbury', JText::_('(GMT+13:00) Enderbury')),
		JHTML::_('select.option','Pacific/Tongatapu', JText::_('(GMT+13:00) Tongatapu')),
		JHTML::_('select.option','Pacific/Kiritimati', JText::_('(GMT+14:00) Kiritimati')));

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