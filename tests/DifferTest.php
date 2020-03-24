<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;

use function Differ\genDiff;

class DifferTest extends TestCase
{
    /**
    * @dataProvider additionProvider
    */
    public function testGenDiff($type, $format)
    {
        $expectedFileName = $type . "_expected_" . $format . ".out";
        $testFileName1 = "before." . $type;
        $testFileName2 = "after." . $type;
        $expectedFilePath = $this->getFilePath($expectedFileName);
        $testFilePath1 = $this->getFilePath($testFileName1);
        $testFilePath2 = $this->getFilePath($testFileName2);
        $diff = genDiff($testFilePath1, $testFilePath2, $format);
        $expectedResult = file_get_contents($expectedFilePath);
        $this->assertEquals($expectedResult, $diff);
    }

    public function getFilePath($fileName)
    {
        return __DIR__ . "/fixtures/$fileName";
    }

    public function additionProvider()
    {
        return [
            ['json', 'pretty'],
            ['yaml', 'pretty'],
            ['json', 'plain'],
            ['yaml', 'plain'],
            ['json', 'json'],
            ['yaml', 'json']
        ];
    }
}
