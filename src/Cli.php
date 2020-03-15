<?php

namespace Differ\Cli;

use function Differ\genDiff;

const SPEC = <<<DOC
Generate diff
Usage:
  gendiff (-h|--help)
  gendiff (-v|--version)
  gendiff [--format <fmt>] <firstFile> <secondFile>
Options:
  -h --help                     Show this screen
  -v --version                  Show version
  --format <fmt>                Report format [default: pretty]
DOC;

function run()
{
    $handler = new \Docopt\Handler();
    $args = $handler->handle(SPEC)->args;
    $pathToFile1 = $args['<firstFile>'];
    $pathToFile2 = $args['<secondFile>'];
    $format = $args['--format'];
    $diff = genDiff($pathToFile1, $pathToFile2, $format);
    echo $diff . "\n";
}
