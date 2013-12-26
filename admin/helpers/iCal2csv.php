<?php
/**
 * iCal2csv
 * ver 2.0
 *
 * copyright (c) 2009 Kjell-Inge Gustafsson kigkonsult
 * www.kigkonsult.se/index.php
 * ical@kigkonsult.se
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
**/
/**
 * function iCal2csv
 *
 * Convert iCal file to csv format and send file to browser (default) or save csv file to disk
 * Definition iCal  : rcf2445, http://localhost/work/kigkonsult.se/downloads/index.php#rfc2445
 * Definition csv   : http://en.wikipedia.org/wiki/Comma-separated_values
 * Using iCalcreator: http://localhost/work/kigkonsult.se/downloads/index.php#iCalcreator
 * ical directory/file read/write error OR iCalcreator parse error will be directed to error_log/log
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 2.0 - 2009-03-27
 * @param string $filename      file to convert (incl. opt. directory)
 * @param array  $conf          opt, default FALSE(=array('del'=>'"','sep'=>',', 'nl'=>'\n'), delimiter, separator and newline characters
 *                              escape sequences will be expanded, '\n' will be used as "\n" etc.
 *                              also map iCal property names to user friendly names, ex. 'DTSTART' => 'startdate'
 *                              also order output columns, ex. 2 => 'DTSTART' (2=first order column, 3 next etc)
 *                              also properties to skip, ex. 'skip' => array( 'CREATED', 'PRIORITY' );
 * @param bool   $save          opt, default FALSE, TRUE=save to disk
 * @param string $diskfilename  opt, filename for file to save or else taken from $filename + 'csv' extension
 * @param object $log           opt, default FALSE (error_log), writes log to file using PEAR LOG or eClog class
 * @return bool                 returns FALSE when error
 */
