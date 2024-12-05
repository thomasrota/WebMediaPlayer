<?php
require 'vendor/autoload.php';

$tagger = new \duncan3dc\MetaAudio\Tagger;
$tagger->addDefaultModules();

$mp3 = $tagger->open("h.mp3");

echo "Artist: {$mp3->getArtist()}\n";
echo "Album: {$mp3->getAlbum()}\n";
echo "Year: {$mp3->getYear()}\n";
echo "Track No: {$mp3->getTrackNumber()}\n";
echo "Title: {$mp3->getTitle()}\n";
?>