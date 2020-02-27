<?php
namespace Differ;
use function Funct\Collection\union;

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

function genDiff($path1, $path2)
{
    $filePath1 = getAbsolutePath($path1);
    $filePath2 = getAbsolutePath($path2);
    $content1= json_decode(file_get_contents($filePath1), true);
    $content2 = json_decode(file_get_contents($filePath2), true);
    $keys = union(array_keys($content1), array_keys($content2));
    $result = array_reduce(
        array_values($keys),
        function ($acc, $key) use ($content1, $content2) {
            $rawValue1 = $content1[$key] ?? null;
            $rawValue2 = $content2[$key] ?? null;
            $value1 = getCorrectValue($rawValue1);
            $value2 = getCorrectValue($rawValue2);
            $cont1HasKey = array_key_exists($key, $content1);
            $cont2HasKey = array_key_exists($key, $content2);
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

