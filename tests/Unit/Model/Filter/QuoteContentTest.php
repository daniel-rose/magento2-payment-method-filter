<?php

namespace DR\PaymentMethodFilter\Test\Unit\Model\Filter;

use DR\PaymentMethodFilter\Model\Filter\QuoteContent;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item;
use PHPUnit_Framework_MockObject_MockObject;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class QuoteContentTest extends TestCase
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
     * @var Item|PHPUnit_Framework_MockObject_MockObject
     */
    protected $firstQuoteItemMock;

    /**
     * @var Item|PHPUnit_Framework_MockObject_MockObject
     */
    protected $secondQuoteItemMock;

    /**
     * @var ProductInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $firstProductMock;

    /**
     * @var ProductInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $secondProductMock;

    /**
     * @var AttributeInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMockForFirstProduct;

    /**
     * @var AttributeInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMockForSecondProduct;

    /**
     * @var QuoteContent
     */
    protected $quoteContentFilter;

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

        $this->firstQuoteItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->secondQuoteItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->firstProductMock = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->secondProductMock = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeMockForFirstProduct = $this->getMockBuilder(AttributeInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeMockForSecondProduct = $this->getMockBuilder(AttributeInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteContentFilter = $objectManager->getObject(QuoteContent::class, []);

        $this->result = $objectManager->getObject(DataObject::class, []);
    }

    public function testExecuteWithQuoteItemThatDisallowCashOnDelivery()
    {
        $this->attributeMockForFirstProduct
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn('');
        
        $this->firstProductMock
            ->expects($this->atLeastOnce())
            ->method('getCustomAttribute')
            ->with('disallowed_payment_methods')
            ->willReturn($this->attributeMockForFirstProduct);

        $this->firstQuoteItemMock
            ->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($this->firstProductMock);

        $this->attributeMockForSecondProduct
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn('cashondelivery,free');

        $this->secondProductMock
            ->expects($this->atLeastOnce())
            ->method('getCustomAttribute')
            ->with('disallowed_payment_methods')
            ->willReturn($this->attributeMockForSecondProduct);

        $this->secondQuoteItemMock
            ->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($this->secondProductMock);

        $this->quoteMock
            ->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn([$this->firstQuoteItemMock, $this->secondQuoteItemMock]);

        $this->paymentMethodMock
            ->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn(Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE);

        $this->result->setData('is_available', true);

        $this->quoteContentFilter->execute($this->paymentMethodMock, $this->quoteMock, $this->result);
        
        $this->assertFalse($this->result->getData('is_available'));
    }

    public function testExecuteWithQuoteItemsThatAllowAllPaymentMethods()
    {
        $this->attributeMockForFirstProduct
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn('');

        $this->firstProductMock
            ->expects($this->atLeastOnce())
            ->method('getCustomAttribute')
            ->with('disallowed_payment_methods')
            ->willReturn($this->attributeMockForFirstProduct);

        $this->firstQuoteItemMock
            ->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($this->firstProductMock);

        $this->attributeMockForSecondProduct
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn('');

        $this->secondProductMock
            ->expects($this->atLeastOnce())
            ->method('getCustomAttribute')
            ->with('disallowed_payment_methods')
            ->willReturn($this->attributeMockForSecondProduct);

        $this->secondQuoteItemMock
            ->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($this->secondProductMock);

        $this->quoteMock
            ->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn([$this->firstQuoteItemMock, $this->secondQuoteItemMock]);

        $this->paymentMethodMock
            ->expects($this->never())
            ->method('getCode')
            ->willReturn(Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE);

        $this->result->setData('is_available', true);

        $this->quoteContentFilter->execute($this->paymentMethodMock, $this->quoteMock, $this->result);

        $this->assertTrue($this->result->getData('is_available'));
    }

    public function testExecuteWithCustomAttributeEqualsNull()
    {
        $this->attributeMockForFirstProduct
            ->expects($this->never())
            ->method('getValue');

        $this->firstProductMock
            ->expects($this->atLeastOnce())
            ->method('getCustomAttribute')
            ->with('disallowed_payment_methods')
            ->willReturn(null);

        $this->firstQuoteItemMock
            ->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($this->firstProductMock);

        $this->attributeMockForSecondProduct
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn('');

        $this->secondProductMock
            ->expects($this->atLeastOnce())
            ->method('getCustomAttribute')
            ->with('disallowed_payment_methods')
            ->willReturn($this->attributeMockForSecondProduct);

        $this->secondQuoteItemMock
            ->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($this->secondProductMock);

        $this->quoteMock
            ->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn([$this->firstQuoteItemMock, $this->secondQuoteItemMock]);

        $this->paymentMethodMock
            ->expects($this->never())
            ->method('getCode')
            ->willReturn(Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE);

        $this->result->setData('is_available', true);

        $this->quoteContentFilter->execute($this->paymentMethodMock, $this->quoteMock, $this->result);

        $this->assertTrue($this->result->getData('is_available'));
    }
}