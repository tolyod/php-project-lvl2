<?php

namespace Differ\Formatters\Plain;

function formatValue($value)
{
    if (is_object($value)) {
        return "complex value";
    }
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    if (is_null($value)) {
        return 'null';
    }
    return $value;
}

function getStringBuilders($parentName)
{
    return [
        "added" => function ($elem) use ($parentName) {
            [ "name" => $name, "value" => $value ] = $elem;
            return "Property '" . $parentName . $name . "' was added with value: '" . formatValue($value) . "'";
        },
        "deleted" => function ($elem) use ($parentName) {
            [ "name" => $name ] = $elem;
            return "Property '" . $parentName . $name . "' was removed";
        },
        "node" => function ($elem, $renderFn) use ($parentName) {
            [ "name" => $name, "children" => $children ] = $elem;
            return $renderFn($children, "{$parentName}{$name}.");
        },
        "updated" => function ($elem) use ($parentName) {
            [ "name" => $name, "beforeValue" => $beforeValue, "afterValue" => $afterValue ] = $elem;
            return "Property '" . $parentName . $name . "' was changed. From '"
                . formatValue($beforeValue) . "' to '" . formatValue($afterValue) . "'";
        }
     ];
}

function renderAst($data, $parentName = "")
{
    $selfCallback = __FUNCTION__;
    $filtered = array_filter($data, fn ($elem) => $elem['type'] !== 'unchanged');
    $stringBuilders = getStringBuilders($parentName);

    $strings = array_map(function ($elem) use ($stringBuilders, $selfCallback) {
        ['type' => $type] = $elem;
        return $stringBuilders[$type]($elem, $selfCallback);
    }, $filtered);
    return join("\n", $strings);
}