function iCal2csv( $filename, $conf=FALSE, $save=FALSE, $diskfilename=FALSE, $log=FALSE ) {
  if( $log ) $timeexec = array( 'start' => microtime( TRUE ));
  $iCal2csv_VERSION = 'iCal2csv 2.0';
  if( !function_exists( 'fileCheckRead' ))
    require_once 'fileCheck.php';
  if( !class_exists( 'vcalendar', FALSE ))
    require_once 'iCalcreator.class.php';
  if( $log ) $log->log( "$iCal2csv_VERSION input=$filename, conf=".var_export($conf,TRUE).", save=$save, diskfilename=$diskfilename", 7 );
  $remoteInput = (( 'http://' == strtolower( substr( $filename, 0, 7 ))) || ( 'webcal://' == strtolower( substr( $filename, 0, 9 )))) ? TRUE : FALSE;
  // field DELimiter && field SEParator && NewLine character(-s) etc.
  if( !$conf ) $conf = array();
  if( !isset( $conf['del'] ))
    $conf['del'] = '"';
  if( !isset( $conf['sep'] ))
    $conf['sep'] = ',';
  if( !isset( $conf['nl'] ))
    $conf['nl']  = "\n";
  foreach( $conf as $key => $value ) {
    if( 'skip' == $key ) {
      foreach( $value as $six => $skipp )
        $conf['skip'][$six] = strtoupper( $skipp );
    }
    elseif(( '2' <= $key) && ( '99' > $key )) {
      $conf[$key] = strtoupper( $value );
      if( $log ) $log->log( "$iCal2csv_VERSION column $key contains ".strtoupper( $value ), 7 );
    }
    elseif( in_array( $key, array( 'del', 'sep', 'nl' )))
      $conf[$key] = "$value";
    else {
      $conf[strtoupper( $key )] = $value;
      if( $log ) $log->log( "$iCal2csv_VERSION ".strtoupper( $key )." mapped to $value", 7 );
    }
  }
  /* create path and filename */
  if( $remoteInput ) {
    $inputFileParts = parse_url( $filename );
    $inputFileParts = array_merge( $inputFileParts, pathinfo( $inputFileParts['path'] ));
    if( !$diskfilename )
      $diskfilename = $inputFileParts['filename'].'.csv';
  }
  else {
    if( FALSE === ( $filename = fileCheckRead( $filename, $log ))) {
      if( $log ) {
        $log->log( "$iCal2csv_VERSION (".number_format(( microtime( TRUE ) - $timeexec['start'] ),  5 ).')' );
        $log->flush();
      }
      return FALSE;
    }
    $inputFileParts = pathinfo( $filename );
    if( !$diskfilename )
      $diskfilename = $inputFileParts['dirname'].DIRECTORY_SEPARATOR.$inputFileParts['filename'].'.csv';
  }
  $outputFileParts = pathinfo( $diskfilename );
  if( $save ) {
    if( FALSE === ( $diskfilename = fileCheckWrite( $outputFileParts['dirname'].DIRECTORY_SEPARATOR.$outputFileParts['basename'], $log ))) {
      if( $log ) {
        $log->log( "$iCal2csv_VERSION (".number_format(( microtime( TRUE ) - $timeexec['start'] ),  5 ).')' );
        $log->flush();
      }
      return FALSE;
    }
  }
  if( $log ) {
    $msg = $iCal2csv_VERSION.' INPUT FILE:"'.$inputFileParts['dirname'].DIRECTORY_SEPARATOR.$inputFileParts['basename'].'"';
    if( $save )
      $msg .= ' OUTPUT FILE: "'.$outputFileParts['dirname'].DIRECTORY_SEPARATOR.$outputFileParts['basename'].'"';
    $log->log( $msg, 7 );
  }
  /* iCalcreator check, read and parse input iCal file */
  $calendar = new vcalendar();
  $calnl = $calendar->getConfig( 'nl' );
  if( $remoteInput ) {
    if( FALSE === $calendar->setConfig( 'url', $filename )) {
      $msg = $iCal2csv_VERSION.' ERROR 3 INPUT FILE:"'.$filename.'" iCalcreator: invalid url';
      if( $log ) { $log->log( $msg, 3 ); $log->flush(); } else error_log( $msg );
      return FALSE;
    }
  }
  else {
    if( FALSE === $calendar->setConfig( 'directory', $inputFileParts['dirname'] )) {
      $msg = $iCal2csv_VERSION.' ERROR 4 INPUT FILE:"'.$filename.'" iCalcreator: invalid directory: "'.$inputFileParts['dirname'].'"';
      if( $log ) { $log->log( $msg, 3 ); $log->flush(); } else error_log( $msg );
      return FALSE;
    }
    if( FALSE === $calendar->setConfig( 'filename',  $inputFileParts['basename'] )) {
      $msg = $iCal2csv_VERSION.' ERROR 5 INPUT FILE:"'.$filename.'" iCalcreator: invalid filename: "'.$inputFileParts['basename'].'"';
      if( $log ) { $log->log( $msg, 3 ); $log->flush(); } else error_log( $msg );
      return FALSE;
    }
  }
  if( FALSE === $calendar->parse()) {
    $msg = $iCal2csv_VERSION.' ERROR 6 INPUT FILE:"'.$filename.'" iCalcreator parse error';
    if( $log ) { $log->log( $msg, 3 ); $log->flush(); } else error_log( $msg );
    return FALSE;
  }
  if( $log ) $timeexec['fileOk'] = microtime( TRUE );
  if( !function_exists( 'iCaldate2timestamp' )) {
    function iCaldate2timestamp( $d ) {
      if( 6 > count( $d ))
        return mktime( 0, 0, 0, $d['month'], $d['day'], $d['year'] );
      else
        return mktime( $d['hour'], $d['min'], $d['sec'], $d['month'], $d['day'], $d['year'] );
    }
  }
  if( !function_exists( 'fixiCalString' )) {
    function fixiCalString( $s ) {
      $s = str_replace( '\,',   ',',     $s );
      $s = str_replace( '\;',   ';',     $s );
      $s = str_replace( '\n ',  chr(10), $s );
      $s = str_replace( '\\\\', '\\',    $s );
      return $s;
    }
  }
  /* create output array */
  $rows = array();
  /* info rows */
  $rows[] = array( 'kigkonsult.se', ICALCREATOR_VERSION, $iCal2csv_VERSION, date( 'Y-m-d H:i:s' ));
  $filename = ( $remoteInput ) ? $filename : $inputFileParts['basename'];
  $rows[] = array( 'iCal input', $filename, 'csv output', $outputFileParts['basename'] );
  if( $prop = $calendar->getProperty( 'CALSCALE' ))
    $rows[] = array( 'CALSCALE', $prop );
  if( $prop = $calendar->getProperty( 'METHOD' ))
    $rows[] = array( 'METHOD', $prop );
  while( $xprop = $calendar->getProperty())
    $rows[] = array( $xprop[0], $xprop[1] );
  if( $log ) $timeexec['infoOk'] = microtime( TRUE );
  /* fix vtimezone property order list */
  $proporder = array();
  foreach( $conf as $key => $value ) {
    if(( '2' <= $key) && ( '99' > $key )) {
      $proporder[$value] = $key;
      if( $log ) $log->log( "$iCal2csv_VERSION $value in column $key", 7 );
    }
  }
  $proporder['TYPE']          =  0;
  $proporder['ORDER']         =  1;
  $props = array( 'TZID', 'LAST-MODIFIED', 'TZURL', 'DTSTART', 'TZOFFSETTO', 'TZOFFSETFROM'
                , 'TZOFFSETTFROM' // iCalcreator 2.6 bug
                , 'COMMENT', 'RRULE', 'RDATE', 'TZNAME' );
  $pix = 2;
  foreach( $props as $prop ) {
    if( isset( $proporder[$prop] )) continue;
    if( isset( $conf['skip'] ) && in_array( $prop, $conf['skip'] )) {
      if( $log ) $log->log( "$iCal2csv_VERSION $prop removed from output", 7 );
      continue;
    }
    while( in_array( $pix, $proporder )) $pix++;
    $proporder[$prop] = $pix++;
  }
  /* remove unused properties from and add x-props to property order list */
  $maxpropix = 11;
  if( $maxpropix != ( count( $proporder ) - 1 ))
    $maxpropix = count( $proporder ) - 1;
  $compsinfo = $calendar->getConfig( 'compsinfo');
  $potmp = array();
  $potmp[0]                   =  'TYPE';
  $potmp[1]                   =  'ORDER';
  foreach( $compsinfo as $cix => $compinfo) {
    if( 'vtimezone' != $compinfo['type'] )
      continue;
    $comp = $calendar->getComponent( $compinfo['ordno'] );
    foreach( $compinfo['props'] as $propName => $propcnt ) {
      if( !in_array( $propName, $potmp ) && isset( $proporder[$propName] ))
        $potmp[$proporder[$propName]] = $propName;
      elseif( 'X-PROP' == $propName ) {
        while( $xprop = $comp->getProperty()) {
          if( !in_array( $xprop[0], $potmp )) {
            $maxpropix += 1;
            $potmp[$maxpropix] = $xprop[0];
          } // end if
        } // end while xprop
      } // end X-PROP
    } // end $compinfo['props']
    if( isset( $compinfo['sub'] )) {
      foreach( $compinfo['sub'] as $compinfo2 ) {
        foreach( $compinfo2['props'] as $propName => $propcnt ) {
          if( !in_array( $propName, $potmp ) && isset( $proporder[$propName] ))
            $potmp[$proporder[$propName]] = $propName;
          elseif( 'X-PROP' == $propName ) {
            $scomp = $comp->getComponent( $compinfo2['ordno'] );
            while( $xprop = $scomp->getProperty()) {
              if( !in_array( $xprop[0], $potmp )) {
                $maxpropix += 1;
                $potmp[$maxpropix] = $xprop[0];
              } // end if
            } // end while xprop
          } // end X-PROP
        } // end $compinfo['sub']['props']
      } // end foreach( $compinfo['sub']
    } // end if( isset( $compinfo['sub']
  } // end foreach compinfo - vtimezone
  ksort( $potmp, SORT_NUMERIC );
  if( '2.6' == substr ( ICALCREATOR_VERSION, -3 )) {
    foreach( $potmp as $k => $v ) { // iCalcreator 2.6 bug fix
      if( 'TZOFFSETTFROM' == $v )
        $potmp[$k] = 'TZOFFSETFROM';
    }
  }
  $proporder = array_flip( array_values( $potmp ));
  if( $log ) $log->log( "$iCal2csv_VERSION zone proporder=".implode(',',array_flip($proporder)), 7 );
  /* create vtimezone info */
  $row = count( $rows ) - 1;
  if( 2 < count( $proporder )) {
    $row += 1;
  /* create vtimezone header row */
    foreach( $proporder as $propName => $col ) {
      if( isset( $conf[$propName] )) {
        $rows[$row][$col] = $conf[$propName]; // check map of userfriendly name to iCal property name
        if( $log ) $log->log( "$iCal2csv_VERSION header row, col=$col: $propName, replaced by ".$conf[$propName], 7 );
      }
      else
        $rows[$row][$col] = $propName;
    }
    $allowedProps = array( 'VTIMEZONE' => array( 'TZID', 'LAST-MODIFIED', 'TZURL' )
                         , 'STANDARD'  => array( 'DTSTART', 'TZOFFSETTO', 'TZOFFSETFROM', 'COMMENT', 'RDATE', 'RRULE', 'TZNAME' )
                         , 'DAYLIGHT'  => array( 'DTSTART', 'TZOFFSETTO', 'TZOFFSETFROM', 'COMMENT', 'RDATE', 'RRULE', 'TZNAME' ));
  /* create vtimezone data rows */
    foreach( $compsinfo as $cix => $compinfo) {
      if( 'vtimezone' != $compinfo['type'] )
        continue;
      $row += 1;
      foreach( $proporder as $propName => $col )
        $rows[$row][] = ''; // set all cells empty
      $rows[$row][$proporder['TYPE']]  = $compinfo['type'];
      $rows[$row][$proporder['ORDER']] = $compinfo['ordno'];
      $comp = $calendar->getComponent( $compinfo['ordno'] );
      foreach( $proporder as $propName => $col ) {
        if(( 'TYPE' == $propName ) || ( 'ORDER' == $propName ))
          continue;
        if( 'X-' == substr( $propName, 0, 2 ))
          continue;
        if( !in_array( $propName, $allowedProps['VTIMEZONE'] )) // check if component allows property
          continue;
        if( isset( $compinfo['props'][$propName] )) {
          if( 'LAST-MODIFIED' == $propName )
            $fcn = 'createLastModified';
          else
            $fcn = 'create'.strtoupper( substr( $propName, 0, 1 )).strtolower( substr( $propName, 1 ));
          if( !method_exists ( $comp, $fcn )) {
            $msg = $iCal2csv_VERSION.' ERROR 7 INPUT FILE:"'.$filename.'" iCalcreator: unknown property: "'.$propName.'" ('.$fcn.')';
            if( $log ) $log->log( $msg, 3 ); else error_log( $msg );
            continue;
          }
          $output = str_replace( "$calnl ",     '',      rtrim( $comp->$fcn()));
          $output = str_replace( $propName.';', '',      $output );
          $output = str_replace( $propName.':', '',      $output );
          $rows[$row][$proporder[$propName]] = fixiCalString( $output );
        }
      } // end foreach( $proporder
      if( isset( $compinfo['props']['X-PROP'] ))  {
        while( $xprop = $comp->getProperty()) {
          $output = str_replace( "$calnl ", '',      rtrim( $xprop[1] ));
          $rows[$row][$proporder[$xprop[0]]] = fixiCalString( $output );
        }
      }
      if( isset( $compinfo['sub'] )) {
        foreach( $compinfo['sub'] as $compinfo2 ) {
          $row += 1;
         foreach( $proporder as $propName => $col )
            $rows[$row][] = ''; // set all cells empty
          $rows[$row][$proporder['TYPE']]  = $compinfo2['type'];
          $rows[$row][$proporder['ORDER']] = $compinfo['ordno'].':'.$compinfo2['ordno'];
          $scomp = $comp->getComponent( $compinfo2['ordno'] );
          foreach( $proporder as $propName => $col ) {
            if(( 'TYPE' == $propName ) || ( 'ORDER' == $propName ))
              continue;
            if( 'X-' == substr( $propName, 0, 2 ))
              continue;
            if( !in_array( $propName, $allowedProps[strtoupper( $compinfo2['type'] )] )) // check if component allows property
              continue;
            if(( '2.6' == substr ( ICALCREATOR_VERSION, -3 )) && ( 'TZOFFSETFROM' == $propName )) $propName = 'TZOFFSETTFROM'; // iCalcreator 2.6 bug fix
            if( isset( $compinfo2['props'][$propName] )) {
              if(( '2.6' == substr ( ICALCREATOR_VERSION, -3 )) && ( 'TZOFFSETTFROM' == $propName )) $propName = 'TZOFFSETFROM'; // iCalcreator 2.6 bug fix
              $fcn = 'create'.strtoupper( substr( $propName, 0, 1 )).strtolower( substr( $propName, 1 ));
              if( !method_exists ( $scomp, $fcn )) {
                $msg = $iCal2csv_VERSION.' ERROR 8 INPUT FILE:"'.$filename.'" iCalcreator: unknown property: "'.$propName.'" ('.$fcn.')';
                if( $log ) $log->log( $msg, 3 ); else error_log( $msg );
                continue;
              }
              $output = str_replace( "$calnl ",     '',      rtrim( $scomp->$fcn()));
              $output = str_replace( $propName.';', '',      $output );
              $output = str_replace( $propName.':', '',      $output );
              $rows[$row][$proporder[$propName]] = fixiCalString( $output );
            }
          } // end foreach( $proporder
          if( isset( $compinfo2['props']['X-PROP'] ))  {
            while( $xprop = $scomp->getProperty()) {
              $output = str_replace( "$calnl ", '',      rtrim( $xprop[1] ));
              $rows[$row][$proporder[$xprop[0]]] = fixiCalString( $output );
            }
          }
        } // end foreach( $compinfo['sub']
      } // end if( isset( $compinfo['sub']['props'] ))
    } // end foreach
  } // end vtimezone
  if( $log ) $timeexec['zoneOk'] = microtime( TRUE );
  $maxColCount = count( $proporder );
  /* fix property order list */
  $proporder = array();
  foreach( $conf as $key => $value ) {
    if(( '2' <= $key) && ( '99' > $key )) {
      $proporder[$value] = $key;
      if( $log ) $log->log( "$iCal2csv_VERSION $value in column $key", 7 );
    }
  }
  $proporder['TYPE']             =  0;
  $proporder['ORDER']            =  1;
  $props = array( 'UID', 'DTSTAMP', 'SUMMARY', 'DTSTART', 'DURATION', 'DTEND', 'DUE', 'RRULE', 'RDATE', 'EXRULE', 'EXDATE'
                , 'DESCRIPTION', 'CATEGORIES', 'ORGANIZER', 'LOCATION', 'RESOURCES', 'CONTACT', 'URL', 'COMMENT', 'PRIORITY'
                , 'ATTENDEE', 'CLASS', 'TRANSP', 'SEQUENCE', 'STATUS', 'COMPLETED', 'CREATED', 'LAST-MODIFIED', 'ACTION'
                , 'TRIGGER', 'REPEAT', 'ATTACH', 'FREEBUSY', 'RELATED-TO', 'REQUEST-STATUS', 'GEO', 'PERCENT-COMPLETE', 'RECURRENCE-ID' );
  $pix = 2;
  foreach( $props as $prop ) {
    if( isset( $proporder[$prop] )) continue;
    if( isset( $conf['skip'] ) && in_array( $prop, $conf['skip'] )) {
      if( $log ) $log->log( "$iCal2csv_VERSION $prop removed from output", 7 );
      continue;
    }
    while( in_array( $pix, $proporder )) $pix++;
    $proporder[$prop] = $pix++;
  }
  /* remove unused properties from and add x-props to property order list */
  if( $maxpropix < (count( $proporder ) - 1))
    $maxpropix = count( $proporder ) - 1;
  $potmp = array();
  $potmp[0]                   =  'TYPE';
  $potmp[1]                   =  'ORDER';
//  $potmp[2]                   =  'UID';
  foreach( $compsinfo as $cix => $compinfo) {
    if( 'vtimezone' == $compinfo['type'] )
      continue;
    foreach( $compinfo['props'] as $propName => $propcnt ) {
      if( !in_array( $propName, $potmp ) && isset( $proporder[$propName] ))
        $potmp[$proporder[$propName]] = $propName;
      elseif( 'X-PROP' == $propName ) {
        $comp = $calendar->getComponent( $compinfo['ordno'] );
        while( $xprop = $comp->getProperty()) {
          if( !in_array( $xprop[0], $potmp )) {
            $maxpropix += 1;
            $potmp[$maxpropix] = $xprop[0];
          } // end if
        } // while( $xprop
      } // end elseif( 'X-PROP'
    } // end foreach( $compinfo['props']
    if( isset( $compinfo['sub'] )) {
      foreach( $compinfo['sub'] as $compinfo2 ) {
        foreach( $compinfo2['props'] as $propName => $propcnt ) {
          if( !in_array( $propName, $potmp ) && isset( $proporder[$propName] ))
            $potmp[$proporder[$propName]] = $propName;
          elseif( 'X-PROP' == $propName ) {
            $scomp = $comp->getComponent( $compinfo2['ordno'] );
            while( $xprop = $scomp->getProperty()) {
              if( !in_array( $xprop[0], $potmp )) {
                $maxpropix += 1;
                $potmp[$maxpropix] = $xprop[0];
              } // end if
            } // end while xprop
          } // end X-PROP
        } // end $compinfo['sub']['props']
      } // end foreach( $compinfo['sub']
    } // end if( isset( $compinfo['sub']
  }
  ksort( $potmp, SORT_NUMERIC );
  $proporder = array_flip( array_values( $potmp ));
  if( $log ) $log->log( "$iCal2csv_VERSION comp proporder=".implode(',',array_flip($proporder)), 7 );
  if( $maxColCount < count( $proporder ))
    $maxColCount = count( $proporder );
  /* create header row */
  $row += 1;
  foreach( $proporder as $propName => $col ) {
    if( isset( $conf[$propName] )) {
      $rows[$row][$col] = $conf[$propName]; // check map of userfriendly name to iCal property name
      if( $log ) $log->log( "$iCal2csv_VERSION header row, col=$col: $propName, replaced by ".$conf[$propName], 7 );
    }
    else
      $rows[$row][$col] = $propName;
  }
  $allowedProps = array( 'VEVENT'    => array( 'ATTACH', 'ATTENDEE', 'CATEGORIES', 'CLASS', 'COMMENT', 'CONTACT', 'CREATED', 'DESCRIPTION', 'DTEND'
                                             , 'DTSTAMP', 'DTSTART', 'DURATION', 'EXDATE', 'RXRULE', 'GEO', 'LAST-MODIFIED', 'LOCATION', 'ORGANIZER'
                                             , 'PRIORITY', 'RDATE', 'RECURRENCE-ID', 'RELATED-TO', 'RESOURCES', 'RRULE', 'REQUEST-STATUS', 'SEQUENCE'
                                             , 'STATUS', 'SUMMARY', 'TRANSP', 'UID', 'URL', )
                       , 'VTODO'     => array( 'ATTACH', 'ATTENDEE', 'CATEGORIES', 'CLASS', 'COMMENT', 'COMPLETED', 'CONTACT', 'CREATED', 'DESCRIPTION'
                                             , 'DTSTAMP', 'DTSTART', 'DUE', 'DURATION', 'EXATE', 'EXRULE', 'GEO', 'LAST-MODIFIED', 'LOCATION', 'ORGANIZER'
                                             , 'PERCENT', 'PRIORITY', 'RDATE', 'RECURRENCE-ID', 'RELATED-TO', 'RESOURCES', 'RRULE', 'REQUEST-STATUS'
                                             , 'SEQUENCE', 'STATUS', 'SUMMARY', 'UID', 'URL' )
                       , 'VJOURNAL'  => array( 'ATTACH', 'ATTENDEE', 'CATEGORIES', 'CLASS', 'COMMENT', 'CONTACT', 'CREATED', 'DESCRIPTION', 'DTSTAMP'
                                             , 'DTSTART', 'EXDATE', 'EXRULE', 'LAST-MODIFIED', 'ORGANIZER', 'RDATE', 'RECURRENCE-ID', 'RELATED-TO'
                                             , 'RRULE', 'REQUEST-STATUS', 'SEQUENCE', 'STATUS', 'SUMMARY', 'UID', 'URL' )
                       , 'VFREEBUSY' => array( 'ATTENDEE', 'COMMENT', 'CONTACT', 'DTEND', 'DTSTAMP', 'DTSTART', 'DURATION', 'FREEBUSY', 'ORGANIZER', 'UID', 'URL' )
                       , 'VALARM'    => array( 'ACTION', 'ATTACH', 'ATTENDEE', 'DESCRIPTION', 'DURATION', 'REPEAT', 'TRANSP', 'TRIGGER' ));
  /* create data rows */
  foreach( $compsinfo as $cix => $compinfo) {
    if( 'vtimezone' == $compinfo['type'] )
      continue;
    $row += 1;
    foreach( $proporder as $propName => $col )
      $rows[$row][] = ''; // set all cells empty
    $rows[$row][$proporder['TYPE']]  = $compinfo['type'];
    $rows[$row][$proporder['ORDER']] = $compinfo['ordno'];
//    $rows[$row][$proporder['UID']]   = $compinfo['uid'];
    $comp = $calendar->getComponent( $compinfo['ordno'] );
    foreach( $proporder as $propName => $col ) {
      if(( 'TYPE' == $propName ) || ( 'ORDER' == $propName ))
        continue;
      if( 'X-' == substr( $propName, 0, 2 ))
        continue;
      if( !in_array( $propName, $allowedProps[strtoupper( $compinfo['type'] )] )) // check if component allows property
        continue;
      if( isset( $compinfo['props'][$propName] )) {
        switch( $propName ) {
          case 'LAST-MODIFIED' ;
            $fcn = 'createLastModified';
            break;
          case 'RECURRENCE-ID':
            $fcn = 'createRecurrenceid';
            break;
          case 'RELATED-TO':
            $fcn = 'createRelatedTo';
            break;
          case 'REQUEST-STATUS':
            $fcn = 'createRequestStatus';
            break;
          case 'PERCENT-COMPLETE':
            $fcn = 'createPercentComplete';
            break;
         default:
          $fcn = 'create'.strtoupper( substr( $propName, 0, 1 )).strtolower( substr( $propName, 1 ));
        }
        if( !method_exists ( $comp, $fcn )) {
          $msg = $iCal2csv_VERSION.' ERROR 9 INPUT FILE:"'.$filename.'" iCalcreator: unknown property: "'.$propName.'" ('.$fcn.')';
          if( $log ) $log->log( $msg, 3 ); else error_log( $msg );
          continue;
        }
        $output = str_replace( "$calnl ",     '',      rtrim( $comp->$fcn()));
        $output = str_replace( $propName.';', '',      $output );
        $output = str_replace( $propName.':', '',      $output );
        $rows[$row][$proporder[$propName]] = fixiCalString( $output );
      }
    } // end foreach( $proporder
    if( isset( $compinfo['props']['X-PROP'] ))  {
      while( $xprop = $comp->getProperty()) {
        $output = str_replace( "$calnl ", '', rtrim( $xprop[1] ));
        $rows[$row][$proporder[$xprop[0]]] = fixiCalString( $output );
      }
    }
    if( isset( $compinfo['sub'] )) {
      foreach( $compinfo['sub'] as $compinfo2 ) {
        $row += 1;
        foreach( $proporder as $propName => $col )
          $rows[$row][] = ''; // set all cells empty
        $rows[$row][$proporder['TYPE']]  = $compinfo2['type'];
        $rows[$row][$proporder['ORDER']] = $compinfo['ordno'].':'.$compinfo2['ordno'];
        $scomp = $comp->getComponent( $compinfo2['ordno'] );
        foreach( $proporder as $propName => $col ) {
          if(( 'TYPE' == $propName ) || ( 'ORDER' == $propName ))
            continue;
          if( 'X-' == substr( $propName, 0, 2 ))
            continue;
          if( !in_array( $propName, $allowedProps[strtoupper( $compinfo2['type'] )] )) // check if component allows property
            continue;
          if( isset( $compinfo2['props'][$propName] )) {
            $fcn = 'create'.strtoupper( substr( $propName, 0, 1 )).strtolower( substr( $propName, 1 ));
            if( !method_exists ( $scomp, $fcn )) {
              $msg = $iCal2csv_VERSION.' ERROR 10 INPUT FILE:"'.$filename.'" iCalcreator: unknown property: "'.$propName.'" ('.$fcn.')';
              if( $log ) $log->log( $msg, 3 ); else error_log( $msg );
              continue;
            }
            $output = str_replace( "$calnl ", '', rtrim( $scomp->$fcn()));
            $output = str_replace( $propName.';', '',   $output );
            $output = str_replace( $propName.':', '',   $output );
            $rows[$row][$proporder[$propName]] = fixiCalString( $output );
          }
        } // end foreach( $proporder
        if( isset( $compinfo2['props']['X-PROP'] ))  {
          while( $xprop = $scomp->getProperty()) {
            $output = str_replace( "$calnl ", '', rtrim( $xprop[1] ));
            $rows[$row][$proporder[$xprop[0]]] = fixiCalString( $output );
          }
        }
      } // if( isset( $compinfo2['props']['X-PROP']
    } // end if( isset( $compinfo['sub']
  } // foreach( $compsinfo as
  if( $log ) $timeexec['compOk'] = microtime( TRUE );
  /* fix csv format */
  // fields that contain commas, double-quotes, or line-breaks must be quoted,
  // a quote within a field must be escaped with an additional quote immediately preceding the literal quote,
  // space before and after delimiter commas may be trimmed (which is prohibited by RFC 4180)
  // a line break within an element must be preserved.
  // Fields may ALWAYS be enclosed within double-quote characters, whether necessary or not.
  foreach( $rows as $row => $line ) {
    for( $col = 0; $col < $maxColCount; $col++ ) {
      if( !isset( $line[$col] ) || empty( $line[$col] )) {
        $rows[$row][$col] = $conf['del'].$conf['del'];
        continue;
      }
      if( ctype_digit( $line[$col] ))
        continue;
      $cell = str_replace( $conf['del'], $conf['del'].$conf['del'], $line[$col] );
      $rows[$row][$col] = $conf['del'].$cell.$conf['del'];
    }
    $rows[$row] = implode( $conf['sep'], $rows[$row] );
  }
  $output = implode( $conf['nl'], $rows ).$conf['nl'];
  if( $log ) {
    $timeexec['exit'] = microtime( TRUE );
    $msg  = "$iCal2csv_VERSION '$filename'";
    $msg .= ' fileOk:' .number_format(( $timeexec['fileOk']  - $timeexec['start'] ),  5 );
    $msg .= ' infoOk:' .number_format(( $timeexec['infoOk']  - $timeexec['fileOk'] ), 5 );
    $msg .= ' zoneOk:' .number_format(( $timeexec['zoneOk']  - $timeexec['infoOk'] ), 5 );
    $msg .= ' compOk:' .number_format(( $timeexec['compOk']  - $timeexec['zoneOk'] ), 5 );
    $msg .= ' csvOk:'  .number_format(( $timeexec['exit']    - $timeexec['compOk'] ), 5 );
    $msg .= ' total:'  .number_format(( $timeexec['exit']    - $timeexec['start'] ),  5 ).'sec';
    $log->log( $msg, 7 );
    $msg  = "$iCal2csv_VERSION '$filename' (".count($compsinfo).' components) start:'.date( 'H:i:s', $timeexec['start'] );
    $msg .= ' total:'  .number_format(( $timeexec['exit']    - $timeexec['start'] ),  5 ).'sec';
    if( $save )
      $msg .= " -> '$diskfilename'";
    $msg .= ', size='.strlen( $output );
    $msg .= ', '.count( $rows )." rows, $maxColCount cols"; 
    $log->log( $msg, 6 );
  }
  /* save or send the file */
  if( $save ) {
    if( FALSE !== file_put_contents( $diskfilename, $output )) {
      if( $log ) $log->flush();
      return TRUE;
    }
    else {
      $msg = $iCal2csv_VERSION.' ERROR 11 INPUT FILE:"'.$filename.'" Invalid write to output file : "'.$diskfilename.'"';
      if( $log ) { $log->log( $msg, 3 ); $log->flush(); } else error_log( $msg );
      return FALSE;
    }
  }
  else {
    if( $log ) $log->flush();
           /** return data, auto gzip */
    $filezise = strlen( $output );
    if( isset( $_SERVER['HTTP_ACCEPT_ENCODING'] )) {
      $output   = gzencode( $output, 9 );
      $filezise = strlen( $output );
      header( 'Content-Encoding: gzip');
      header( 'Vary: *');
    }
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="'.$outputFileParts['basename'].'"' );
    header( 'Cache-Control: max-age=10' );
    header( 'Content-Length: '.$filezise );
    echo $output;
  }
  exit();
}
?>