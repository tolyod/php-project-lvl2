<?php

namespace Differ;

use function Differ\Parsers\getParsedContent;
use function Differ\Ast\generateDiff;
use function Differ\Render\renderFormatedAst;

function genDiff($path1, $path2, $format = 'pretty')
{
    $filePath1 = realpath($path1);
    $filePath2 = realpath($path2);
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

    $ast = generateDiff($content1, $content2);
    return renderFormatedAst($ast, $format);
}
