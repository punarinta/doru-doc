<?php

chdir(__DIR__);

require_once '../src/DoruDoc.php';

$compiler = new DoruDoc\DoruDoc($_SERVER['argc'] == 2 ? $_SERVER['argv'][1] : (getcwd() . '/config.json'));
$compiler->build();
$stats = $compiler->getStats();


echo "\nBuild complete. Processed:\n";
echo "* {$stats['collectionCount']} controllers\n";
echo "* {$stats['procedureCount']} procedures.\n";
echo "\n";
