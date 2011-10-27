<?php
/**
 * An Echonest XML Parser using SAX to reduce memory consumption for large libraries
 *
 * @author Richard Hoar
 * @package streeme
 */
class StreemeEchonestCatalogParser
{
  //start depth
  protected $start_depth = 2;
  
  //parse as a tree
  protected $depth = array();
  
  //save state of tag per read
  protected $newStartElement = false;
  
  /**
   * construct the parser class
   * @param file str: the itunes music library.xml file
   * @see: http://developer.apple.com/internet/opensource/php.html
   */
  public function __construct( $file )
  {
    //create the parser
    $this->xml_parser = xml_parser_create( "UTF-8" );
    xml_parser_set_option( $this->xml_parser, XML_OPTION_CASE_FOLDING, 1 );
    xml_set_element_handler( $this->xml_parser, array( 'StreemeEchonestCatalogParser', 'startElement' ), array( 'StreemeEchonestCatalogParser', 'endElement' ) );
    xml_set_character_data_handler( $this->xml_parser, array( 'StreemeEchonestCatalogParser', 'charData' ) );
    if ( !( $this->fp = @fopen( $file, "r" ) ) )
    {
      throw new Exception( sprintf( 'Could not open echonest XML response file %s', $file ) );
    }
  }
  
  /**
   * Iterate over the tracks in the echonest xml file and return an array to flush
   * out to the database
   * @return           array: echonest details for a single song
   */
  public function getDetails()
  {
    while ($data = fread($this->fp, 4096))
    {
      //read and parse another line from the itunes file
      if ( !xml_parse( $this->xml_parser, $data ) )
      {
        throw new Exception( sprintf( "XML error: %s at line %d",
          xml_error_string(xml_get_error_code($this->xml_parser)),
          xml_get_current_line_number($this->xml_parser)));
      }
      
      //is the array ready yet?
      if ( $this->pull )
      {
        $this->pull = 0;
        var_dump($this->song_data);
      }
    }
  }

  /**
   * Free the xml parser
   */
  public function free()
  {
    xml_parser_free($this->xml_parser);
  }
  
  /**
   * This callback indicates that the start of a dict element has
   * occured and manages the outer track loop when the loop is complete
   * we fire indicate that the array is ready to be flushed to the application
   * in getTrack.
   * @param parser  res: the SAX parser handle
   * @param name    str: the element nam
   * @param         arr: attributes for the callback
   */
  private function startElement( $parser, $name, $attribs )
  {
    if($name === "ITEMS")
    {
      $this->pull = true;
    }
    $this->current_element = $name;
    $this->newStartElement = true;
  }
  
  /**
   * Looks for the end of the Track list
   * @param parser  res: the SAX parser handle
   * @param data    str: the string to stream through
   */
  private function charData( $parser, $data )
  {
    if($this->newStartElement)
    {
      $this->elementData = $data;
      $this->newStartElement = false;
    }
    else
    {
      $this->elementData .= $data;
    }
    $this->newEndElement = true;
  }
  
  /**
   * Populates the pull array
   * @param parser  res: the SAX parser handle
   * @param name    str: the element name
   */
  private function endElement( $parser, $name )
  {
    if(strlen($this->current_element) > 0)
    {
      if($this->newEndElement)
      {
        $this->song_data[$this->current_element] = trim($this->elementData);
        $this->newEndElement = false;
      }
    }
  }
}