<?php

namespace Differ\Ast;

use tightenco\collect;

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

function getMatchedAction($first, $second, $key, $actions)
{
    $matchedAction = collect($actions)
        ->first(fn($action) => $action['match']($first, $second, $key));
    return $matchedAction['action'];
}

function getValueByKey($content, $key)
{
    return $content->$key ?? null;
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
        $action = getMatchedAction($content1, $content2, $key, $actions);
        $value1 = getValueByKey($content1, $key);
        $value2 = getValueByKey($content2, $key);

        return $action($value1, $value2, $key, $astGenFunction);
    },
    $keys);
    return $result;
}
