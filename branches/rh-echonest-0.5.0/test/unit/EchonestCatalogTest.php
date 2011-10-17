<?php
include( dirname(__FILE__) . '/../bootstrap/unit.php' );
include( dirname(__FILE__) . '/../../apps/client/lib/EchonestCatalog.class.php' );
include( dirname(__FILE__) . '/../mock/sfWebBrowserMock.class.php' );

// Initialize the test object
$t = new lime_test( 9, new lime_output_color() );

$t->comment( '->construct()' );
$echonest = new StreemeEchonestConsumer('TESTAPIKEY', new SfWebBrowserMock(), 'http://developer.echonest.com/api', 'v4' );
$catalog = new EchonestCatalog($echonest);

$t->comment( '->()' );
$catalog->create()