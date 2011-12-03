<?php
include( dirname(__FILE__) . '/../bootstrap/doctrine.php' );

// Initialize the test object
$t = new lime_test( 27, new lime_output_color() );

$song_array = array(
  /*song1*/ array('artist_name'=>'Radiohead', 'album_name' => 'The King of Limbs', 'song_name'=>'feral', 'genre_name'=>'Pop'),
  /*song2*/ array('artist_name'=>'Sigur Rós', 'album_name' => 'Með suð í eyrum við spilum endalaust', 'song_name'=>'Góðan daginn', 'genre_name'=>'Shoegaze'),
  /*song3*/ array('artist_name'=>'Jonas Munk', 'album_name' => 'ASIP - isolatedmix 21 - Dreamy Sounds from Odense', 'song_name'=>'ASIP - isolatedmix 21 - Dreamy Sounds from Odense', 'genre_name'=>'Ambient'),
  /*song4*/ array('artist_name'=>'Ulrich Schnauss and Jonas Munk', 'album_name' => 'Travelling', 'song_name'=>'01_disc_rip_ulrich_and_jona...TR [01]', 'genre_name'=>'Deep House'),
  /*song5*/ array('artist_name'=>'Umicia Schnapps and Jose Carerra', 'album_name' => 'Melodia Francisca', 'song_name'=>'Oye!', 'genre_name'=>'Soundalike'),
);
$keys = array(); $i=1;
foreach($song_array as $song)
{
  $keys['song'.$i] = sha1(serialize($song));
  $i++;
}

$t->comment('->construct()');
$lucene = new StreemeLucene();

$t->comment('->getLuceneIndexInstance()');
$t->is(get_class($lucene->getLuceneIndexInstance()), 'Zend_Search_Lucene_Proxy', 'Instantiation succeeded - proxy instance created');

$t->comment('->getLuceneIndexFile()');
$t->ok(strlen($lucene->getLuceneIndexFile()) > 5, 'Index file returns a pathname');

$t->comment('->updateIndex()');
$i=1;
foreach($song_array as $song)
{
  $t->ok($lucene->updateIndex($song), 'Successfully added Song ' . $i);
  $i++;
}
$t->is($lucene->updateIndex(array()), false, 'Catch empty update array');

$t->comment('->getSongIds()');
$t->is($lucene->getSongIds('Radiohead'), array($keys['song1']), 'Simple Ansi keyword search succeded');
$t->is($lucene->getSongIds('Með suð í við'), array($keys['song2']), 'UTF-8 keyword search succeeded');
$t->is($lucene->getSongIds('Jonas Munk'), array($keys['song3'], $keys['song4']), 'ASCII multiple result search succeeded');
$t->is($lucene->getSongIds('artist_name:Travelling'), array(), 'If a field is present, it should not bleed over');
$t->is($lucene->getSongIds('album_name:Travelling'), array($keys['song4']), 'Search within a field');
$t->is($lucene->getSongIds('Radiohead AND King'), array($keys['song1']), 'AND Logic targets a record');
$t->is($lucene->getSongIds('Radiohead AND King AND suð'), array(), 'AND Logic too strict');
$t->is($lucene->getSongIds('Radiohead OR suð'), array($keys['song1'], $keys['song2']), 'OR Logic targets two records');
$t->is($lucene->getSongIds('Radiohead suð'), array($keys['song1'], $keys['song2']), 'compound of two records implicitly OR targets two records');
$t->is($lucene->getSongIds('Jonas Munk -Ulrich'), array($keys['song3']), 'Record rejection returns a single record');
$t->is($lucene->getSongIds('Jonas Munk -"Ulrich Schnauss"'), array($keys['song3']), 'Record rejection returns a single record');
$t->is($lucene->getSongIds('Schnauss~'), array($keys['song4'], $keys['song5']), 'Fuzzy match by score 0.5');
$t->is($lucene->getSongIds('Schnauss~0.9'), array($keys['song4']), 'Fuzzy match by score 0.9');
$t->is($lucene->getSongIds(''), array(), 'Empty string returns no results');
$t->is($lucene->getSongIds('*'), array(), 'invalid wildcard returns no results');
$t->is(count($lucene->getSongIds('delkey:qjz')), count($keys), 'Delkey will target all records in case all records must be deleted');
$t->is($lucene->getSongIds('q'), array(), 'q char returns no results');

$t->comment('->optimize()');
$t->ok($lucene->optimize(), 'optimization succeeded');
$t->is($lucene->getSongIds('Radiohead'), array($keys['song1']), 'Simple Ansi keyword search succeded after optimizing table');