<?php

namespace Differ\Renders;

function formatValue($value, $level = 1)
{
    $offset = makeOffset($level);
    if (is_object($value)) {
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
                explode(
                    "\n",
                    json_encode(
                        $value,
                        JSON_PRETTY_PRINT
                    )
                )
            )
        );
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
    $offset = makeOffset($level);
    $opOffset = substr($offset, 0, -2);
     return  [
         "added" => function ($elem) use ($opOffset, $level) {
             [
                 "name" => $name,
                 "value" => $rawValue
             ] = $elem;
             $value = formatValue($rawValue, $level);
             return "{$opOffset}+ $name: $value";
         },
         "deleted" => function ($elem) use ($opOffset, $level) {
             [
                 "name" => $name,
                 "value" => $rawValue
             ] = $elem;
             $value = formatValue($rawValue, $level);
             return "{$opOffset}- $name: $value";
         },
         "unchanged" => function ($elem) use ($offset, $level) {
             [
                 "name" => $name,
                 "value" => $rawValue
             ] = $elem;
             $value = formatValue($rawValue, $level);
             return "{$offset}$name: $value";
         },
         "node" => function ($elem, $renderFn) use ($offset, $level) {
             [
                 "name" => $name,
                 "children" => $children
             ] = $elem;
             $newLevel = $level + 1;
             return "{$offset}$name: {$renderFn($children, $newLevel)}";
         },
         "updated" => function ($elem) use ($opOffset) {
             [
                 "name" => $name,
                 "beforeValue" => $beforeValue,
                 "afterValue" => $afterValue
             ] = $elem;
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
    array_unshift($strings, $firstLine);
    array_push($strings, $lastLine);
    return join("\n", $strings);
}
