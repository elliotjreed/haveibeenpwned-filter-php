<?php

declare(strict_types=1);

namespace Tests\ElliotJReed\HaveIBeenPwned;

use DateTime;
use ElliotJReed\HaveIBeenPwned\Pastes;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class PastesTest extends TestCase
{
    public function testItReturnsEmptyArrayIfNoPastes(): void
    {
        $mock = new MockHandler([
            new Response(200, [], '')
        ]);

        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $pastes = (new Pastes($client, 'fake-hibn-api-key'))->all('email@example.com');

        $this->assertSame('GET', $mock->getLastRequest()->getMethod());
        $this->assertSame('https', $mock->getLastRequest()->getUri()->getScheme());
        $this->assertSame('haveibeenpwned.com', $mock->getLastRequest()->getUri()->getHost());
        $this->assertSame('/api/v3/pasteaccount/email@example.com', $mock->getLastRequest()->getUri()->getPath());
        $this->assertSame('', $mock->getLastRequest()->getUri()->getQuery());
        $this->assertSame('/api/v3/pasteaccount/email@example.com', $mock->getLastRequest()->getRequestTarget());
        $this->assertSame(['fake-hibn-api-key'], $mock->getLastRequest()->getHeaders()['hibp-api-key']);
        $this->assertSame(['www.elliotjreed.com'], $mock->getLastRequest()->getHeaders()['user-agent']);

        $this->assertSame([], $pastes);
    }

    public function testItGetsAllPastes(): void
    {
        $response = '
          [
            {
              "Source":"Pastebin",
              "Id":"8Q0BvKD8",
              "Title":"syslog",
              "Date":"2014-03-04T19:14:54Z",
              "EmailCount":139
            },
            {
              "Source":"Pastie",
              "Id":"7152479",
              "Date":"2013-03-28T16:51:10Z",
              "EmailCount":30
            }
          ]
        ';

        $mock = new MockHandler([
            new Response(200, [], $response)
        ]);

        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $pastes = (new Pastes($client, 'fake-hibn-api-key'))->all('email@example.com');

        $this->assertSame('GET', $mock->getLastRequest()->getMethod());
        $this->assertSame('https', $mock->getLastRequest()->getUri()->getScheme());
        $this->assertSame('haveibeenpwned.com', $mock->getLastRequest()->getUri()->getHost());
        $this->assertSame('/api/v3/pasteaccount/email@example.com', $mock->getLastRequest()->getUri()->getPath());
        $this->assertSame('', $mock->getLastRequest()->getUri()->getQuery());
        $this->assertSame('/api/v3/pasteaccount/email@example.com', $mock->getLastRequest()->getRequestTarget());
        $this->assertSame(['fake-hibn-api-key'], $mock->getLastRequest()->getHeaders()['hibp-api-key']);
        $this->assertSame(['www.elliotjreed.com'], $mock->getLastRequest()->getHeaders()['user-agent']);

        $firstPaste = $pastes[0];
        $this->assertSame('Pastebin', $firstPaste->getSource());
        $this->assertSame('8Q0BvKD8', $firstPaste->getId());
        $this->assertSame('syslog', $firstPaste->getTitle());
        $this->assertEquals(new DateTime('2014-03-04T19:14:54Z'), $firstPaste->getDate());
        $this->assertSame(139, $firstPaste->getEmailCount());

        $secondPaste = $pastes[1];
        $this->assertSame('Pastie', $secondPaste->getSource());
        $this->assertSame('7152479', $secondPaste->getId());
        $this->assertNull($secondPaste->getTitle());
        $this->assertEquals(new DateTime('2013-03-28T16:51:10Z'), $secondPaste->getDate());
        $this->assertSame(30, $secondPaste->getEmailCount());
    }
}