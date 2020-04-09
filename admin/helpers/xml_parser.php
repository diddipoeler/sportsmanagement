<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage helpers
 * @file       xml_parser.php
 * @author
 * @copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/*
 * Xml_parser.php
 *
 * @(#) $Header: /opt2/ena/metal/xmlparser/xml_parser.php,v 1.35 2012/09/22 06:01:56 mlemos Exp $
 *
 */

/*
 * Parser error numbers:
 */
define('XML_PARSER_NO_ERROR', 0);
define('XML_PARSER_CREATE_PARSER_ERROR', 1);
define('XML_PARSER_PARSE_DATA_ERROR', 2);
define('XML_PARSER_READ_INPUT_DATA_ERROR', 3);
define('XML_PARSER_VALIDATE_DATA_ERROR', 4);

$xml_parser_handlers = array();

Function xml_parser_start_element_handler($parser, $name, $attrs)
{
	global $xml_parser_handlers;

	if (!strcmp($xml_parser_handlers[$parser]->error, ""))
	{
		$xml_parser_handlers[$parser]->StartElement($xml_parser_handlers[$parser], $name, $attrs);
	}
}

Function xml_parser_end_element_handler($parser, $name)
{
	global $xml_parser_handlers;

	if (!strcmp($xml_parser_handlers[$parser]->error, ""))
	{
		$xml_parser_handlers[$parser]->EndElement($xml_parser_handlers[$parser], $name);
	}
}

Function xml_parser_character_data_handler($parser, $data)
{
	global $xml_parser_handlers;

	if (!strcmp($xml_parser_handlers[$parser]->error, ""))
	{
		$xml_parser_handlers[$parser]->CharacterData($xml_parser_handlers[$parser], $data);
	}
}

class xml_parser_handler_class
{
	var $xml_parser;

	var $error_number = XML_PARSER_NO_ERROR;

	var $error = '';

	var $error_code = 0;

	var $error_line, $error_column, $error_byte_index;

	var $structure = array();

	var $positions = array();

	var $path = '';

	var $store_positions = 0;

	var $simplified_xml = 0;

	var $fail_on_non_simplified_xml = 0;

	Function StartElement(&$object, $name, &$attrs)
	{
		if (strcmp($this->path, ""))
		{
			$element = $object->structure[$this->path]["Elements"];
			$object->structure[$this->path]["Elements"]++;
			$this->path .= ",$element";
		}
		else
		{
			$element    = 0;
			$this->path = "0";
		}

		$data = array(
			"Elements" => 0
		);

		if ($object->extract_namespaces
			&& ($colon = strcspn($name, ':')) < strlen($name)
		)
		{
			$data['Namespace'] = substr($name, 0, $colon);
			$data['Tag']       = substr($name, $colon + 1);
		}
		else
		{
			$data['Tag'] = $name;
		}

		if ($object->simplified_xml)
		{
			if ($object->fail_on_non_simplified_xml
				&& count($attrs) > 0
			)
			{
				$this->SetError($object, XML_PARSER_PARSE_DATA_ERROR, "Simplified XML can not have attributes in tags");

				return;
			}
		}
		elseif ($object->extract_namespaces)
		{
			$attributes = $namespaces = array();
			$ta         = count($attrs);

			for ($a = 0, Reset($attrs); $a < $ta; Next($attrs), ++$a)
			{
				$attr  = Key($attrs);
				$value = $attrs[$attr];

				if (($colon = strcspn($attr, ':')) < strlen($attr))
				{
					$attribute              = substr($attr, $colon + 1);
					$attributes[$attribute] = $value;
					$namespaces[$attribute] = substr($attr, 0, $colon);
				}
				else
				{
					$attributes[$attr] = $value;
				}
			}

			$data["Attributes"]          = $attributes;
			$data["AttributeNamespaces"] = $namespaces;
		}
		else
		{
			$data["Attributes"] = $attrs;
		}

		$this->SetElementData($object, $this->path, $data);
	}

	Function SetError(&$object, $error_number, $error)
	{
		$object->error_number     = $error_number;
		$object->error            = $error;
		$object->error_line       = xml_get_current_line_number($object->xml_parser);
		$object->error_column     = xml_get_current_column_number($object->xml_parser);
		$object->error_byte_index = xml_get_current_byte_index($object->xml_parser);
	}

	Function SetElementData(&$object, $path, &$data)
	{
		$object->structure[$path] = $data;

		if ($object->store_positions)
		{
			$object->positions[$path] = array(
				"Line"   => xml_get_current_line_number($object->xml_parser),
				"Column" => xml_get_current_column_number($object->xml_parser),
				"Byte"   => xml_get_current_byte_index($object->xml_parser)
			);
		}
	}

	Function EndElement(&$object, $name)
	{
		$this->path = (($position = strrpos($this->path, ",")) ? substr($this->path, 0, $position) : "");
	}

	Function CharacterData(&$object, $data)
	{
		$element  = $object->structure[$this->path]["Elements"];
		$previous = $this->path . "," . strval($element - 1);

		if ($element > 0
			&& GetType($object->structure[$previous]) == "string"
		)
		{
			$object->structure[$previous] .= $data;
		}
		else
		{
			$this->SetElementData($object, $this->path . ",$element", $data);
			$object->structure[$this->path]["Elements"]++;
		}
	}
}

;

/*
{metadocument}<?xml version="1.0" encoding="ISO-8859-1"?>
<class>

	<package>net.manuellemos.xmlparser</package>

	<version>@(#) $Id: xml_parser.php,v 1.35 2012/09/22 06:01:56 mlemos Exp $</version>
	<copyright>Copyright © (C) Manuel Lemos 1999-2012</copyright>
	<title>XML document parser</title>
	<author>Manuel Lemos</author>
	<authoraddress>mlemos@acm.org</authoraddress>

	<documentation>
		<idiom>en</idiom>
		<purpose>This class is meant to parse, validate and extract
			information from XML documents.</purpose>
		<usage>A XML document may be parsed using the
			<functionlink>Parse</functionlink> function by passing it the whole
			XML document data as a single string all at once or calling the
			function multiple times passing the XML document as separated data
			chunks.<paragraphbreak />
			Alternatively, the class can parse a XML document reading it from a
			given file using the <functionlink>ParseFile</functionlink> function
			or from a previously opened file or stream using the
			<functionlink>ParseStream</functionlink> function.<paragraphbreak />
			The <functionlink>ExtractElementData</functionlink> function can be
			used to validate and extract data from a XML document after it has
			been parsed.<paragraphbreak />
			It allows validating the XML document tag structure and data
			elements according to rules specific of common data
			types.<paragraphbreak />
			Custom types of tag element validation can be done by extending the
			class and implementing the
			<functionlink>ValidateElementData</functionlink> function in a
			subclass.</usage>
	</documentation>

{/metadocument}
*/

