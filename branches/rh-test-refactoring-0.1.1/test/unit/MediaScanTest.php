<?php
include( dirname(__FILE__) . '/../bootstrap/doctrine.php' );

// Initialize the test object
$t = new lime_test( 8, new lime_output_color() );

$valid_test_song = array(
                          'artist_name' => 'Gorillaz', //string
                          'album_name' => 'Gorillaz Compilation', //string
                          'genre_name' => 'Electronic', //string
                          'song_name' => 'Clint Eastwood', //string
                          'song_length' => '2:05', //min:sec
                          'accurate_length' => '125000', //milliseconds
                          'filesize' => 3000024, //int: bytes
                          'bitrate' => 128, //int: bitrate in estimated kilobits CBR
                          'yearpublished' => 2010, //int: 4 digit  calendar year
                          'tracknumber' => 7, //int: the track number as it appears on disc eg.1
                          'label' => 'EMI', //str: the name of the label the album is on
                          'mtime' => 1293300000, //int:unix time
                          'atime' => 1293300011, //int:unix time
                          'filename' => 'file://localhost/home/notroot/music/test.mp3', //txt: protocol file style
                        );

$utf8_test_song = array(
                          'artist_name' => 'Sigur Rós', //string
                          'album_name' => 'með suð í eyrum við spilum endalaust', //string
                          'genre_name' => 'Русский', //string
                          'song_name' => 'dót widget', //string
                          'song_length' => '3:05', //min:sec
                          'accurate_length' => '185000', //milliseconds
                          'filesize' => 3002332, //int: bytes
                          'bitrate' => 128, //int: bitrate in estimated kilobits CBR
                          'yearpublished' => 2005, //int: 4 digit  calendar year
                          'tracknumber' => 1, //int: the track number as it appears on disc eg.1
                          'label' => 'ンスの映像を世界に先がけて', //str: the name of the label the album is on
                          'mtime' => 1293300023, //int:unix time
                          'atime' => 1293300011, //int:unix time
                          'filename' => 'file://localhost/home/notroot/music/Fließgewässer.mp3', //txt: protocol file style
                        );

$media_scan = new MediaScan();
$t->comment( '->construct()');
$t->like( $media_scan->get_last_scan_id(), '/\d+/', 'Entered a new scan id successfully.' );

$t->comment( '->is_scanned()');
$t->is( $media_scan->is_scanned( 'file://localhost/home/notroot/music/test.mp3', '1293300000' ), false, 'Song should not exist yet' );
$first_insert_id = $media_scan->add_song( $valid_test_song );
$t->like( $first_insert_id, '/\d+/', 'Successfully added a song to the database' );
$t->is( $media_scan->is_scanned( 'file://localhost/home/notroot/music/test.mp3', '1293300000' ), true, 'Song Record Exists Now' );

$t->comment( '->add_song()' );
$media_scan = new MediaScan();
$second_insert_id = $media_scan->add_song( $utf8_test_song );
$t->like( $second_insert_id, '/\d+/', 'Successfully added a UTF-8 Song entry.' );
$t->is( $media_scan->is_scanned( 'file://localhost/home/notroot/music/Fließgewässer.mp3', '1293300023' ), true, 'is_scanned sucessfully found UTF-8 filename' );

$t->comment( '->finalize_scan()' );
$t->is( $media_scan->finalize_scan(), 4, 'Removed Song and Associations' );

$t->comment( '->get_summary()' );
$t->is( is_string( $media_scan->get_summary() ), true, 'returned string' );