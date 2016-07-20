<?php

namespace DR\PaymentMethodFilter\Test\Unit\Model\Filter;

use DR\PaymentMethodFilter\Model\Filter\Customer as CustomerFilter;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartInterface;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\AttributeInterface;

class CustomerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Cashondelivery|PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodMock;

    /**
     * @var CartInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var CustomerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    /**
     * @var AttributeInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;

    /**
     * @var CustomerFilter
     */
    protected $customerFilter;

    /**
     * @var DataObject
     */
    protected $result;

    protected function setUp()
    {
        parent::setUp();

        $objectManager = new ObjectManager($this);

        $this->paymentMethodMock = $this->getMockBuilder(Cashondelivery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteMock = $this->getMockBuilder(CartInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerMock = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeMock = $this->getMockBuilder(AttributeInterface::class)
            ->getMock();

        $this->customerFilter = $objectManager->getObject(CustomerFilter::class, []);

        $this->result = $objectManager->getObject(DataObject::class, []);
    }

    /**
     * @test
     */
    public function testExecuteWithCustomerThatIsNotAllowedToPayByCashOnDelivery()
    {
        $this->quoteMock
            ->expects($this->atLeastOnce())
            ->method('getCustomer')
            ->willReturn($this->customerMock);

        $this->attributeMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn('cashondelivery');

        $this->customerMock
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);

        $this->customerMock
            ->expects($this->atLeastOnce())
            ->method('getCustomAttribute')
            ->with('disallowed_payment_methods')
            ->willReturn($this->attributeMock);

        $this->paymentMethodMock
            ->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn(Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE);

        $this->result->setData('is_available', true);

        $this->customerFilter->execute($this->paymentMethodMock, $this->quoteMock, $this->result);

        $this->assertFalse($this->result->getData('is_available'));
    }

    /**
     * @test
     */
    public function testExecuteWithCustomerThatIsAllowedToPayByCashOnDelivery()
    {
        $this->attributeMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn('');

        $this->customerMock
            ->expects($this->atLeastOnce())
            ->method('getCustomAttribute')
            ->with('disallowed_payment_methods')
            ->willReturn($this->attributeMock);

        $this->customerMock
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);

        $this->quoteMock
            ->expects($this->atLeastOnce())
            ->method('getCustomer')
            ->willReturn($this->customerMock);

        $this->paymentMethodMock
            ->expects($this->never())
            ->method('getCode');

        $this->result->setData('is_available', true);

        $this->customerFilter->execute($this->paymentMethodMock, $this->quoteMock, $this->result);

        $this->assertTrue($this->result->getData('is_available'));
    }

    /**
     * @test
     */
    public function testExecuteWithGuest()
    {
        $this->attributeMock
            ->expects($this->never())
            ->method('getValue')
            ->willReturn('');

        $this->customerMock
            ->expects($this->never())
            ->method('getCustomAttribute')
            ->with('disallowed_payment_methods')
            ->willReturn($this->attributeMock);

        $this->customerMock
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->quoteMock
            ->expects($this->atLeastOnce())
            ->method('getCustomer')
            ->willReturn($this->customerMock);

        $this->paymentMethodMock
            ->expects($this->never())
            ->method('getCode');

        $this->result->setData('is_available', true);

        $this->customerFilter->execute($this->paymentMethodMock, $this->quoteMock, $this->result);

        $this->assertTrue($this->result->getData('is_available'));
    }

    /**
     * @test
     */
    public function testExecuteWithCustomAttributeEqualsNull()
    {
        $this->customerMock
            ->expects($this->atLeastOnce())
            ->method('getCustomAttribute')
            ->with('disallowed_payment_methods')
            ->willReturn(null);

        $this->customerMock
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);

        $this->quoteMock
            ->expects($this->atLeastOnce())
            ->method('getCustomer')
            ->willReturn($this->customerMock);

        $this->paymentMethodMock
            ->expects($this->never())
            ->method('getCode');

        $this->result->setData('is_available', true);

        $this->customerFilter->execute($this->paymentMethodMock, $this->quoteMock, $this->result);

        $this->assertTrue($this->result->getData('is_available'));
    }
}