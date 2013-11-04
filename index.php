<?php

require_once __DIR__.'/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->register();
$loader->registerNamespace('Redjik', __DIR__.'/vendor');

$fs = new \Redjik\Filesystem\Filesystem();
try{
    var_dump($handle = $fs->fopen('/tmp/test','r'));
}catch (\Exception $e){
    echo $e->getMessage().' '.get_class($e);
}