class xml_parser_class
{
	/*
    {metadocument}
    <variable>
    <name>error</name>
    <type>STRING</type>
    <value></value>
    <documentation>
    <purpose>Store the message of the last error that occured.</purpose>
    <usage>Check this variable to retrieve the reason of failure if a
				call to the class failed.</usage>
    </documentation>
    </variable>
    {/metadocument}
    */
	var $error = '';

	/*
    {metadocument}
    <variable>
    <name>error_number</name>
    <type>INTEGER</type>
    <value>0</value>
    <documentation>
    <purpose>Store the code of the last error that occured.</purpose>
    <usage>Check this variable to retrieve the number of the error
				that happened when a call to the class failed. Valid error code
				numbers are defined by constants:<paragraphbreak />
				<tt>XML_PARSER_NO_ERROR</tt> - no error happened<paragraphbreak />
				<tt>XML_PARSER_CREATE_PARSER_ERROR</tt> - failed to create the XML parser<paragraphbreak />
				<tt>XML_PARSER_PARSE_DATA_ERROR</tt> - failed to parse the XML document<paragraphbreak />
				<tt>XML_PARSER_READ_INPUT_DATA_ERROR</tt> - failed to read XML data from file<paragraphbreak />
				<tt>XML_PARSER_VALIDATE_DATA_ERROR</tt> - failed to validate XML data</usage>
    </documentation>
    </variable>
    {/metadocument}
    */
	var $error_number = XML_PARSER_NO_ERROR;

	/*
    {metadocument}
    <variable>
    <name>error_line</name>
    <type>INTEGER</type>
    <value>0</value>
    <documentation>
    <purpose>Store the number of the line of the XML document related to
				the last error that occured.</purpose>
    <usage>Check this variable to retrieve the number of the XML
				document line of the error that happened when a call to the class
				failed.<paragraphbreak />
				XML document line numbers start at <tt>1</tt>.</usage>
    </documentation>
    </variable>
    {/metadocument}
    */
	var $error_line = 0;

	/*
    {metadocument}
    <variable>
    <name>error_column</name>
    <type>INTEGER</type>
    <value>0</value>
    <documentation>
    <purpose>Store the number of the column of the XML document related
				to the last error that occured.</purpose>
    <usage>Check this variable to retrieve the number of the XML
				document column of the error that happened when a call to the class
				failed.<paragraphbreak />
				XML document column numbers start at <tt>1</tt>.</usage>
    </documentation>
    </variable>
    {/metadocument}
    */
	var $error_column = 0;

	/*
    {metadocument}
    <variable>
    <name>error_byte_index</name>
    <type>INTEGER</type>
    <value>0</value>
    <documentation>
    <purpose>Store the position of the byte of the XML document related
				to the last error that occured relatively to the beginning of the
				document.</purpose>
    <usage>Check this variable to retrieve the byte index of the XML
				document position of the error that happened when a call to the
				class failed.<paragraphbreak />
				XML document byte indices start at <tt>0</tt>.</usage>
    </documentation>
    </variable>
    {/metadocument}
    */
	var $error_byte_index = 0;

	/*
    {metadocument}
    <variable>
    <name>error_code</name>
    <type>INTEGER</type>
    <value>0</value>
    <documentation>
    <purpose>XML parser error code number</purpose>
    <usage>Check this variable to retrieve the original error code
				returned by the XML parser when the document parsing fails.</usage>
    </documentation>
    </variable>
    {/metadocument}
    */
	var $error_code = 0;

	/*
    {metadocument}
    <variable>
    <name>stream_buffer_size</name>
    <type>INTEGER</type>
    <value>4096</value>
    <documentation>
    <purpose>Size of the buffer of the chunks of the XML document file
				to be parsed</purpose>
    <usage>Increase this variable if you need to parse a XML document
				file in larger chunks to reduce document parsing time
				overhead.</usage>
    </documentation>
    </variable>
    {/metadocument}
    */
	var $stream_buffer_size = 4096;

	/*
    {metadocument}
    <variable>
    <name>structure</name>
    <type>HASH</type>
    <value></value>
    <documentation>
    <purpose>Structure of the parsed XML document</purpose>
    <usage>This variable is an associative array that has all elements
				of the parsed XML document.<paragraphbreak />

				The indexes are strings that contain numbers separated by commas.
				The numbers represent the order of the element inside the parent
				tag element. The order number of the first element inside a tag is
				<tt>0</tt>. The index of the root tag is <tt>'0'</tt>. The index
				of the first child element inside the root tag is <tt>'0,0'</tt>,
				and so on.<paragraphbreak />

				The values of each document element may be strings for data
				elements, or associative arrays for tag elements. The tag element
				arrays may contain the following entries:<paragraphbreak />

				<tt>'Tag'</tt> - string with the tag name<paragraphbreak />

				<tt>'Namespace'</tt> - string with the namespace of the tag if the
				<variablelink>extract_namespaces</variablelink> variable is set to
				<booleanvalue>1</booleanvalue>, otherwise the namespace is
				included in the <tt>'Tag'</tt> entry.<paragraphbreak />

				<tt>'Elements'</tt> - integer with the number of elements inside a
				tag<paragraphbreak />

				<tt>'Attributes'</tt> - associative arrays with the tag attributes
				and their values.<paragraphbreak />

				<tt>'AttributeNamespaces'</tt> - associative array with the
				namespaces of each of the tag attributes if the
				<variablelink>extract_namespaces</variablelink> variable is set to
				<booleanvalue>1</booleanvalue>, otherwise the attribute namespaces
				are included in the <tt>'Attributes'</tt> entries.</usage>
    </documentation>
    </variable>
    {/metadocument}
    */
	var $structure = array();

