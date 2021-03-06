<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\EncryptionKey\Test\Unit\Model\ResourceModel\Key;

/**
 * Test Class For Magento\EncryptionKey\Model\ResourceModel\Key\Change
 */
class ChangeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $encryptMock;
    /** @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $filesystemMock;
    /** @var \Magento\Config\Model\Config\Structure|\PHPUnit_Framework_MockObject_MockObject */
    protected $structureMock;
    /** @var \Magento\Framework\App\DeploymentConfig\Writer|\PHPUnit_Framework_MockObject_MockObject */
    protected $writerMock;
    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $adapterMock;
    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceMock;
    /** @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject */
    protected $selectMock;
    /** @var \Magento\Framework\Model\ModelResource\Db\TransactionManagerInterface */
    protected $tansactionMock;
    /** @var |\PHPUnit_Framework_MockObject_MockObject */
    protected $objRelationMock;
    /** @var \Magento\EncryptionKey\Model\ResourceModel\Key\Change */
    protected $model;

    public function setUp()
    {
        $this->encryptMock = $this->getMockBuilder('Magento\Framework\Encryption\EncryptorInterface')
            ->disableOriginalConstructor()
            ->setMethods(['setNewKey', 'exportKeys'])
            ->getMockForAbstractClass();
        $this->filesystemMock = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->structureMock = $this->getMockBuilder('Magento\Config\Model\Config\Structure')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->writerMock = $this->getMockBuilder('Magento\Framework\App\DeploymentConfig\Writer')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->adapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->resourceMock = $this->getMockBuilder('Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->selectMock = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $translationClassName = 'Magento\Framework\Model\ModelResource\Db\TransactionManagerInterface';
        $this->tansactionMock = $this->getMockBuilder($translationClassName)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $relationClassName = 'Magento\Framework\Model\ModelResource\Db\ObjectRelationProcessor';
        $this->objRelationMock = $this->getMockBuilder($relationClassName)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $helper->getObject(
            'Magento\EncryptionKey\Model\ResourceModel\Key\Change',
            [
                'filesystem' => $this->filesystemMock,
                'structure' => $this->structureMock,
                'encryptor' => $this->encryptMock,
                'writer' => $this->writerMock,
                'adapterInterface' => $this->adapterMock,
                'resource' => $this->resourceMock,
                'transactionManager' => $this->tansactionMock,
                'relationProcessor' => $this->objRelationMock
            ]
        );
    }

    public function testChangeEncryptionKey()
    {
        $paths = ['path1', 'path2'];
        $table = ['item1', 'item2'];
        $values = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];
        $key = 'key';

        $this->writerMock->expects($this->once())->method('checkIfWritable')->willReturn(true);
        $this->resourceMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->adapterMock);
        $this->adapterMock->expects($this->once())->method('beginTransaction');
        $this->structureMock->expects($this->once())->method('getFieldPathsByAttribute')->willReturn($paths);
        $this->resourceMock->expects($this->atLeastOnce())->method('getTableName')->willReturn($table);
        $this->adapterMock->expects($this->any())->method('select')->willReturn($this->selectMock);
        $this->adapterMock->expects($this->any())->method('fetchPairs')->willReturn($values);
        $this->selectMock->expects($this->any())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())->method('where')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('update')->willReturnSelf();
        $this->writerMock->expects($this->once())->method('saveConfig');
        $this->adapterMock->expects($this->once())->method('getTransactionLevel')->willReturn(1);

        $this->assertEquals($key, $this->model->changeEncryptionKey($key));
    }

    public function testChangeEncryptionKeyThrowsException()
    {
        $key = 'key';
        $this->writerMock->expects($this->once())->method('checkIfWritable')->willReturn(false);

        try {
            $this->model->changeEncryptionKey($key);
        } catch (\Exception $e) {
            return;
        }

        $this->fail('An excpected exception was not signaled.');
    }
}
