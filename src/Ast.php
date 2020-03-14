<?php

namespace Differ\Ast;

use function Funct\Collection\union;
use function Funct\Collection\get;

function actionAdded($firstValue, $secondValue, $name)
{
    return [ 'type' => 'added', 'name' => $name, 'value' => $secondValue ];
}

function actionDeleted($firstValue, $secondValue, $name)
{
    return [ 'type' => 'deleted', 'name' => $name, 'value' => $firstValue ];
}

function actionUnchanged($firstValue, $secondValue, $name)
{
    return [ 'type' => 'unchanged', 'name' => $name, 'value' => $firstValue ];
}

function actionNode($firstValue, $secondValue, $name, $cb)
{
    return ['type' => 'node', 'name' => $name, 'children' => $cb($firstValue, $secondValue)];
}

function actionUpdated($firstValue, $secondValue, $name)
{
    return ['type' => 'updated',
        'name' => $name, 'beforeValue' => $firstValue, 'afterValue' => $secondValue];
}

function getActions()
{
    return [
        [ 'match' => function ($content1, $content2, $key) {
            return !property_exists($content1, $key);
        }, 'action' => '\Differ\Ast\actionAdded'],
        [ 'match' => function ($content1, $content2, $key) {
            return !property_exists($content2, $key);
        }, 'action' => '\Differ\Ast\actionDeleted'
        ], [ 'match' => function ($content1, $content2, $key) {
            return $content1->$key === $content2->$key;
        }, 'action' => '\Differ\Ast\actionUnchanged'
        ], [ 'match' => function ($content1, $content2, $key) {
            return is_object($content1->$key) && is_object($content2->$key);
        }, 'action' => '\Differ\Ast\actionNode'
        ], [ 'match' => function ($content1, $content2, $key) {
            return $content1->$key !== $content2->$key;
        }, 'action' => '\Differ\Ast\actionUpdated'
        ]
    ];
}

function getMatchedAction($first, $second, $key)
{
    $actions = getActions();
    foreach ($actions as $actionItem) {
        $match = $actionItem['match'];
        if ($match($first, $second, $key)) {
            return $actionItem;
        }
    }
    throw new Exception('no valid action matched');
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

function generateDiffAstTree($content1, $content2)
{
    $keys = union(
        array_keys(get_object_vars($content1)),
        array_keys(get_object_vars($content2))
    );
    $astGenFunction = '\Differ\Ast\generateDiffAstTree';
    $result = array_map(function ($key) use ($content1, $content2, $astGenFunction) {
        [ 'action' => $action ] = getMatchedAction($content1, $content2, $key);
        [$value1, $value2] = getValuesPairByKey($content1, $content2, $key);

        return $action($value1, $value2, $key, $astGenFunction);
    },
    $keys);
    return $result;
}
