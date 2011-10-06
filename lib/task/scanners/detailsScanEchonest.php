<?php
/**
 * detailsScanEchonest
 *
 * This scanner scans individual song tracks for details from
 * Echonest, a service for retrieving text metdata about music from
 *
 * @package streeme
 * @author Richard Hoar
 */
$apiKey = sfConfig::get('app_echonest_api_key', false);
if(!$apiKey)
{
  throw new Exception('You must declare an API Key for echonest in your app.yml file to continue.');
}
/*
$echonest = new StreemeEchonestConsumer($apiKey, new sfWebBrowser, 'v4');
$echonest->setOption('artist','Sigur Ros');
$echonest->setOption('title','Takk');
$echonest->setOption('bucket','audio_summary');
$echonest->setOption('bucket','song_hotttnesss');
$echonest->setOption('bucket','artist_hotttnesss');
var_dump($echonest->call('song', 'search')->fetchResult());
*/

$echonest = new StreemeEchonestConsumer($apiKey, new sfWebBrowser);
/*
$echonest->setParameter('name', 'testCatalog1');
$echonest->setParameter('type','song');
$echonest->setHeader('Content-Type', 'multipart/form-data');
var_dump($echonest->query('catalog', 'create', 'POST')->fetchResult());

$songTable = SongTable::getInstance();
$entries = array();
foreach( $songTable->getEchonestList() as $value)
{
  $entries[] = $echonest->getCatalogWrapper('update',array(
    'artist_name' => $value['artist_name'],
    'release'     => $value['album_name'],
    'song_name'   => $value['name'],
    'track_number'=> (int) $value['tracknumber'],
  ));
}
file_put_contents(sfConfig::get('sf_cache_dir').'/update.json', sprintf('[%s]', join(',', $entries)));

$echonest->setParameter('id', 'CARTNYN132D32ABFD9');
$echonest->setParameter('data-type', 'json');
$echonest->setParameter('data', sfConfig::get('sf_cache_dir').'/update.json');

var_dump($echonest->query('catalog', 'update', 'POST')->fetchRawResult());
*/

$echonest->setParameter('id', 'CARTNYN132D32ABFD9');
$echonest->setParameter('bucket', 'audio_summary');
var_dump($echonest->query('catalog', 'read')->fetchResult());
