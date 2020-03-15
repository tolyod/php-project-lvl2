<?php

namespace Differ\tests;

use PHPUnit\Framework\TestCase;

use function Differ\genDiff;

class DifferTest extends TestCase
{
    /**
    * @dataProvider additionProvider
    */
    public function testGenDiff($expectedFileName, $testFileName1, $testFileName2, $format)
    {
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
            ['flatJsonExpected.out', 'flatJsonBefore.json', 'flatJsonAfter.json', 'pretty'],
            ['flatYamlExpected.out', 'flatYamlBefore.yaml', 'flatYamlAfter.yaml', 'pretty'],
            ['nestedResult.out', 'nestedBefore.json', 'nestedAfter.json', 'pretty'],
            ['nestedResultPlain.out', 'nestedBefore.json', 'nestedAfter.json', 'plain'],
            ['nestedResultJson.out', 'nestedBefore.json', 'nestedAfter.json', 'json']
        ];
    }
}
