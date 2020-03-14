<?php

namespace Differ;

use function Funct\Collection\union;
use function Differ\Parsers\getParsedContent;
use function Differ\Ast\generateDiffAstTree;
use function Differ\Renders\renderAst;

function getAbsolutePath($path)
{
    return $path[0] == '/' ? $path : getcwd() . '/' . $path;
}

function genDiff($path1, $path2)
{
    $filePath1 = getAbsolutePath($path1);
    $filePath2 = getAbsolutePath($path2);
    $extention1 = pathinfo($filePath1, PATHINFO_EXTENSION);
    $extention2 = pathinfo($filePath2, PATHINFO_EXTENSION);
    $content1 = getParsedContent(
        file_get_contents($filePath1),
        $extention1
    );
    $content2 = getParsedContent(
        file_get_contents($filePath2),
        $extention2
    );
    $keys = union(
        array_keys(get_object_vars($content1)),
        array_keys(get_object_vars($content2))
    );
    $ast = generateDiffAstTree($content1, $content2);
    return renderAst($ast) . "\n";
}
