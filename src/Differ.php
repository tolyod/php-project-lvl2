<?php

namespace Differ;

use function Differ\Parsers\getParsedContent;
use function Differ\Ast\generateDiffAstTree;
use function Differ\Render\renderFormatedAst;

function getAbsolutePath($path)
{
    return $path[0] == '/' ? $path : getcwd() . '/' . $path;
}

function genDiff($path1, $path2, $format = 'pretty')
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

    $ast = generateDiffAstTree($content1, $content2);
    return renderFormatedAst($ast, $format) . "\n";
}
