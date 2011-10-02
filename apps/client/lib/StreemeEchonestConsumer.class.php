<?php
/**
 * An echonest API consumer for Streeme
 *
 * @author Richard Hoar
 * @package Streeme
 * @depends sfWebBrowser
 */
class StreemeEchonestConsumer
{
  public $format, $apiKey, $apiVersion, $http, $endPoint;
  public $options = array();

  /**
   * Constructor
   *
   * @param apiKey str: Echonest Api Key
   * @param http   obj: sfWebBrowser instance
   */
  public function __construct($apiKey, sfWebBrowser $http, $apiVersion = 'v4')
  {
    $this->format = 'xml';
    $this->apiKey = $apiKey;
    $this->apiVersion = $apiVersion;
    $this->http = $http;
    $this->endPoint = 'http://developer.echonest.com/api';
  }
  
  /**
   * Call echonest and get the results
   *
   * @param api     str: the api to call (eg. artist, song, track)
   * @param service str: the api method to call (eg. search, profile)
   * @return        str: xml result
   */
  public function call($api, $service)
  {
    //build the uri and send the request
    if(is_array($this->options))
    {
      foreach ($this->options as $option)
      {
        $params .= sprintf('&%s', http_build_query($option));
      }
    }
  
    $uri = sprintf('%s/%s/%s/%s?api_key=%s&format=%s&%s', $this->endPoint, $this->apiVersion, $api, $service, $this->apiKey, $this->format, $params);
    var_dump($uri);
    $this->http->call($uri);
   
    $this->options = array();
    
    return $this;
  }
  
  public function fetchRawResult()
  {
    return $this->http->getResponseText();
  }
  
  public function fetchResult()
  {
    return simplexml_load_string($this->http->getResponseText(),'SimpleXMLElement', LIBXML_NOCDATA);
  }

  /**
   * Set an option for the call
   *
   * @param name str: the key name
   * @param value str: the corresponding value
   */
  public function setOption($name, $value)
  {
    $this->options[] = array($name=>$value);
  }
}