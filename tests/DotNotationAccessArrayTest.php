<?php

namespace Tests\Gorka\DotNotationAccess;

use Gorka\DotNotationAccess\DotNotationAccessArray;

/**
 * Class ConfigTest
 */
class DotNotationAccessArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DotNotationAccessArray
     */
    protected $sut;

    protected function setUp()
    {
        $this->sut = new DotNotationAccessArray($this->getTestConfig());
    }

    /**
     * @return array
     */
    protected function getTestConfig()
    {
        $config = array(
            'foo' => array(
                'bar' => 2,
                'ren' => 'test'
            ),
            'bar' => 3,
            'gru' => array(
                'min' => 3
            ),
            'pol' => array(
                'iop' => array(
                    'a' => 3, 'b' => array('b', 5), 'c' => array()
                ),
                'mul' => 7
            )
        );
        return $config;
    }

    /**
     * Unit Test:
     *
     *  method: GetAll
     *  when:   Called
     *  will:   ReturnExistingConfig
     */
    public function testGetAllWhenCalledWillReturnExistingConfig()
    {
        $this->sut = new DotNotationAccessArray($this->getTestConfig());
        $expected = $this->getTestConfig();
        $actual = $this->sut->getAll();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Unit Test:
     *
     *  method: Get
     *  when:   CalledWithEmptyPath
     *  will:   ThrowInvalidArgumentException
     * @expectedException \InvalidArgumentException
     */
    public function testGetWhenCalledWithEmptyPathWillThrowInvalidArgumentException()
    {
        $this->sut->get('');
    }

    /**
     * Unit Test:
     *
     *  method: Get
     *  when:   CalledWithInvalidPath
     *  will:   ThrowInvalidArgumentException
     * @expectedException \InvalidArgumentException
     */
    public function testGetWhenCalledWithInvalidPathWillThrowInvalidArgumentException()
    {
        $this->sut->get('#fa .l');
    }

    /**
     * Unit Test:
     *
     *  method: Has
     *  when:   CalledWithAnExistingPathOrLeaf
     *  will:   ReturnTrue
     */
    public function testHasWhenCalledWithAnExistingPathOrLeafWillReturnTrue()
    {
        $this->assertTrue($this->sut->has('foo.bar'));
    }

    /**
     * Unit Test:
     *
     *  method: Has
     *  when:   CalledWithUnexistingPathOrLeaf
     *  will:   ReturnFalse
     */
    public function testHasWhenCalledWithUnexistingPathOrLeafWillReturnFalse()
    {
        $this->assertFalse($this->sut->has('foobar'));
    }

    /**
     * Unit Test:
     *
     *  method: Get
     *  when:   CalledWithAnExistingLeaf
     *  will:   ReturnLeaf
     */
    public function testGetWhenCalledWithAnExistingLeafWillReturnLeaf()
    {
        $config = $this->getTestConfig();

        $expected = $config['foo']['bar'];
        $actual = $this->sut->get('foo.bar');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Unit Test:
     *
     *  method: Get
     *  when:   CalledWithAnExistingPathNotLeaf
     *  will:   ReturnValueAsArray
     */
    public function testGetWhenCalledWithAnExistingPathNotLeafWillReturnValueAsArray()
    {
        $config = $this->getTestConfig();

        $expected = $config['foo'];
        $actual = $this->sut->get('foo');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Unit Test:
     *
     *  method: Get
     *  when:   CalledWithAnUnexistingPathOrLeaf
     *  will:   ReturnNull
     */
    public function testGetWhenCalledWithAnUnexistingPathOrLeafWillReturnNull()
    {
        $actual = $this->sut->get('foo.nemo');
        $this->assertNull($actual);
    }

    /**
     * Unit Test:
     *
     *  method: Get
     *  when:   CalledWithAnUnexistingPathOrLeafAndDefaultValue
     *  will:   ReturnDefaultValue
     */
    public function testGetWhenCalledWithAnUnexistingPathOrLeafAndDefaultValueWillReturnDefaultValue()
    {
        $expected = 7;
        $actual = $this->sut->get('foo.nemo', 7);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Unit Test:
     *
     *  method: Get
     *  when:   CalledWithAnExistingPathOrLeafAndDefaultValue
     *  will:   ReturnExistingLeaf
     */
    public function testGetWhenCalledWithAnExistingPathOrLeafAndDefaultValueWillReturnExistingValue()
    {
        $config = $this->getTestConfig();

        $expected = $config['foo']['ren'];
        $actual = $this->sut->get('foo.ren', 7);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Unit Test:
     *
     *  method: Set
     *  when:   CalledWithArrayValue
     *  will:   CreateHierarchyAndSetValue
     */
    public function testSetWhenCalledWithArrayValueWillCreateHierarchyAndSetValue()
    {
        $newSut = $this->sut->set('gru', array('min'=>7, 'b' => array('free' => 21, 'gtr' => 45)));
        $expected = 21;
        $actual = $newSut->get('gru.b.free');
        $this->assertEquals($expected, $actual);
    }

    /**
     * method: set
     * when : called
     * should : return new instance and not modify original config
     **/
    public function testSetCalledReturnNewInstanceAndNotModifyOriginalConfig()
    {
        $preModifiedSut = clone $this->sut;
        $modifiedSut = $this->sut->set('gru', array('min'=>7, 'b' => array('free' => 21, 'gtr' => 45)));
        $this->assertNotSame($preModifiedSut, $this->sut);
        $this->assertEquals($preModifiedSut, $this->sut);

        $expected = 21;
        $actual = $modifiedSut->get('gru.b.free');
        $this->assertEquals($expected, $actual);
        $this->assertNotSame($this->sut, $modifiedSut);
    }

    /**
     * Unit Test:
     *
     *  method: Set
     *  when:   CalledWithUnexistingPath
     *  will:   CreatePathAndSetLeafValue
     */
    public function testSetWhenCalledWithUnexistingPathWillCreatePathAndSetLeafValue()
    {
        $newSut = $this->sut->set('foo.min.test', 7);

        $expected = 7;
        $actual = $newSut->get('foo.min.test');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Unit Test:
     *
     *  method: Set
     *  when:   CalledWithExistingLeaf
     *  will:   SetLeafValue
     */
    public function testSetWhenCalledWithExistingLeafWillSetLeafValue()
    {
        $newSut = $this->sut->set('foo.ren', 7);

        $expected = 7;
        $actual = $newSut->get('foo.ren');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Unit Test:
     *
     *  method: Set
     *  when:   CalledWithExistingPathNoLeaf
     *  will:   RemoveOldBranchAndSetLeafValue
     */
    public function testSetWhenCalledWithExistingPathNoLeafWillRemoveOldBRanchAndSetLeafValue()
    {
        $newSut = $this->sut->set('foo', 7);

        $expected = 7;
        $actual = $newSut->get('foo');
        $this->assertEquals($expected, $actual);
    }

    /**
     * method: remove
     * when : called
     * should : return new instance and not modify original
     **/
    public function testRemoveCalledReturnNewInstanceAndNotModifyOriginal()
    {
        $preModifiedSut = clone $this->sut;
        $modifiedSut = $this->sut->remove('gru');
        $this->assertNotSame($preModifiedSut, $this->sut);
        $this->assertEquals($preModifiedSut, $this->sut);

        $actual = $modifiedSut->get('gru');
        $this->assertNull($actual);
        $this->assertNotSame($this->sut, $modifiedSut);
    }
    
    /**
     * Unit Test:
     *
     *  method: Remove
     *  when:   CalledWithAnExistingLeafOrPath
     *  will:   RemoveLeafOrPath
     */
    public function testRemoveWhenCalledWithAnExistingLeafOrPathWillRemoveLeafOrPath()
    {
        $newSut = $this->sut->remove('foo');

        $actual = $newSut->get('foo', null);
        $this->assertNull($actual);
    }

    /**
     * Unit Test:
     *
     *  method: Serialize
     *  when: Called
     *  will: ReturnSerializedConfig
     */
    public function testSerializeWhenCalledWillReturnSerializedConfig()
    {
        $expected = json_encode($this->getTestConfig());
        $this->sut = new DotNotationAccessArray($this->getTestConfig());
        $actual = $this->sut->serialize();
        $this->assertEquals($expected, $actual);
    }

    /**
     * method: merge
     * when : called
     * should : return new instance and not modify original
     **/
    public function testMergeCalledReturnNewInstanceAndNotModifyOriginal()
    {
        $this->sut = new DotNotationAccessArray(['a' => 9090]);
        $preModifiedSut = clone $this->sut;
        $this->assertNotSame($preModifiedSut, $this->sut);
        $this->assertEquals($preModifiedSut, $this->sut);

        $newConfig = new DotNotationAccessArray(
            [
                'a' => 23,
                'b' => [45, 67]
            ]
        );

        $modifiedSut = $this->sut->merge($newConfig);
        $this->assertNotSame($modifiedSut, $this->sut);
        $this->assertEquals($preModifiedSut, $this->sut);
    }

    /**
     * method: merge
     * when : called
     * should : merge current config with the given one
     **/
    public function testMergeCalledMergeCurrentConfigWithTheGivenOne()
    {
        $this->sut = new DotNotationAccessArray([
            'a' => 9090,
            'b' => [45, 2],
            'c' => 'test',
            'd' => [
                'da' => 3,
                'db' => [
                    'db1' => 'uilo',
                    'db2' => 'poiu'
                ]
            ],
            'e' => 'number7'
        ]);

        $newConfig = new DotNotationAccessArray([
            'a' => 23,
            'b' => [45, 67],
            'c' => null,
            'd' => [
                'da' => 3,
                'db' => 7
            ],
            'f' => [
                'fa' => true,
                'fb' => 2
            ]
        ]);

        $expected = new DotNotationAccessArray([
            'a' => 23,
            'b' => [45, 67],
            'c' => null,
            'd' => [
                'da' => 3,
                'db' => 7
            ],
            'e' => 'number7',
            'f' => [
                'fa' => true,
                'fb' => 2
            ]
        ]);

        $actual = $this->sut->merge($newConfig);
        $this->assertEquals($expected, $actual);
    }
}
