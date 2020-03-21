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

function formatValue($value, $level = 1)
{
    $offset = makeOffset($level);
    if (is_object($value)) {
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
    return str_repeat('    ', $level);
}

function getStringBuilders($level)
{
    $opOffset = substr(makeOffset($level), 0, -2);
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

function renderAst($data, $level = 1)
{
    $selfCallback = __FUNCTION__;
    $stringBuilders = getStringBuilders($level);
    $curOffset = makeOffset($level - 1);
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

    return join(PHP_EOL, [$firstLine, ...$strings, $lastLine]);
}
