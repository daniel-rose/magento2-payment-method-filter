<?php

namespace DR\PaymentMethodFilter\Test\Unit\Observer;

use DR\PaymentMethodFilter\Model\Filter\Customer;
use DR\PaymentMethodFilter\Model\Filter\Guest;
use DR\PaymentMethodFilter\Model\Filter\QuoteContent;
use DR\PaymentMethodFilter\Model\FilterInterface;
use DR\PaymentMethodFilter\Observer\ExcludeDisallowedPaymentMethod;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\Quote\Model\Quote;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit\Framework\TestCase;

class ExcludeDisallowedPaymentMethodTest extends TestCase
{
    /**
     * @var DataObject
     */
    protected $result;

    /**
     * @var FilterInterface[]|PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $filterList = array();

    /**
     * @var ExcludeDisallowedPaymentMethod
     */
    protected $excludeDisallowedPaymentMethodObserver;

    /**
     * @var Event|PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventMock;

    /**
     * @var Observer|PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var Cashondelivery|PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodMock;

    /**
     * @var Quote|PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->result = $objectManager->getObject(DataObject::class);

        $this->filterList[] = $this->getMockBuilder(Guest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterList[] = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterList[] = $this->getMockBuilder(QuoteContent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentMethodMock = $this->getMockBuilder(Cashondelivery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethodInstance', 'getQuote', 'getResult'])
            ->getMock();

        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->excludeDisallowedPaymentMethodObserver = $objectManager->getObject(ExcludeDisallowedPaymentMethod::class, [
            'filterList' => $this->filterList
        ]);
    }

    /**
     * @test
     */
    public function testExecuteWithNotAvailablePaymentMethod()
    {
        $this->result->setData('is_available', false);

        $this->eventMock
            ->expects($this->never())
            ->method('getQuote');

        $this->eventMock
            ->expects($this->never())
            ->method('getMethodInstance');

        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn($this->result);

        $this->observerMock
            ->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->excludeDisallowedPaymentMethodObserver->execute($this->observerMock);

        $this->assertFalse($this->result->getData('is_available'));
    }

    /**
     * @test
     */
    public function testExecuteWithAllowedPaymentMethod()
    {
        $this->result->setData('is_available', true);

        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quoteMock);

        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getMethodInstance')
            ->willReturn($this->paymentMethodMock);

        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn($this->result);

        $this->observerMock
            ->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        foreach ($this->filterList as $filter) {
            $filter->expects($this->atLeastOnce())
                ->method('execute');
        }

        $this->excludeDisallowedPaymentMethodObserver->execute($this->observerMock);

        $this->assertTrue($this->result->getData('is_available'));
    }

    /**
     * @test
     */
    public function testExecuteWithPaymentMethodThatWillBeDisallowedByCustomer()
    {
        $this->result->setData('is_available', true);

        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quoteMock);

        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getMethodInstance')
            ->willReturn($this->paymentMethodMock);

        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn($this->result);

        $this->observerMock
            ->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->filterList[0]->expects($this->atLeastOnce())
            ->method('execute');

        $this->filterList[1]->expects($this->atLeastOnce())
            ->method('execute')
            ->willReturnCallback(function ($paymentMethod, $quote, $result) {
                $result->setData('is_available', false);
            });

        $this->filterList[2]->expects($this->never())
            ->method('execute');

        $this->excludeDisallowedPaymentMethodObserver->execute($this->observerMock);

        $this->assertFalse($this->result->getData('is_available'));
    }

    /**
     * @test
     */
    public function testExecuteWithAllowedPaymentMethodWithoutQuote()
    {
        $this->result->setData('is_available', true);

        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn(null);

        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getMethodInstance')
            ->willReturn($this->paymentMethodMock);

        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn($this->result);

        $this->observerMock
            ->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        foreach ($this->filterList as $filter) {
            $filter->expects($this->never())
                ->method('execute');
        }

        $this->excludeDisallowedPaymentMethodObserver->execute($this->observerMock);

        $this->assertTrue($this->result->getData('is_available'));
    }

    /**
     * @test
     */
    public function testExecuteWithAllowedPaymentMethodWithoutPaymentMethodInstance()
    {
        $this->result->setData('is_available', true);

        $this->eventMock
            ->expects($this->never())
            ->method('getQuote');

        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getMethodInstance')
            ->willReturn(null);

        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn($this->result);

        $this->observerMock
            ->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        foreach ($this->filterList as $filter) {
            $filter->expects($this->never())
                ->method('execute');
        }

        $this->excludeDisallowedPaymentMethodObserver->execute($this->observerMock);

        $this->assertTrue($this->result->getData('is_available'));
    }

    /**
     * @test
     */
    public function testExecuteWithAllowedPaymentMethodWithoutEvent()
    {
        $this->result->setData('is_available', true);

        $this->eventMock
            ->expects($this->never())
            ->method('getQuote');

        $this->eventMock
            ->expects($this->never())
            ->method('getMethodInstance');

        $this->eventMock
            ->expects($this->never())
            ->method('getResult');

        $this->observerMock
            ->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn(null);

        foreach ($this->filterList as $filter) {
            $filter->expects($this->never())
                ->method('execute');
        }

        $this->excludeDisallowedPaymentMethodObserver->execute($this->observerMock);

        $this->assertTrue($this->result->getData('is_available'));
    }
}