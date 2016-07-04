<?php

namespace DR\PaymentMethodFilter\Test\Unit\Model\Check;

use DR\PaymentMethodFilter\Model\Filter\Guest as GuestFilter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\Quote\Model\Quote;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

class GuestTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Cashondelivery|PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodMock;

    /**
     * @var Quote|PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

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

        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId'])
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
        $this->quoteMock
            ->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn(null);

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
        $this->quoteMock
            ->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn(1);

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
        $this->quoteMock
            ->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn(null);

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
}