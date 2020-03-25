<?php

namespace Differ\Formatters\Json;

function renderAst($data)
{
    return json_encode($data, JSON_PRETTY_PRINT) . "\n";
}
