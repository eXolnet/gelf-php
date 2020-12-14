<?php

namespace Gelf\Test\Transport;

use Gelf\Message;
use Gelf\TestCase;
use Gelf\Transport\AbstractTransport;
use Gelf\Transport\RetryTransportWrapper;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;
use RuntimeException;

/**
 * @covers RetryTransportWrapper
 */
class RetryTransportWrapperTest extends TestCase
{
    /**
     * @const string
     */
    const SUCCESS_VALUE = "HTTP/1.1 202 Accepted\r\n\r\n";

    /**
     * @var Message
     */
    private $message;

    /**
     * @var AbstractTransport|MockObject
     */
    private $transport;

    /**
     * @var RetryTransportWrapper
     */
    private $wrapper;

    public function setUp()
    {
        $this->message = new Message();
        $this->transport = $this->buildTransport();
        $this->wrapper   = new RetryTransportWrapper($this->transport);
    }

    public function testSendSuccess()
    {
        $this->transport->expects($this->once())
            ->method('send')
            ->with($this->message)
            ->will($this->returnValue(self::SUCCESS_VALUE));

        $bytes = $this->wrapper->send($this->message);

        $this->assertEquals(self::SUCCESS_VALUE, $bytes);
    }

    public function testSendFailOnce()
    {
        $expectedException = new \RuntimeException();

        $this->transport->expects($this->exactly(2))
            ->method('send')
            ->with($this->message)
            ->will($this->onConsecutiveCalls(
                $this->throwException($expectedException),
                $this->returnValue(self::SUCCESS_VALUE)
            ));

        $bytes = $this->wrapper->send($this->message);

        $this->assertEquals(self::SUCCESS_VALUE, $bytes);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Exception 2
     */
    public function testSendFail()
    {
        $expectedException1 = new RuntimeException('Exception 1');
        $expectedException2 = new RuntimeException('Exception 2');

        $this->transport->expects($this->exactly(2))
            ->method('send')
            ->with($this->message)
            ->will($this->onConsecutiveCalls(
                $this->throwException($expectedException1),
                $this->throwException($expectedException2)
            ));

        $this->wrapper->send($this->message);
    }

    /**
     * @return MockObject|AbstractTransport
     */
    private function buildTransport()
    {
        return $this->getMockForAbstractClass("\\Gelf\\Transport\\AbstractTransport");
    }
}
