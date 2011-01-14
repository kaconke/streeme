<?php
/**
* The Media Proxy / Gateway for streeme. This class conatins a standard system for delivering music files to the user 
* from nearly any valid media file.  This service requires the PEAR HTTP_Download and HTTP libraries.
* @package    streeme
* @subpackage play
* @author     Richard Hoar
*/
error_reporting( 0 ); //HTTP download is extremely noisy  
require_once( 'HTTP/Download.php' );
require_once( dirname( __FILE__ ) . '/StreemeUtil.class.php' );

class MediaProxy
{
  //private class variables
  private
    $source_bitrate,
    $source_duration,
    $source_format,
    $source_extension,
    $source_type,
    $source_basename,
    $source_filename,
    $source_file_length,
    $source_file_mtime,
    $target_extension,
    $target_type,
    $filename,
    $types,
    $ffmpeg_executable,
    $ffmpeg_args,
    $allow_transcoding,
    $use_chunked_encoding,
    $user_requested_format,
    $user_requested_bitrate;
  
  //user options
  protected $target_bitrate = false;
  protected $target_format = false;
  protected $is_icy_response = false; 
    
  /**
   * Constructor - Hydrates class variables based on a song_id
   * @param unique_song_id str: song id (unique id in the song table)
   * @return               bool: false if no song is found
   */
  public function __construct( $unique_song_id )
  {
    //get the song details by unique id
    $result = Doctrine_Core::getTable('Song')->getSongByUniqueId( $unique_song_id );
    if( !$result ) return false;
    
    //read configuration file /apps/client/config/app.yml
    $this->ffmpeg_executable     = sfConfig::get( 'app_ffmpeg_executable' );
    $this->allow_transcoding     = sfConfig::get( 'app_allow_ffmpeg_transcoding' );
    
    //get the filename and test if it exists  
    $this->filename = StreemeUtil::itunes_format_decode( $result->filename ); 
 
    if( !isset( $this->filename ) || empty( $this->filename ) )
    {
      return false;
    }
    if( !is_readable( $this->filename ) )
    {
      $this->filename = null;
      return false;
    }
    
    //get the source file information
    $fstat = stat( $this->filename );
    $file_info = pathinfo( $this->filename );
    
    
    //FFMPEG type list
    $this->types = array(
                      'mp3'    => 'audio/mpeg',
                      'ogg'    => 'audio/ogg',
                  ); 
    
    //create three letter target_format extensions
    foreach( $this->types as $k => $v )
    {
      $this->target_formats[] = $k; 
    }

    //extract source file details
    $this->source_bitrate      = (int) $result->bitrate;
    $this->source_duration     = (int) $result->accurate_length;
    $this->source_format       = $file_info[ 'extension' ];
    $this->source_extension    = '.' . $file_info[ 'extension' ];
    $this->source_type         = $this->types[ $file_info[ 'extension' ] ];
    $this->source_basename     = $file_info[ 'basename' ];
    $this->source_filename     = $file_info[ 'filename' ];
    $this->source_file_length  = $fstat[ 'size' ];
    $this->source_file_mtime   = $fstat[ 'mtime' ];
    
    $result->free();
    unset( $result );
  }
  
  /**
   * Set the target bitrate for the stream
   * @param bitrate int: a bitrate in max kbps - will be scaled for VBR formats
   */
  public function setTargetBitrate( $bitrate )
  {
    if( $this->allow_transcoding )
    {
      $this->target_bitrate         = ( $bitrate ) ? (int) $bitrate : false;
      $this->user_requested_bitrate = ( $this->target_bitrate ) ? true : false;
    }
  }
  
  /**
   * Set the target format for the stream
   * @param format str: a target format by 3 letter file extension
   */
  public function setTargetFormat( $format )
  {
    if( $this->allow_transcoding )
    {
      $this->target_format         = ( $format && in_array( $format, $this->target_formats ) ) ? $format : false;
      $this->user_requested_format = ( $this->target_format )  ? true : false;
      $this->target_extension      = '.' . $this->target_format; 
      $this->target_type           = $this->types[ $this->target_format ];
    }
  }
  
  /**
   * Set the stream to use non HTTP standard headers for ICECAST compatible stream recievers
   * @param is_icy_response boolean
   */
  public function setIsIcyResponse( $is_icy_response )
  {
    $this->is_icy_response       = ( $is_icy_response  )  ? true : false;
  }
  
  /**
   * play - this method will play the selection using class variables made in the constructor 
   * this is the main public method for the class
   */
  public function play()
  {    
    //determine right send method 
    if(
        ( $this->user_requested_bitrate || $this->user_requested_format )
        && ( ( $this->target_bitrate <= $this->source_bitrate ) || ( $this->source_extension == $this->target_extension ) ) 
        && !$this->is_icy_response
      )
    {
       $this->stream_modify();
    }
    else if( 
        ( $this->user_requested_bitrate || $this->user_requested_format )
        && $this->is_icy_response
        && $this->target_format == 'mp3'
      )
    {
       $this->stream_icy();
    }
    else
    {
       $this->stream_original();
    }
  }
  
