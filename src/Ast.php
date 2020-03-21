<?php

namespace Differ\Ast;

use function Funct\Collection\union;

function getActions()
{
    return [
        [
            'match' => fn($content1, $content2, $key) => !property_exists($content1, $key),
            'action' => fn($firstValue, $secondValue, $name) =>
                ['type' => 'added', 'name' => $name, 'value' => $secondValue],
        ], [
            'match' => fn($content1, $content2, $key) => !property_exists($content2, $key),
            'action' => fn($firstValue, $secondValue, $name) =>
                ['type' => 'deleted', 'name' => $name, 'value' => $firstValue],
        ], [
            'match' => fn($content1, $content2, $key) => $content1->$key === $content2->$key,
            'action' => fn($firstValue, $secondValue, $name) =>
                ['type' => 'unchanged', 'name' => $name, 'value' => $firstValue],
        ], [
            'match' => fn($content1, $content2, $key) => is_object($content1->$key) && is_object($content2->$key),
            'action' => fn($firstValue, $secondValue, $name, $cb) =>
                ['type' => 'node', 'name' => $name, 'children' => $cb($firstValue, $secondValue)],
        ], [
            'match' => fn($content1, $content2, $key) => $content1->$key !== $content2->$key,
            'action' => fn($firstValue, $secondValue, $name) =>
                ['type' => 'updated', 'name' => $name, 'beforeValue' => $firstValue, 'afterValue' => $secondValue],
        ]
    ];
}

function getMatchedAction($first, $second, $key, $actions, $actionIndex = 0)
{
    if (count($actions) < ($actionIndex + 1) ) {
        throw new Exception('no valid action matched');
    }

    $actionItem = $actions[$actionIndex];
    $match = $actionItem['match'];
    if ($match($first, $second, $key)) {
        return $actionItem;
    }

    return getMatchedAction($first, $second, $key, $actions, $actionIndex + 1);
}

function getCorrectValue($value)
{
    if (is_null($value)) {
        return 'null';
    } else {
        return $value;
    }
}

function getValuesPairByKey($content1, $content2, $key)
{
    $rawValue1 = $content1->$key ?? null;
    $rawValue2 = $content2->$key ?? null;
    $value1 = getCorrectValue($rawValue1);
    $value2 = getCorrectValue($rawValue2);
    return [$value1, $value2];
}

function generateDiff($content1, $content2)
{
    $keys = union(
        array_keys(get_object_vars($content1)),
        array_keys(get_object_vars($content2))
    );
    $astGenFunction = '\Differ\Ast\generateDiff';
    $actions = getActions();
    $result = array_map(function ($key) use ($content1, $content2, $astGenFunction, $actions) {
        [ 'action' => $action ] = getMatchedAction($content1, $content2, $key, $actions);
        [ $value1, $value2 ] = getValuesPairByKey($content1, $content2, $key);

        return $action($value1, $value2, $key, $astGenFunction);
    },
    $keys);
    return $result;
}
