<?php
namespace Mouf\FixService\Services;

class FixServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testCsFix(){

        $file = file_get_contents(__DIR__."/../Fixtures/FooController.php");

        $fixService = new FixService();
        $fixService->csFix(__DIR__."/../Fixtures/FooController.php");

        $file2 = file_get_contents(__DIR__."/../Fixtures/FooController.php");

        file_put_contents(__DIR__."/../Fixtures/Result.php", $file2);
        file_put_contents(__DIR__."/../Fixtures/FooController.php", $file);

        $this->assertNotEquals($file, $file2);
    }
}