	/*
    {metadocument}
    <variable>
    <name>positions</name>
    <type>HASH</type>
    <value></value>
    <documentation>
    <purpose>Positions of each document element</purpose>
    <usage>This variable is an associative array that stores the
				positions of each element of the parsed XML document, if the
				<variablelink>store_positions</variablelink> variable is set to
				<booleanvalue>1</booleanvalue>.<paragraphbreak />

				The indexes are strings of the respective elements in the
				<variablelink>structure</variablelink> variable array.<paragraphbreak />

				The entry values are associative arrays with the <tt>Line</tt>,
				<tt>Column</tt> and <tt>Byte</tt> of the respective element.</usage>
    </documentation>
    </variable>
    {/metadocument}
    */
	var $positions = array();

	/*
    {metadocument}
    <variable>
    <name>store_positions</name>
    <type>BOOLEAN</type>
    <value>0</value>
    <documentation>
    <purpose>Set the class to keep track of the positions of the parsed
				XML document elements</purpose>
    <usage>Set this variable to <booleanvalue>1</booleanvalue> if you
				need to obtain the positions of XML document elements, in
				particular when a parsing error occurs.</usage>
    </documentation>
    </variable>
    {/metadocument}
    */
	var $store_positions = 0;

	/*
    {metadocument}
    <variable>
    <name>extract_namespaces</name>
    <type>BOOLEAN</type>
    <value>0</value>
    <documentation>
    <purpose>Set the class to extract the namespace names from tags and
				tag attributes.</purpose>
    <usage>Set this variable to <booleanvalue>1</booleanvalue> if you
				need to separate the namespaces from the tag names and attribute
				names.</usage>
    </documentation>
    </variable>
    {/metadocument}
    */
	var $extract_namespaces = 0;

	/*
    {metadocument}
    <variable>
    <name>case_folding</name>
    <type>BOOLEAN</type>
    <value>0</value>
    <documentation>
    <purpose>Set the class to convert the tag and attribute names to
				upper case.</purpose>
    <usage>Set this variable to <booleanvalue>1</booleanvalue> if you
				process the tags and attributes in a case insensitive way.</usage>
    </documentation>
    </variable>
    {/metadocument}
    */
	var $case_folding = 0;

	/*
    {metadocument}
    <variable>
    <name>target_encoding</name>
    <type>STRING</type>
    <value>ISO-8859-1</value>
    <documentation>
    <purpose>Set the class to convert the character encoding of the
				parsed XML document text</purpose>
    <usage>Set this variable to a specific character encoding if you
				need the parsed text values to be returned in that encoding.</usage>
    </documentation>
    </variable>
    {/metadocument}
    */
	var $target_encoding = 'ISO-8859-1';

	/*
    {metadocument}
    <variable>
    <name>simplified_xml</name>
    <type>BOOLEAN</type>
    <value>0</value>
    <documentation>
    <purpose>Set the class to parse documents in simplified XML
				documents which use no tag attributes.</purpose>
    <usage>Set this variable to <booleanvalue>1</booleanvalue> to gain
				some parsing time and spend less memory if the XML document to be
				parsed is not expected to have tags with attributes. In this case,
				namespaces are not extracted from tags.</usage>
    </documentation>
    </variable>
    {/metadocument}
    */
	var $simplified_xml = 0;

	/*
    {metadocument}
    <variable>
    <name>fail_on_non_simplified_xml</name>
    <type>BOOLEAN</type>
    <value>0</value>
    <documentation>
    <purpose>Set the class to fail in error if the document tags have
				attributes and the <variablelink>simplified_xml</variablelink>
				variable is set to <booleanvalue>1</booleanvalue>.</purpose>
    <usage>Set this variable to <booleanvalue>1</booleanvalue> if the
				XML document to parse uses tag attributes but those are not meant
				to be allowed.</usage>
    </documentation>
    </variable>
    {/metadocument}
    */
	var $fail_on_non_simplified_xml = 0;

	// Private variables
	var $xml_parser = 0;

	var $parser_handler;

	// Private functions

	Function xml_parser_start_element_handler($parser, $name, $attrs)
	{
		if (!strcmp($this->error, ""))
		{
			$this->parser_handler->StartElement($this, $name, $attrs);
		}
	}

	Function xml_parser_end_element_handler($parser, $name)
	{
		if (!strcmp($this->error, ""))
		{
			$this->parser_handler->EndElement($this, $name);
		}
	}

	Function xml_parser_character_data_handler($parser, $data)
	{
		if (!strcmp($this->error, ""))
		{
			$this->parser_handler->CharacterData($this, $data);
		}
	}

	Function ParseFile($file)
	{
		if (!($definition = @fopen($file, "r")))
		{
			return ("could not open the XML file ($file)" . (IsSet($php_errormsg) ? ': ' . $php_errormsg : ''));
		}

		$error = $this->ParseStream($definition);
		fclose($definition);

		return ($error);
	}

	Function ParseStream($stream)
	{
		if (strcmp($this->error, ""))
		{
			return ($this->error);
		}

		do
		{
			if (!($data = @fread($stream, $this->stream_buffer_size)))
			{
				if (!feof($stream))
				{
					$this->SetError(XML_PARSER_READ_INPUT_DATA_ERROR, "Could not read from input stream" . (IsSet($php_errormsg) ? ': ' . $php_errormsg : ''));
					break;
				}
			}

			if (strcmp($error = $this->Parse($data, feof($stream)), ""))
			{
				break;
			}
		} while (!feof($stream));

		return ($this->error);
	}

	Function SetError($error_number, $error)
	{
		$this->error_number = $error_number;
		$this->error        = $error;

		if ($this->xml_parser)
		{
			$line       = xml_get_current_line_number($this->xml_parser);
			$column     = xml_get_current_column_number($this->xml_parser);
			$byte_index = xml_get_current_byte_index($this->xml_parser);
		}
		else
		{
			$line       = $column = 1;
			$byte_index = 0;
		}

		$this->SetErrorPosition($error_number, $error, $line, $column, $byte_index);
	}

	Function SetErrorPosition($error_number, $error, $line, $column, $byte_index)
	{
		$this->error_number     = $error_number;
		$this->error            = $error;
		$this->error_line       = $line;
		$this->error_column     = $column;
		$this->error_byte_index = $byte_index;

		return ($error);
	}

