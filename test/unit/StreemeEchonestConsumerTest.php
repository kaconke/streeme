<?php
include( dirname(__FILE__) . '/../bootstrap/unit.php' );
include( dirname(__FILE__) . '/../../apps/client/lib/StreemeEchonestConsumer.class.php' );
include( dirname(__FILE__) . '/../mock/sfWebBrowserMock.class.php' );

// Initialize the test object
$t = new lime_test( 9, new lime_output_color() );

$t->comment( '->construct()' );
$echonest = new StreemeEchonestConsumer('TESTAPIKEY', new SfWebBrowserMock(), 'http://developer.echonest.com/api', 'v4' );

$t->comment( '->setParameter()' );
$echonest->setParameter('id', 'testid');
$t->is_deeply($echonest->getParameters(), array(0 => array ('id' => 'testid')), 'Inserted parameter id');
$echonest->setParameter('bucket', 'song_profile');
$echonest->setParameter('bucket', 'artist_hotttnesss');
$t->is_deeply($echonest->getParameters(), array(
    0 => array ('id' => 'testid'),
    1 => array ('bucket' => 'song_profile'),
    2 => array ('bucket' => 'artist_hotttnesss')
    ), 'Inserterd multiple bucket types in the same parameter list');

$t->comment( '->setHeader()' );
$echonest->setHeader('User-Agent', 'Streeme/PHP5');
$t->is_deeply($echonest->getHeaders(), array ('User-Agent' => 'Streeme/PHP5'), 'set user agent header');

$t->comment( '->query()' );
$result = $echonest->query('song', 'search');

$t->comment( '->getUri()' );
$t->is($echonest->getUri(), 'http://developer.echonest.com/api/v4/song/search?api_key=TESTAPIKEY&format=xml&id=testid&bucket=song_profile&bucket=artist_hotttnesss', 'Correct URI/URL pattern');

$t->comment( '->query()->fetchResult()' );
$t->ok(is_object($result->fetchResult()), 'fetchResult produces an object version of the response' );
$t->is((string) $result->fetchResult()->status->message,'Success','Acessing xml dom');

$t->comment( '->query()->fetchRawResult()' );
$t->is($result->fetchRawResult(), file_get_contents( dirname(__FILE__) . '/../fixtures/sfWebBrowserMock/f1aa6b021b6b76d438552626219f5ecd.txt'), 'Got the correct response');

$t->comment( '->query()->fetchAssocResult()' );
$test = $result->fetchAssocResult();
$t->ok(is_array($test), 'Item is an associative array');
$t->is($test['status']['message'], 'Success', 'Correct Assoc structure');

$t->comment( '->getResponseTime()' );
$t->ok(is_float($echonest->getResponseTime()), 'Valid response time');