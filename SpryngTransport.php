<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\Spryng;

use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Paul Rijke
 */
final class SpryngTransport extends AbstractTransport
{
    protected const HOST = 'rest.spryngsms.com';

    public function __construct(
        #[\SensitiveParameter] private readonly string $apiKey,
        private readonly string $sender,
        private readonly int|string $route,
        ?HttpClientInterface $client = null,
        ?EventDispatcherInterface $dispatcher = null
    ) {
        parent::__construct($client, $dispatcher);
    }

    public function __toString(): string
    {
        return sprintf('spryng://%s?sender=%s&route=%s', $this->getEndpoint(), $this->sender, $this->route);
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage;
    }

    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$message instanceof SmsMessage) {
            throw new UnsupportedMessageTypeException(__CLASS__, SmsMessage::class, $message);
        }

        $sender = $message->getFrom() ?: $this->sender;

        $response = $this->client->request('POST', 'https://'.$this->getEndpoint().'/v1/messages', [
            'json' => [
                'sender' => $sender,
                'encoding' => 'auto',
                'originator' => $sender,
                'recipients' => [$message->getPhone()],
                'body' => $message->getSubject(),
                'route' => $this->route ?? 'business',
            ],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$this->apiKey}",
            ],
        ]);

        try {
            $statusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw new TransportException('Could not reach the remote Spryng server.', $response, 0, $e);
        }

        if (201 !== $statusCode) {
            $error = $response->toArray(false);

            throw new TransportException('Unable to send the SMS: '.($error['message'] ?? $response->getContent(false)), $response);
        }

        $success = $response->toArray(false);

        $sentMessage = new SentMessage($message, (string) $this);
        $sentMessage->setMessageId($success['messageId']);

        return $sentMessage;
    }
}