	/*
    {metadocument}
    <function>
    <name>Parse</name>
    <type>STRING</type>
    <documentation>
    <purpose>Parse a XML document from data.</purpose>
    <usage>Pass the XML document data to the <argumentlink>
					<argument>data</argument>
					<function>Parse</function>
				</argumentlink> parameter. The data of the document may be passed
					all at once or one chunk at a time. The <argumentlink>
					<argument>end_of_data</argument>
					<function>Parse</function>
				</argumentlink> parameter should be set to
				<booleanvalue>1</booleanvalue> if the current chunk is the last
				chunk of the XML document.</usage>
    <returnvalue>Message string of an error that occured when this
				function was called. An empty string means that no error
				occured.</returnvalue>
    </documentation>
    <argument>
    <name>data</name>
    <type>STRING</type>
    <documentation>
				<purpose>The XML document data to be parsed.</purpose>
    </documentation>
    </argument>
    <argument>
    <name>end_of_data</name>
    <type>BOOLEAN</type>
    <documentation>
				<purpose>Flag that determines if the data being passed is the last
					chunk of the XML document.</purpose>
    </documentation>
    </argument>
    <do>
    {/metadocument}
    */

	Function Parse($data, $end_of_data)
	{
		global $xml_parser_handlers;

		if (strcmp($this->error, ""))
		{
			return ($this->error);
		}

		if (!$this->xml_parser)
		{
			if (!function_exists("xml_parser_create"))
			{
				$this->SetError(XML_PARSER_CREATE_PARSER_ERROR, "XML support is not available in this PHP configuration");

				return ($this->error);
			}

			if (!($this->xml_parser = xml_parser_create()))
			{
				$this->SetError(XML_PARSER_CREATE_PARSER_ERROR, "Could not create the XML parser");

				return ($this->error);
			}

			xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, $this->case_folding);
			xml_parser_set_option($this->xml_parser, XML_OPTION_TARGET_ENCODING, $this->target_encoding);

			if (function_exists("xml_set_object"))
			{
				xml_set_object($this->xml_parser, $this);
				$this->parser_handler = new xml_parser_handler_class;
				$this->structure      = array();
				$this->positions      = array();
			}
			else
			{
				$xml_parser_handlers[$this->xml_parser]                             = new xml_parser_handler_class;
				$xml_parser_handlers[$this->xml_parser]->xml_parser                 = $this->xml_parser;
				$xml_parser_handlers[$this->xml_parser]->store_positions            = $this->store_positions;
				$xml_parser_handlers[$this->xml_parser]->simplified_xml             = $this->simplified_xml;
				$xml_parser_handlers[$this->xml_parser]->fail_on_non_simplified_xml = $this->fail_on_non_simplified_xml;
			}

			xml_set_element_handler($this->xml_parser, "xml_parser_start_element_handler", "xml_parser_end_element_handler");
			xml_set_character_data_handler($this->xml_parser, "xml_parser_character_data_handler");
		}

		$parser_ok = xml_parse($this->xml_parser, $data, $end_of_data);

		if (!function_exists("xml_set_object"))
		{
			$this->error = $xml_parser_handlers[$this->xml_parser]->error;
		}

		if (!strcmp($this->error, ""))
		{
			if ($parser_ok)
			{
				if ($end_of_data)
				{
					if (function_exists("xml_set_object"))
					{
						Unset($this->parser_handler);
					}
					else
					{
						$this->structure = $xml_parser_handlers[$this->xml_parser]->structure;
						$this->positions = $xml_parser_handlers[$this->xml_parser]->positions;
						Unset($xml_parser_handlers[$this->xml_parser]);
					}

					xml_parser_free($this->xml_parser);
					$this->xml_parser = 0;
				}
			}
			else
			{
				$this->SetError(XML_PARSER_PARSE_DATA_ERROR, "Could not parse data: " . xml_error_string($this->error_code = xml_get_error_code($this->xml_parser)));
			}
		}
		else
		{
			if (!function_exists("xml_set_object"))
			{
				$this->error_number     = $xml_parser_handlers[$this->xml_parser]->error_number;
				$this->error_code       = $xml_parser_handlers[$this->xml_parser]->error_code;
				$this->error_line       = $xml_parser_handlers[$this->xml_parser]->error_line;
				$this->error_column     = $xml_parser_handlers[$this->xml_parser]->error_column;
				$this->error_byte_index = $xml_parser_handlers[$this->xml_parser]->error_byte_index;
			}
		}

