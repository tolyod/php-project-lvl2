<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function getParsedContent($data, $type)
{
    switch ($type) {
        case 'json':
            return json_decode($data);
            break;
        case 'yaml':
            return Yaml::parse($data, Yaml::PARSE_OBJECT_FOR_MAP);
            break;
        default:
            throw new Exception('no parser for extention ' . $data);
    }
}