  /**
   * Instead of streaming the original file, we'll feed it to FFMPEG for modification and copy its
   * output to the output bufer in binary mode. You may want a fastish computer for this.
   * method will only work if you have the ffmpg executable installed an enabled with the correct codec support
   */
  private function stream_modify()
  {
    header("HTTP/1.1 200 OK");
    header("Content-Type: " . ( ( $this->user_requested_format ) ? $this->target_type : $this->source_type ) );
    header("Content-Disposition: inline; filename=" . iconv( 'UTF-8', 'ASCII//TRANSLIT', $this->source_filename . $this->target_extension ) );
    header("Content-Encoding: none");
    $this->ffmpeg_passthru();
  }
  
  /**
   * Stream this file using nonstandard HTTP headers for shoutcast servers. 
   * it will output ICY 200 OK for shoutcast clients with no/incomplete HTTP/1.1 support.
   * method will only work if you have the ffmpeg executable installed an enabled with the correct codec support
   */
  private function stream_icy()
  {
    //special ICY - ice/shoutcast headers
    header( "ICY 200 OK" );
    header( "icy-name: Streeme Client Server" );
    header( "icy-genre: Unknown Genre" );
    header( "icy-pub: 1" );
    header( "icy-br: " . $this->target_bitrate );
    header( "icy-metaint: 8192" );
    header( "Content-Type: " . ( ( $this->user_requested_format ) ? $this->target_type : $this->source_type ) );
    header("Content-Encoding: none");  
    
    $this->ffmpeg_passthru();
  }
  
  /**
   * Stream the original file from anywhere on the user's PC. This function will serve the original file 
   * and offer ranges for seeking through the content.
   */
  private function stream_original()
  { 
    //does the user have apache mod XSendFile installed? use that as a first priority
    //otherwise we can send it using php's PEAR HTTP_Download functionality 
    $mods = apache_get_modules();
    $flip = array_flip( $mods );
    $mod_number = (string) $flip[ 'mod_xsendfile' ];
    if( !empty( $mod_number ) )
    { 
      header("X-Sendfile: $this->filename");
      header("Content-Type: $this->source_type");
      header("Content-Disposition: attachment; filename=\"$this->source_basename\"");
      header("Content-Length: $this->source_file_length" );
      exit;
    }
    else
    {    
      $params = array(
        'File'                => $this->filename,
        'ContentType'         => $this->source_type,
        'BufferSize'	        => 32000,
        'ContentDisposition'  => array( HTTP_DOWNLOAD_INLINE, $this->source_basename ),
      );
      
      $error = HTTP_Download::staticSend( $params, false );
    }
  }
  
  /**
   * get the arguments for FFMPEG when resampling - set content lengths by algorithm (guesses)
   * @return   str: commandline arguments for ffmpeg
   */
  private function get_ffmpeg_args()
  {
    $args  = '-y '; //play without prompts / overwrite
    $args .= sprintf( '-i "%s" ', $this->filename ); //source filename
    
    $this->argformat = ( $this->user_requested_format ) ? $this->target_format : $this->source_format;
    $this->argbitrate = (int) ( $this->user_requested_bitrate ) ? $this->target_bitrate : $this->source_bitrate;
      
    switch ( $this->argformat )
    {
      case 'mp3':
        $args .= sprintf( '-ab %dk ', intval( $this->argbitrate ) ); //bitrate
        $args .= sprintf( '-acodec %s ', 'libmp3lame' ); //codec        
        $args .= sprintf( '-f %s ', 'mp3' ); //container                                     
        break;     
      case 'ogg':
        $args .= sprintf( '-aq %d ', floor( intval( $this->argbitrate ) / 2.5 ) ); //vbr quality
        $args .= sprintf( '-acodec %s ', 'vorbis' ); 
        $args .= sprintf( '-f %s ', 'ogg' );                                      
        break;
    }
    
    $args .= ' - ';                              
            
    return trim( $args );                                                                 
  }
  
  /**
   * Use FFMPEG in a process to send re-compressed files on the fly
   * @return    bool: false if user has not allowed ffmpeg transcoding 
   */
  private function ffmpeg_passthru()
  {
    if( $this->allow_transcoding )
    {
      $this->ffmpeg_args = $this->get_ffmpeg_args();
      switch ( $this->argformat )
      {
        case 'mp3':
          $this->output_mp3();
          break;  
          
        case 'ogg':
         	$this->output_ogg();
          break;    
      }
      exit;
    }
    return false;
  }
    
  /**
   * Send an MP3 to the output buffer with an inaccurate content-length guess
   * calculate the new filesize ( this algortihm is a huge hack ) 
   */
  private function output_mp3()
  {
    $new_filesize = (( $this->source_duration / 1000 ) //time in seconds 
                  * ( $this->target_bitrate * 1000 ) //bitrate 
                  / 8 ) // convert to bytes
                  - 1024; //trim 1024 bytes for headers
  	header( 'Content-Length:' . $new_filesize );
  	passthru( $this->ffmpeg_executable . ' ' . $this->ffmpeg_args );
	}
  
  /**
   * Send an OGG/Vorbis audio file to the output buffer with a very large filesize
   */
  private function output_ogg()
  {
  	header( 'Content-Length: 999999999' );
  	passthru( $this->ffmpeg_executable . ' ' . $this->ffmpeg_args );
  }
}