		return ($this->error);
	}
	/*
    {metadocument}
    </do>
    </function>
    {/metadocument}
    */

	/*
    {metadocument}
    <function>
    <name>ParseStream</name>
    <type>STRING</type>
    <documentation>
    <purpose>Parse a XML document from an opened file or stream.</purpose>
    <usage>Pass an already opened file or stream to the <argumentlink>
					<argument>stream</argument>
					<function>ParseStream</function>
				</argumentlink> parameter.</usage>
    <returnvalue>Message string of an error that occured when this
				function was called. An empty string means that no error
				occured.</returnvalue>
    </documentation>
    <argument>
    <name>stream</name>
    <type>INTEGER</type>
    <documentation>
				<purpose>The file or stream handler from which the XML document
					data will be retrieved.</purpose>
    </documentation>
    </argument>
    <do>
    {/metadocument}
    */

	Function GetTagValue($path, &$value)
	{
		if (!IsSet($this->structure[$path]))
		{
			return ($this->SetErrorPosition(XML_PARSER_PARSE_DATA_ERROR, $path . ' element path does not exist', 1, 1, 0));
		}

		$tag = $this->structure[$path];

		if (GetType($tag) != "array")
		{
			return ($this->SetPathErrorPosition($path, XML_PARSER_PARSE_DATA_ERROR, 'element is not tag'));
		}

		$value = '';
		$te    = $tag['Elements'];

		for ($e = 0; $e < $te; ++$e)
		{
			$element_path = $path . ',' . $e;
			$data         = $this->structure[$element_path];

			if (GetType($data) != 'string')
			{
				return ($this->SetPathErrorPosition($element_path, XML_PARSER_PARSE_DATA_ERROR, 'tag has elements that are not data'));
			}

			$value .= $data;
		}

		return ('');
	}
	/*
    {metadocument}
    </do>
    </function>
    {/metadocument}
    */

	/*
    {metadocument}
    <function>
    <name>ParseFile</name>
    <type>STRING</type>
    <documentation>
    <purpose>Parse a XML document read from a file.</purpose>
    <usage>Pass the name of the file to the <argumentlink>
					<argument>file</argument>
					<function>ParseFile</function>
				</argumentlink> parameter.</usage>
    <returnvalue>Message string of an error that occured when this
				function was called. An empty string means that no error
				occured.</returnvalue>
    </documentation>
    <argument>
    <name>file</name>
    <type>STRING</type>
    <documentation>
				<purpose>Name of XML document file to be parsed.</purpose>
    </documentation>
    </argument>
    <do>
    {/metadocument}
    */

	Function SetPathErrorPosition($path, $error_number, $error)
	{
		if ($this->store_positions)
		{
			$line       = $this->positions[$path]["Line"];
			$column     = $this->positions[$path]["Column"];
			$byte_index = $this->positions[$path]["Byte"];
		}
		else
		{
			$line       = $column = 1;
			$byte_index = 0;
		}

		return ($this->SetErrorPosition($error_number, $error, $line, $column, $byte_index));
	}

	/*
    {metadocument}
    </do>
    </function>
    {/metadocument}
    */

	Function ExtractElementData($path, $name, $types, $hash, &$values)
	{
		$values   = array();
		$required = $types;

		if (!IsSet($this->structure[$path]))
		{
			if (strlen($path))
			{
				return ($this->SetErrorPosition(XML_PARSER_PARSE_DATA_ERROR, $path . ' element path does not exist', 1, 1, 0));
			}

			$elements = 1;
		}
		else
		{
			$elements = $this->structure[$path]['Elements'];
		}

		for ($index = $element = 0; $element < $elements; $element++)
		{
			$element_path = (strlen($path) ? $path . ',' : '') . $element;
			$data         = $this->structure[$element_path];

			if (GetType($data) == 'array')
			{
				$type = $data['Tag'];

				if (!IsSet($types[$type]))
				{
					return ($this->SetPathErrorPosition($element_path, XML_PARSER_VALIDATE_DATA_ERROR, strlen($path) ? 'unexpected ' . $name . ' element "' . $type . '"' : 'the XML document is not valid ' . $name . ' definition'));
				}

				$t = $types[$type];

				if (!IsSet($t['type']))
				{
					return ($this->SetPathErrorPosition($element_path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was not specified the ' . $name . ' ' . $type . ' element type'));
				}

				$expected_type = $t['type'];
				$minimum       = (IsSet($t['minimum']) ? $t['minimum'] : 1);
				$maximum       = (IsSet($t['maximum']) ? $t['maximum'] : 1);

				if (strcmp($maximum, '*'))
				{
					if ($minimum < 0
						|| $minimum > $maximum
					)
					{
						return ($this->SetPathErrorPosition($element_path, XML_PARSER_VALIDATE_DATA_ERROR, $name . ' element "' . $type . '" is set to an invalid minimum value'));
					}

					$current = (IsSet($values[$type]) ? (GetType($values[$type]) == 'array' ? count($values[$type]) : 1) : 0);

					if ($current >= $maximum)
					{
						return ($this->SetPathErrorPosition($element_path, XML_PARSER_VALIDATE_DATA_ERROR, $name . ' element "' . $type . '" is defined more than ' . ($maximum == 1 ? 'once' : $maximum . ' times')));
					}
				}

				if (strcmp($expected_type, 'path')
					&& strcmp($expected_type, 'hash')
					&& strcmp($expected_type, 'array')
				)
				{
					if ($data['Elements'] > 1
						|| ($data['Elements'] == 1
							&& GetType($this->structure[$element_path . ',0']) == 'array')
					)
					{
						return ($this->SetPathErrorPosition($element_path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was not specified a valid ' . $name . ' element "' . $type . '" value'));
					}

					$value = ($data['Elements'] == 1 ? $this->structure[$element_path . ',0'] : '');
				}

				switch ($expected_type)
				{
					case 'text':
					case 'integer':
					case 'decimal':
					case 'boolean':
					case 'date':
						if (strlen($error = $this->ValidateTypedValue($value, $expected_type, $element_path, $t, $v)))
						{
							return $error;
						}
						break;
					case 'path':
						$v = $element_path;
						break;
					case 'array':
					case 'hash':
						if (!IsSet($t['types'])
							|| GetType($t['types']) != 'array'
						)
						{
							return ($this->SetPathErrorPosition($element_path, XML_PARSER_PARSE_DATA_ERROR, 'it was not specified a valid list of types elements for ' . $type . ' element'));
						}

						$child_hash = !strcmp($expected_type, 'hash');

						if (strlen($error = $this->ExtractElementData($element_path, $type, $t['types'], $child_hash, $v)))
						{
							return $error;
						}

						if (IsSet($t['attributes']))
						{
							$a     = $t['attributes'];
							$total = count($a);

							for (Reset($a), $ia = 0; $ia < $total; Next($a), ++$ia)
							{
								$attribute_name = Key($a);
								$ta             = $a[$attribute_name];

								if (!IsSet($ta['type']))
								{
									return ($this->SetPathErrorPosition($element_path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was not specified the ' . $name . ' ' . $type . ' attribute ' . $attribute_name . ' type'));
								}

								if (!IsSet($data['Attributes'][$attribute_name]))
								{
									if (IsSet($ta['optional'])
										&& $ta['optional']
									)
									{
										continue;
									}

									return ($this->SetPathErrorPosition($element_path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was not specified the ' . $name . ' ' . $type . ' attribute ' . $attribute_name . ' value'));
								}

								if (strlen($error = $this->ValidateTypedValue($data['Attributes'][$attribute_name], $ta['type'], $element_path, $ta, $av)))
								{
									return $error;
								}

								if (IsSet($ta['validation']))
								{
									$result = array();

									if (strlen($error = $this->ValidateElementData($ta['validation'], $element_path, $va, $result)))
									{
										return ($this->SetPathErrorPosition($element_path, XML_PARSER_PARSE_DATA_ERROR, $error));
									}

									if (IsSet($result['error']))
									{
										return ($this->SetPathErrorPosition(IsSet($result['path']) ? $result['path'] : $element_path, XML_PARSER_VALIDATE_DATA_ERROR, strlen($result['error']) ? $result['error'] : 'the ' . $name . ' attribute failed the ' . $t['validation'] . ' validation'));
									}
								}

								if ($child_hash)
								{
									$v[$attribute_name] = $av;
								}
								else
								{
									$v[count($v)] = array('type' => $attribute_name, 'value' => $av);
								}
							}
						}
						break;
					default:
						return ($this->SetPathErrorPosition($element_path, XML_PARSER_PARSE_DATA_ERROR, $name . ' element "' . $type . '" type "' . $expected_type . '" is not yet supported'));
				}

				if (IsSet($t['validation']))
				{
					$result = array();

					if (strlen($error = $this->ValidateElementData($t['validation'], $element_path, $v, $result)))
					{
						return ($this->SetPathErrorPosition($element_path, XML_PARSER_PARSE_DATA_ERROR, $error));
					}

					if (IsSet($result['error']))
					{
						return ($this->SetPathErrorPosition(IsSet($result['path']) ? $result['path'] : $element_path, XML_PARSER_VALIDATE_DATA_ERROR, strlen($result['error']) ? $result['error'] : 'the ' . $name . ' element failed the ' . $t['validation'] . ' validation'));
					}
				}

				$i = ($hash ? $type : $index++);

				if ($hash
					&& (!strcmp($maximum, '*')
						|| $maximum > 1)
				)
				{
					$values[$i][] = $v;
					$current      = count($values[$i]);
				}
				else
				{
					$values[$i] = ($hash ? $v : array('type' => $type, 'value' => $v));
					$current    = 1;
				}

				if (IsSet($required[$type])
					&& $current >= $minimum
				)
				{
					UnSet($required[$type]);
				}
			}
			else
			{
				if (strlen($error = $this->VerifyWhiteSpace($element_path)))
				{
					return ($this->SetPathErrorPosition($element_path, XML_PARSER_PARSE_DATA_ERROR, $error));
				}
			}
		}

		if (count($required))
		{
			$tr = count($required);

			for (Reset($required), $r = 0; $r < $tr; Next($required), ++$r)
			{
				$element = Key($required);
				$t       = $required[$element];
				$minimum = (IsSet($t['minimum']) ? $t['minimum'] : 1);

				if ($minimum > 0)
				{
					return ($this->SetPathErrorPosition($element_path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was not specified the ' . $name . ' element "' . $element . '"' . ($minimum > 1 ? ' at least ' . $minimum . ' times' : '')));
				}
			}
		}

		return '';
	}

	Function ValidateTypedValue($value, $type, $path, $parameters, &$v)
	{
		switch ($type)
		{
			case 'text':
				$v = $value;

				if (IsSet($parameters['minimumlength'])
					&& strlen($v) < $parameters['minimumlength']
				)
				{
					return ($this->SetPathErrorPosition($path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was specified a text value with length below the limit of ' . $parameters['minimumlength'] . ' ' . ($parameters['minimumlength'] == 1 ? 'character' : 'characters')));
				}

				if (IsSet($parameters['maximumlength'])
					&& strlen($v) > $parameters['maximumlength']
				)
				{
					return ($this->SetPathErrorPosition($path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was specified a text value with length above the limit of ' . $parameters['maximumlength'] . ' ' . ($parameters['maximumlength'] == 1 ? 'character' : 'characters')));
				}
				break;
			case 'integer':
				if (strcmp($value, intval($value)))
				{
					return ($this->SetPathErrorPosition($path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was not specified a valid integer value'));
				}

				$v = intval($value);

				if (IsSet($parameters['minimumvalue'])
					&& $v < $parameters['minimumvalue']
				)
				{
					return ($this->SetPathErrorPosition($path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was specified a integer value below the limit of ' . $parameters['minimumvalue']));
				}

				if (IsSet($parameters['maximumvalue'])
					&& $v > $parameters['maximumvalue']
				)
				{
					return ($this->SetPathErrorPosition($path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was specified a integer value above the limit of ' . $parameters['maximumvalue']));
				}
				break;
			case 'decimal':
				if (!preg_match('/^[0-9]+(.[0-9]*)?$/', $value))
				{
					return ($this->SetPathErrorPosition($path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was not specified a valid decimal value'));
				}

				$v = $value;

				if (IsSet($parameters['minimumvalue'])
					&& $v < $parameters['minimumvalue']
				)
				{
					return ($this->SetPathErrorPosition($path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was specified a decimal value below the limit of ' . $parameters['minimumvalue']));
				}

				if (IsSet($parameters['maximumvalue'])
					&& $v > $parameters['maximumvalue']
				)
				{
					return ($this->SetPathErrorPosition($path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was specified a decimal value above the limit of ' . $parameters['maximumvalue']));
				}
				break;
			case 'boolean':
				switch (strtolower($value))
				{
					case 'true':
					case 't':
					case '1':
					case 'y':
					case 'yes':
						$v = 1;
						break;
					case 'false':
					case 'f':
					case '0':
					case 'n':
					case 'no':
						$v = 0;
						break;
					default:
						return ($this->SetPathErrorPosition($path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was not specified a valid boolean value'));
				}
				break;
			case 'date':
				if (!strcmp($value, 'now'))
				{
					$v = $value;
					break;
				}

				if (!preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $value, $matches))
				{
					return ($this->SetPathErrorPosition($path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was not specified a valid date value'));
				}

				$year  = $matches[1];
				$month = $matches[2];
				$day   = $matches[3];

				if (strlen($year))
				{
					while (!strcmp($year[0], "0"))
					{
						$year = substr($year, 1);
					}
				}

				if (strcmp($year, intval($year))
					|| intval($year) <= 0
				)
				{
					return ($this->SetPathErrorPosition($path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was not specified a valid date year'));
				}

				switch ($month)
				{
					case "01":
					case "03":
					case "05":
					case "07":
					case "08":
					case "10":
					case "12":
						$month_days = 31;
						break;
					case "02":
						$is_leap_year = (($year % 4) == 0 && (($year % 100) != 0 || ($year % 400) == 0));
						$month_days   = ($is_leap_year ? 29 : 28);
						break;
					case "04":
					case "06":
					case "09":
					case "11":
						$month_days = 30;
						break;
					default:
						return ($this->SetPathErrorPosition($path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was not specified a valid date month'));
				}

				if (strlen($day))
				{
					while (!strcmp($day[0], "0"))
					{
						$day = substr($day, 1);
					}
				}

				if (strcmp($day, intval($day))
					|| $day > $month_days
				)
				{
					return ($this->SetPathErrorPosition($path, XML_PARSER_VALIDATE_DATA_ERROR, 'it was not specified a valid date day'));
				}

				$v = $value;
				break;
			default:
				return ($this->SetPathErrorPosition($path, XML_PARSER_PARSE_DATA_ERROR, 'type "' . $type . '" is not yet supported'));
		}

		return ('');
	}

	/*
    {metadocument}
    <function>
    <name>ValidateElementData</name>
    <type>STRING</type>
    <documentation>
    <purpose>Validate values of parsed XML document elements</purpose>
    <usage>This function is called by the
				<functionlink>ExtractElementData</functionlink> function to
				perform a custom type of validation of tag data or attribute
				values.<paragraphbreak />
				Extend this class and implement this function in a subclass to
				provide custom element data validation types.</usage>
    <returnvalue>This function should return an non-empty string as
				error message when an error occurs. Otherwise it should return an
				empty string even when the value is considered invalid.</returnvalue>
    </documentation>
    <argument>
    <name>validation</name>
    <type>STRING</type>
    <documentation>
				<purpose>Name of the custom validation to be performed.</purpose>
    </documentation>
    </argument>
    <argument>
    <name>path</name>
    <type>STRING</type>
    <documentation>
				<purpose>Path of the element value being validated. It is the
					entry index of the element in the
					<variablelink>structure</variablelink> variable.</purpose>
    </documentation>
    </argument>
    <argument>
    <name>value</name>
    <type>STRING</type>
    <inout />
    <documentation>
				<purpose>Value to be validated.</purpose>
    </documentation>
    </argument>
    <argument>
    <name>result</name>
    <type>HASH</type>
    <inout />
    <documentation>
				<purpose>Associative array that should return the result of the
					validation.<paragraphbreak />
					This array should have an entry named <tt>error</tt> set to an
					error message in case there was a validation error. The error
					entry should not be set if the value is considered
					valid.<paragraphbreak />
					An optional entry named <tt>path</tt> should be set to the path
					of the element to which the eventual validation error is related
					in case the path is different from the original element path
					passed by the <argumentlink>
						<function>ValidateElementData</function>
						<argument>path</argument>
					</argumentlink>.</purpose>
    </documentation>
    </argument>
    <do>
    {/metadocument}
    */

	Function ValidateElementData($validation, $path, &$value, &$result)
	{
		return ($validation . ' is not a supported type of validation');
	}
	/*
    {metadocument}
    </do>
    </function>
    {/metadocument}
    */

	/*
    {metadocument}
    <function>
    <name>ExtractElementData</name>
    <type>STRING</type>
    <documentation>
    <purpose>Validate and extract values of parsed XML document according to given rules</purpose>
    <usage>Call this function after having parsed a XML document to
				validate document elements and extract element values.</usage>
    <returnvalue>Message string of an error that occured when this
				function was called. An empty string means that no error
				occured.</returnvalue>
    </documentation>
    <argument>
    <name>path</name>
    <type>STRING</type>
    <documentation>
				<purpose>Path of the parent of the XML documents to be
					processed.<paragraphbreak />
					It should be set to <stringvalue>0</stringvalue> if it is
					intended to process the children elements of the root tag. An
					empty string should be passed if the processing should start
					from above the root element.</purpose>
    </documentation>
    </argument>
    <argument>
    <name>name</name>
    <type>STRING</type>
    <documentation>
				<purpose>Name of the type of document being processed. It could be
				anything. It is only used in eventual validation error messages.</purpose>
    </documentation>
    </argument>
    <argument>
    <name>types</name>
    <type>HASH</type>
    <documentation>
				<purpose>Associative array with parameters that define rules on
					how element values should be processed and
					extracted.<paragraphbreak />

					This array should contain entries with the names of each tag
					element that is allowed inside the element with the path
					specified by the <argumentlink>
						<argument>path</argument>
						<function>ExtractElementData</function>
					</argumentlink> argument.<paragraphbreak />

					The values of each tag entry should also be associative arrays
					with the respective tag element rule parameters. The rule
					parameters should be:<paragraphbreak />

					<tt>type</tt> - (required) Type of the element value. Supported
					values are: <tt>text</tt>, <tt>integer</tt>, <tt>decimal</tt>,
					<tt>boolean</tt>, <tt>date</tt>, <tt>path</tt> (for just
					returning the path of the element), <tt>array</tt> and
					<tt>hash</tt> (to return an array of contained tag
					elements).<paragraphbreak />

					<tt>minimum</tt> - Minimum number of times a tag element with
						the same name may appear. The default is
						<tt>1</tt>.<paragraphbreak />

					<tt>maximum</tt> - Maximum number of times a tag element with
						the same name may appear. The default is <tt>1</tt>.
						<stringvalue>*</stringvalue> means unlimited.<paragraphbreak />

					<tt>validation</tt> - Name of a custom validation type to be
						applied. The function will call
						<functionlink>ValidateElementData</functionlink> to perform
						the specified validation type.<paragraphbreak />

					Parameters for specific types:<paragraphbreak />
					For <tt>hash</tt> and <tt>array</tt> types:<paragraphbreak />

					<tt>types</tt> - Associative array with the rules for all the
						tags allowed inside the current tag. It is the same as the
						<argumentlink>
							<argument>types</argument>
							<function>ExtractElementData</function>
						</argumentlink> argument.<paragraphbreak />

					<tt>attributes</tt> - Associative array with the names and rules
						of the allowed attributes for the current tag. Each attribute
						rule must contain a <tt>type</tt> parameter that is the same
						as the tag element type parameter except that it cannot be
						<tt>hash</tt>, <tt>hash</tt> or <tt>array</tt>.<paragraphbreak />
						All the type specific parameters are allowed. The
						<tt>validation</tt> parameter may also specify a custom
						validation type. Optional attributes must have set the
						parameter <tt>optional</tt> to
						<booleanvalue>1</booleanvalue>.<paragraphbreak />


					For <tt>text</tt> type:<paragraphbreak />
					<tt>minimumlength</tt> - Minimum length of the value. The
					default is <integervalue>0</integervalue><paragraphbreak />
					<tt>maximumlength</tt> - Maximum length of the value. The default
					is unlimited.<paragraphbreak />

					For <tt>integer</tt> type:<paragraphbreak />
					<tt>minimumvalue</tt> - Minimum allowed value. The default is
					unlimited.<paragraphbreak />
					<tt>maximumvalue</tt> - Maximum allowed value. The default is
					unlimited.<paragraphbreak />

					Special type specific values:<paragraphbreak />
					For <tt>boolean</tt>:<paragraphbreak />

					True may be <tt>true</tt>, <tt>t</tt>, <tt>1</tt>, <tt>y</tt>,
					<tt>yes</tt><paragraphbreak />
					False may be <tt>false</tt>, <tt>f</tt>, <tt>0</tt>, <tt>n</tt>,
					<tt>no</tt><paragraphbreak />

					For <tt>date</tt>:<paragraphbreak />
					The current day date may be <tt>now</tt>
					</purpose>
    </documentation>
    </argument>
    <argument>
    <name>hash</name>
    <type>BOOLEAN</type>
    <documentation>
				<purpose>Flag that determines if the extracted values should be
					returned as an associative array or as a regular array.</purpose>
    </documentation>
    </argument>
    <argument>
    <name>values</name>
    <type>HASH</type>
    <out />
    <documentation>
				<purpose>Array with the extracted element values.</purpose>
    </documentation>
    </argument>
    <do>
    {/metadocument}
    */

	Function VerifyWhiteSpace($path)
	{
		if (!IsSet($this->structure[$path]))
		{
			return ($this->SetErrorPosition(XML_PARSER_PARSE_DATA_ERROR, $path . ' element path does not exist', 1, 1, 0));
		}

		if ($this->store_positions)
		{
			$line       = $this->positions[$path]['Line'];
			$column     = $this->positions[$path]['Column'];
			$byte_index = $this->positions[$path]['Byte'];
		}
		else
		{
			$line       = $column = 1;
			$byte_index = 0;
		}

		if (GetType($this->structure[$path]) != 'string')
		{
			$this->SetErrorPosition(XML_PARSER_PARSE_DATA_ERROR, 'element is not data', $line, $column, $byte_index);

			return ($this->error);
		}

		$data = $this->structure[$path];

		for ($previous_return = 0, $position = 0; $position < strlen($data); $position++)
		{
			switch ($data[$position])
			{
				case " ":
				case "\t":
					$column++;
					$byte_index++;
					$previous_return = 0;
					break;
				case "\n":
					if (!$previous_return)
					{
						$line++;
					}

					$column = 1;
					$byte_index++;
					$previous_return = 0;
					break;
				case "\r":
					$line++;
					$column = 1;
					$byte_index++;
					$previous_return = 1;
					break;
				default:
					$this->SetErrorPosition(XML_PARSER_PARSE_DATA_ERROR, 'data is not white space', $line, $column, $byte_index);

					return ($this->error);
			}
		}

		return ("");
	}
	/*
    {metadocument}
    </do>
    </function>
    {/metadocument}
    */
}

;

/*
{metadocument}
</class>
{/metadocument}

*/

Function XMLParseFile(&$parser, $file, $store_positions, $cache = "", $case_folding = 0, $target_encoding = "ISO-8859-1", $simplified_xml = 0, $fail_on_non_simplified_xml = 0)
{
	if (strcmp($cache, ""))
	{
		if (file_exists($cache)
			&& GetType($last_modified = @filemtime($file)) == 'integer'
			&& $last_modified <= filemtime($cache)
		)
		{
			if (($cache_file = @fopen($cache, "r")))
			{
				if (function_exists("set_file_buffer"))
				{
					set_file_buffer($cache_file, 0);
				}

				if (!($cache_contents = @fread($cache_file, filesize($cache))))
				{
					$error = "could not read from the XML cache file $cache" . (IsSet($php_errormsg) ? ': ' . $php_errormsg : '');
				}
				else
				{
					$error = "";
				}

				fclose($cache_file);

				if (!strcmp($error, ""))
				{
					if (GetType($parser = unserialize($cache_contents)) == "object"
						&& IsSet($parser->structure)
					)
					{
						if (!IsSet($parser->simplified_xml))
						{
							$parser->simplified_xml = 0;
						}

						if (($simplified_xml
								|| !$parser->simplified_xml)
							&& (!$store_positions
								|| $parser->store_positions)
						)
						{
							return ("");
						}
					}
					else
					{
						$error = "it was not specified a valid cache object in XML file ($cache)";
					}
				}
			}
			else
			{
				$error = "could not open cache XML file ($cache)" . (IsSet($php_errormsg) ? ': ' . $php_errormsg : '');
			}

			if (strcmp($error, ""))
			{
				return ($error);
			}
		}
	}

	$parser                             = new xml_parser_class;
	$parser->store_positions            = $store_positions;
	$parser->case_folding               = $case_folding;
	$parser->target_encoding            = $target_encoding;
	$parser->simplified_xml             = $simplified_xml;
	$parser->fail_on_non_simplified_xml = $fail_on_non_simplified_xml;

	if (!strcmp($error = $parser->ParseFile($file), "")
		&& strcmp($cache, "")
	)
	{
		if (($cache_file = @fopen($cache, "w")))
		{
			if (function_exists("set_file_buffer"))
			{
				set_file_buffer($cache_file, 0);
			}

			if (!@fwrite($cache_file, serialize($parser))
				|| !@fclose($cache_file)
			)
			{
				$error = "could to write to the XML cache file ($cache)" . (IsSet($php_errormsg) ? ': ' . $php_errormsg : '');
			}

			if (strcmp($error, ""))
			{
				unlink($cache);
			}
		}
		else
		{
			$error = "could not open for writing to the cache file ($cache)" . (IsSet($php_errormsg) ? ': ' . $php_errormsg : '');
		}
	}

	return ($error);
}

