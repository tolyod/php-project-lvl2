<?php

namespace Differ\Formatters\Pretty;

function buildOffsetedJsonFromObject($value, $offset)
{
    $lines = explode("\n", json_encode($value, JSON_PRETTY_PRINT));
    return join(
        "\n",
        array_map(
            function ($inputLine) use ($offset) {
                $line = preg_replace("/\"/", "", $inputLine);
                if (trim($line) === "{") {
                    return $line;
                }
                return $offset . $line;
            },
            $lines
        )
    );
}

function formatValue($value, $level)
{
    if (is_object($value)) {
        $offset = makeOffset($level + 1);
        return buildOffsetedJsonFromObject($value, $offset);
    }
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    } elseif (is_null($value)) {
        return 'null';
    } else {
        return $value;
    }
}

function makeOffset($level)
{
    $padding = '    ';
    return str_repeat($padding, $level);
}

function makeOpOffset($level)
{
    $padding = '    ';
    $opPaddingPrefix = '  ';
    return $opPaddingPrefix . str_repeat($padding, $level);
}

function getStringBuilders($level)
{
    $opOffset = makeOpOffset($level);
    return [
        "added" => function ($elem) use ($opOffset, $level) {
            [ "name" => $name, "value" => $value ] = $elem;
            return "{$opOffset}+ $name: " . formatValue($value, $level);
        },
        "deleted" => function ($elem) use ($opOffset, $level) {
            [ "name" => $name, "value" => $value ] = $elem;
            return "{$opOffset}- $name: " . formatValue($value, $level);
        },
        "unchanged" => function ($elem) use ($opOffset, $level) {
            [ "name" => $name, "value" => $value ] = $elem;
            return "{$opOffset}  $name: " . formatValue($value, $level);
        },
        "node" => function ($elem, $renderFn) use ($opOffset, $level) {
            [ "name" => $name, "children" => $children ] = $elem;
            return "{$opOffset}  $name: {$renderFn($children, $level + 1)}";
        },
        "updated" => function ($elem) use ($opOffset) {
            [ "name" => $name, "beforeValue" => $beforeValue, "afterValue" => $afterValue ] = $elem;
            return "{$opOffset}+ $name: $afterValue\n{$opOffset}- $name: $beforeValue";
        }
     ];
}

function renderAst($data, $level = 0)
{
    $selfCallback = __FUNCTION__;
    $stringBuilders = getStringBuilders($level);
    $curOffset = makeOffset($level);
    $firstLine = "{";
    $lastLine = "$curOffset}";
    $strings =  array_map(
        function ($elem) use ($stringBuilders, $selfCallback, $level) {
            [
             "type" => $type,
             "name" => $name,
            ] = $elem;
            return $stringBuilders[$type]($elem, $selfCallback);
        },
        $data
    );

    return join("\n", [$firstLine, ...$strings, $lastLine]);
}
