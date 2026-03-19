<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/modules/php')
    ->in(__DIR__ . '/tests')
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
    ])
    ->setFinder($finder);
