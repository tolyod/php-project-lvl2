<?php

namespace Differ;

use function Funct\Collection\union;
use function Differ\Parsers\getParsedContent;

function getAbsolutePath($path)
{
    return $path[0] == '/' ? $path : getcwd() . '/' . $path;
}

function getCorrectValue($value)
{
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    } elseif (is_null($value)) {
        return 'null';
    } else {
        return $value;
    }
}

function getValuesPairByKey($key, $content1, $content2)
{
    $rawValue1 = $content1->$key ?? null;
    $rawValue2 = $content2->$key ?? null;
    $value1 = getCorrectValue($rawValue1);
    $value2 = getCorrectValue($rawValue2);
    return [$value1, $value2];
}

function compareFlatAssocArray($keys, $content1, $content2)
{
    $result = array_reduce(
        array_values($keys),
        function ($acc, $key) use ($content1, $content2) {
            [$value1, $value2] = getValuesPairByKey($key, $content1, $content2);
            $cont1HasKey = property_exists($content1, $key);
            $cont2HasKey = property_exists($content2, $key);
            $keysExists = $cont1HasKey && $cont2HasKey;

            if ($keysExists && $value1 == $value2) {
                $acc[] = "    $key: $value1";
            } elseif ($keysExists && $value1 != $value2) {
                $acc[] = "  + $key: $value2";
                $acc[] = "  - $key: $value1";
            } elseif ($cont1HasKey) {
                $acc[] = "  - $key: $value1";
            } elseif ($cont2HasKey) {
                $acc[] = "  + $key: $value2";
            }
            return $acc;
        },
        ["{"]
    );
    $result[] = "}";
    return join("\n", $result) . "\n";
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
    return compareFlatAssocArray($keys, $content1, $content2);
}
