<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function getParsedContent($fileContent, $fileExtention)
{
    switch ($fileExtention) {
        case 'json':
            return json_decode($fileContent);
            break;
        case 'yaml':
            return Yaml::parse($fileContent, Yaml::PARSE_OBJECT_FOR_MAP);
            break;
        default:
            throw new Exception('no parser for extention ' . $fileExtention);
    }
}
