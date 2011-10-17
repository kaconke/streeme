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


$echonest = new StreemeEchonestConsumer($apiKey, new sfWebBrowser);
$catalog = new EchonestCatalog($echonest);

/*

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


*/


