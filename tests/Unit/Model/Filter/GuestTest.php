<?php

namespace DR\PaymentMethodFilter\Test\Unit\Model\Filter;

use DR\PaymentMethodFilter\Model\Filter\Guest as GuestFilter;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\Quote\Api\Data\CartInterface;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit\Framework\TestCase;

class GuestTest extends TestCase
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
     * @var GuestFilter
     */
    protected $guestFilter;

    /**
     * @var ScopeConfigInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

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

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->guestFilter = $objectManager->getObject(GuestFilter::class, [
            'scopeConfig' => $this->scopeConfigMock
        ]);

        $this->result = $objectManager->getObject(DataObject::class, []);
    }

    public function testExecuteWithGuestThatIsNotAllowedToPayByCashOnDelivery()
    {
        $this->customerMock
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->quoteMock
            ->expects($this->atLeastOnce())
            ->method('getCustomer')
            ->willReturn($this->customerMock);

        $this->scopeConfigMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->with(GuestFilter::XML_PATH_DISALLOWED_PAYMENT_METHODS_FOR_GUEST)
            ->willReturn(Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE . ',free');

        $this->paymentMethodMock
            ->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn(Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE);

        $this->result->setData('is_available', true);

        $this->guestFilter->execute($this->paymentMethodMock, $this->quoteMock, $this->result);

        $this->assertFalse($this->result->getData('is_available'));
    }

    /**
     * @test
     */
    public function testExecuteWithCustomer()
    {
        $this->customerMock
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);

        $this->quoteMock
            ->expects($this->atLeastOnce())
            ->method('getCustomer')
            ->willReturn($this->customerMock);

        $this->scopeConfigMock
            ->expects($this->never())
            ->method('getValue')
            ->with(GuestFilter::XML_PATH_DISALLOWED_PAYMENT_METHODS_FOR_GUEST)
            ->willReturn(Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE);

        $this->paymentMethodMock
            ->expects($this->never())
            ->method('getCode')
            ->willReturn(Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE);

        $this->result->setData('is_available', true);

        $this->guestFilter->execute($this->paymentMethodMock, $this->quoteMock, $this->result);

        $this->assertTrue($this->result->getData('is_available'));
    }

    public function testExecuteWithGuestThatIsAllowedToPayByCashOnDelivery()
    {
        $this->customerMock
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->quoteMock
            ->expects($this->atLeastOnce())
            ->method('getCustomer')
            ->willReturn($this->customerMock);

        $this->scopeConfigMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->with(GuestFilter::XML_PATH_DISALLOWED_PAYMENT_METHODS_FOR_GUEST)
            ->willReturn('free');

        $this->paymentMethodMock
            ->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn(Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE);

        $this->result->setData('is_available', true);

        $this->guestFilter->execute($this->paymentMethodMock, $this->quoteMock, $this->result);

        $this->assertTrue($this->result->getData('is_available'));
    }

    public function testExecuteWithoutConfig()
    {
        $this->customerMock
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->quoteMock
            ->expects($this->atLeastOnce())
            ->method('getCustomer')
            ->willReturn($this->customerMock);

        $this->scopeConfigMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->with(GuestFilter::XML_PATH_DISALLOWED_PAYMENT_METHODS_FOR_GUEST)
            ->willReturn(null);

        $this->paymentMethodMock
            ->expects($this->never())
            ->method('getCode');

        $this->result->setData('is_available', true);

        $this->guestFilter->execute($this->paymentMethodMock, $this->quoteMock, $this->result);

        $this->assertTrue($this->result->getData('is_available'));
    }
}