<?php
/**
 * fileCheck
 * ver 1.0
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
 * function fileCheckRead
 *
 * Check if a filename is a file and readable
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.0 - 2009-03-25
 * @param string $filename      file to check readbility
 * @param object $log           if FALSE write errors to error_log, else to PEAR LOG / eClog object
 *                              NOTE, no '$log->flush()' is done!!
 * @return filenamn             (expanded) or FALSE if error
 */
function fileCheckRead( $filename, & $log ) {
  $fileCheckRead_VERSION = 'fileCheckRead 1.0';
  $msg = FALSE;
  $fileParts = pathinfo( $filename );
  if( empty( $fileParts['dirname'] ))
    $fileParts['dirname'] = '.';
  else
    $fileParts['dirname'] = realpath( $fileParts['dirname'] );
  $dirfile = $fileParts['dirname'].DIRECTORY_SEPARATOR.$fileParts['basename'];
  if( $log ) $log->log( "$fileCheckRead_VERSION START filename=$filename, pathinfo=".var_export( $fileParts, TRUE ), 7 );
  if( !file_exists( $fileParts['dirname'] ))          $msg = "No directory exists (".$fileParts['dirname'].')';
  if( !$msg && !is_dir( $fileParts['dirname'] ))      $msg = "Invalid directory: (".$fileParts['dirname'].')';
  if( !$msg && !is_readable( $fileParts['dirname'] )) $msg = "Directory not readable ($dirfile)";
  clearstatcache();
  if( !$msg && !file_exists( $dirfile ))              $msg = "No file exists ($dirfile)";
  if( !$msg && !is_file( $dirfile ))                  $msg = "File no file ($dirfile)";
  if( !$msg && !is_readable( $dirfile ))              $msg = "File not readable ($dirfile)";
  if( !$msg && ( 0 >= filesize( $dirfile )))          $msg = "File empty ($dirfile)";
  clearstatcache();
  if ( !$msg ) {
    if( $log ) $log->log( "$fileCheckRead_VERSION ok ($dirfile)", 6 );
    return $dirfile;
  }
  else {
    if( $log ) $log->log( "$fileCheckRead_VERSION, $msg", 3 ); else error_log( $msg );
    return FALSE;
  }
}
/**
 * function fileCheckWrite
 *
 * Check if a filename is a writeable file
 * file is created if missing
 * if file exists,it may be backuped with ext .YmdHis.old
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 1.0 - 2009-03-25
 * @param string $filename      file to check
 * @param object $log           if FALSE write errors to error_log, else to PEAR LOG / eClog object
 *                              NOTE, no '$log->flush()' is done!!
 * @param bool   $backup        if TRUE (default), backups any existing file
 *                              format: <filename>.'.'.date( 'YmdHis', filemtime( $dirfile )).'.old'
 * @return filenamn             (expanded) or FALSE if error
 */
function fileCheckWrite( $filename, & $log, $backup=TRUE ) {
  $fileCheckWrite_VERSION = 'fileCheckWrite 1.0';
  $msg = FALSE;
  $fileParts = pathinfo( $filename );
  if( empty( $fileParts['dirname'] ))
    $fileParts['dirname'] = '.';
  else
    $fileParts['dirname'] = realpath( $fileParts['dirname'] );
  $dirfile = $fileParts['dirname'].DIRECTORY_SEPARATOR.$fileParts['basename'];
  if( $log ) $log->log( "$fileCheckWrite_VERSION START filename=$filename, pathinfo=".var_export( $fileParts, TRUE ), 7 );
  if( !is_dir( $fileParts['dirname'] ) &&
     ( FALSE === mkdir( $fileParts['dirname'] )))     $msg = "Can't create directory ($dirfile)";
  if( !$msg && !is_writable( $fileParts['dirname'] )) $msg = "Directory not writeable ($dirfile)";
  if( !$msg && !file_exists( $dirfile ) &&
     ( FALSE === touch( $dirfile )))                  $msg = "Can't create file ($dirfile)";
  if( !$msg && !is_file( $dirfile ))                  $msg = "File no file ($dirfile)";
  clearstatcache();
  if( !$msg && !is_writable( $dirfile ))              $msg = "File not writeable ($dirfile)";
  if( !$msg ) {
    if( 0 < filesize( $dirfile ) && $backup ) { // file exists, make unique backup
      $dirfileOld = $dirfile.'.'.date( 'YmdHis', filemtime( $dirfile )).'.old';
      if( copy( $dirfile, $dirfileOld )) {
        if ( $log ) $log->log( "$fileCheckWrite_VERSION, Existing file ($dirfile) saved as $dirfileOld", 5 );
      }
      else { // ??
        $msg = "unable to backup file ($dirfile) as $dirfileOld";
        if ( $log ) $log->log( "$fileCheckWrite_VERSION, $msg", 3 ); else error_log( $msg );
      }
    }
    clearstatcache();
    if( $log ) $log->log( "$fileCheckWrite_VERSION ok ($dirfile)", 6 );
    return $dirfile;
  }
  else {
    clearstatcache();
    if( $log ) $log->log( "$fileCheckWrite_VERSION, $msg", 3 ); else error_log( $msg );
    return FALSE;
  }
}
?>