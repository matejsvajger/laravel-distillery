<?php
namespace matejsvajger\Distillery\Tests;

use Distillery;
use Illuminate\Foundation\Auth\User;

class DistilleryTest extends TestCase
{
    /**
     * Check that the filter class path is generated correctly
     * @return void
     */
    public function testCreateFilterDecoratorIsCorrectValue()
    {
        $distillery = resolve('distillery');

        $filter = 'sort';
        $model  = new User();
        $params = compact('model', 'filter');

        $result = $this->invokeMethod(
            $distillery,
            'createFilterDecorator',
            $params
        );

        $expectedResult = '\\App\\Filters\\User\\Sort';

        $this->assertSame($result, $expectedResult);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
