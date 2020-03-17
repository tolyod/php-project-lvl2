<?php

namespace Differ\Render;

function selectFormatFunction($format)
{
    $formaters = [
        "pretty" => "Differ\\Formatters\\Pretty\\renderAst",
        "plain" => "Differ\\Formatters\\Plain\\renderAst",
        "json" => "Differ\\Formatters\\Json\\renderAst"
    ];
    return $formaters[$format];
}

function renderFormatedAst($ast, $format)
{
    $formatFunction = selectFormatFunction($format);
    return $formatFunction($ast) . PHP_EOL;
}
