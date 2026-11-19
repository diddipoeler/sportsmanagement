<?php

/**
 * Project:     XMLParser: A library for parsing XML feeds
 * PHP 8 compatibility: the legacy importer is named SportsManagementLegacyXmlParser
 * to avoid colliding with PHP's built-in XMLParser class.
 * File:        XMLParser.class.php
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @link       http://www.phpinsider.com/php/code/XMLParser/
 * @version    1.0-dev
 * @author     Monte Ohrt <monte at newdigitalgroup dot com>
 * @copyright  2004-2005 New Digital Group, Inc.
 * @license    GNU Lesser General Public License version 2.1 or later
 * @package    XMLParser
 */

defined('_JEXEC') or die('Restricted access');

class SportsManagementLegacyXmlParser
{
	/**
	 * holds the expat object
	 *
	 * @var obj
	 */
	var $xml_obj = null;

	/**
	 * holds the output array
	 *
	 * @var array
	 */
	var $output = array();

	/**
	 * the XML file character set
	 *
	 * @var array
	 */
	var $char_set = 'UTF-8';

	// #@-

	/**
	 * PHP 8 compatible constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * Historical constructor method retained for callers invoking it explicitly.
	 *
	 * @deprecated The PHP constructor is __construct().
	 */
	public function XMLParser(): void
	{
	}


	/**
	 * Read and parse one XML document into the legacy nested element array.
	 *
	 * Registers the Expat callbacks used by existing SportsManagement imports,
	 * feeds the document in bounded chunks and finalises the parser even when
	 * the file length is an exact multiple of the chunk size. File handles
	 * are closed on all paths. Invalid or missing files and malformed XML
	 * raise exceptions instead of terminating the Joomla administrator request.
	 *
	 * @param string $path Local path or PHP-supported XML input stream.
	 * @return array Parsed XML element tree in the original legacy layout.
	 * @throws \\InvalidArgumentException When the input path is invalid.
	 * @throws \\RuntimeException When reading or parsing XML fails.
	 */
	function parse($path)
	{
		if (!is_string($path) || $path === '') {
			throw new \\InvalidArgumentException('A valid XML input path is required.');
		}

		$handle = @fopen($path, 'rb');

		if ($handle === false) {
			throw new \\RuntimeException('Cannot open the XML data source.');
		}

		$this->output = array();

		try {
			$parser = xml_parser_create($this->char_set);
			$this->xml_obj = $parser;
			xml_set_object($parser, $this);
			xml_set_character_data_handler($parser, 'dataHandler');
			xml_set_element_handler($parser, 'startHandler', 'endHandler');

			while (!feof($handle)) {
				$data = fread($handle, 4096);

				if ($data === false) {
					throw new \\RuntimeException('Failed to read the XML data source.');
				}

				if ($data === '') {
					if (!feof($handle)) {
						throw new \\RuntimeException('XML input stream made no progress.');
					}

					break;
				}

				if (!xml_parse($parser, $data, false)) {
					throw new \\RuntimeException(
						'XML parse error: ' . xml_error_string(xml_get_error_code($parser))
						. ' at line ' . xml_get_current_line_number($parser)
					);
				}
			}

			// A separate final call is necessary when the last fread() returned
			// exactly 4096 bytes and feof() was not yet set by the driver.
			if (!xml_parse($parser, '', true)) {
				throw new \\RuntimeException(
					'XML parse error: ' . xml_error_string(xml_get_error_code($parser))
					. ' at line ' . xml_get_current_line_number($parser)
				);
			}

			return $this->output;
		} finally {
			fclose($handle);
			$this->xml_obj = null;
		}
	}

	/**
	 * define the start tag handler
	 *
	 * @param   obj     $parser   the expat parser object
	 * @param   string  $name     the XML tag name
	 * @param   array   $attribs  the XML tag attributes
	 */
	function startHandler($parser, $name, $attribs)
	{
		$_content = array('name' => $name);

		if (!empty($attribs))
		{
			$_content['attrs'] = $attribs;
		}

		array_push($this->output, $_content);
	}

	/**
	 * define the tag data handler
	 *
	 * @param   obj     $parser  the expat parser object
	 * @param   string  $data    the XML data
	 */
	function dataHandler($parser, $data)
	{
		if (!empty($data))
		{
			$_output_idx = count($this->output) - 1;

			if (!isset($this->output[$_output_idx]['content']))
			{
				$this->output[$_output_idx]['content'] = $data;
			}
			else
			{
				$this->output[$_output_idx]['content'] .= $data;
			}
		}
	}

	/**
	 * define the end tag handler
	 *
	 * @param   obj     $parser  the expat parser object
	 * @param   string  $name    the XML tag name
	 */
	function endHandler($parser, $name)
	{
		if (count($this->output) > 1)
		{
			$_data                                 = array_pop($this->output);
			$_output_idx                           = count($this->output) - 1;
			$this->output[$_output_idx]['child'][] = $_data;
		}
	}

}
