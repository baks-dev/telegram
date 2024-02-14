<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Telegram\Request;

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Telegram\Bot\Repository\UsersTableTelegramSettings\GetTelegramBotSettingsInterface;
use BaksDev\Telegram\Request\Type\TelegramRequestCallbackDTO;
use BaksDev\Telegram\Request\Type\TelegramRequestMessageDTO;
use BaksDev\Telegram\Request\Type\TelegramRequestProtoDTO;
use DateInterval;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;

final class TelegramRequest
{
    private object $request;

    private RequestStack $requestStack;

    private CacheInterface $cache;

    private LoggerInterface $logger;
    private GetTelegramBotSettingsInterface $telegramBotSettings;

    public function __construct(
        RequestStack $requestStack,
        LoggerInterface $telegramLogger,
        AppCacheInterface $appCache,
        GetTelegramBotSettingsInterface $telegramBotSettings
    )
    {
        $this->requestStack = $requestStack;
        $this->cache = $appCache->init('telegram'); // new ApcuAdapter('telegram');
        $this->logger = $telegramLogger;
        $this->telegramBotSettings = $telegramBotSettings;
    }

    public function response(): ?TelegramResponseInterface
    {
        $secretToken = $this->requestStack->getCurrentRequest()
            ->headers->get('x-telegram-bot-api-secret-token');

        if(!$secretToken)
        {
            $this->logger->critical('Отсутствует заголовок X-Telegram-Bot-Api-Secret-Token');
            return null;
        }

        $settings = $this->telegramBotSettings->settings();

        if(!$settings->equalsSecret($secretToken))
        {
            $this->logger->critical('Не соответствует заголовок X-Telegram-Bot-Api-Secret-Token');
            return null;
        }

        $data = $this->requestStack->getCurrentRequest()?->getContent();
        $this->logger->debug($data);

        $this->request = json_decode($data, false, 512, JSON_THROW_ON_ERROR);

        if(!property_exists($this->request, 'message'))
        {
            return null;
        }

        if(property_exists($this->request, 'callback_query') && !empty($this->request->callback_query))
        {
            return new TelegramRequestCallbackDTO($this->getUser(), $this->getChat());
        }

        if(property_exists($this->request->message, 'photo') && !empty($this->request->message->photo))
        {
            return new TelegramRequestProtoDTO($this->getUser(), $this->getChat());
        }

        return $this->responseMessage();
    }

    public function getUser(): TelegramUserDTO
    {
        $user = new TelegramUserDTO();

        $data = $this->request->message->from;

        $user
            ->setId($data->id)
            ->setUsername($data->username)
            ->setFirstName($data->first_name)
            ->setLastName($data->last_name)
            ->setIsBot($data->is_bot);

        return $user;
    }


    public function getChat(): TelegramChatDTO
    {
        $chat = new TelegramChatDTO();

        $data = $this->request->message->chat;

        $chat
            ->setId($data->id)
            ->setUsername($data->username)
            ->setFirstName($data->first_name)
            ->setLastName($data->last_name)
            ->setType($data->type);

        return $chat;
    }

    public function responseMessage(): ?TelegramRequestMessageDTO
    {
        $TelegramRequestMessageDTO = new TelegramRequestMessageDTO($this->getUser(), $this->getChat());

        $message = $this->request->message;

        $lastItem = $this->cache->getItem('last-'.$TelegramRequestMessageDTO->getUserId());

        $TelegramRequestMessageDTO
            ->setId($message->message_id)
            ->setUpdate($this->request->update_id)
            ->setDate($message->date)
            ->setText($message->text)
            ->setLast((int) $lastItem->get());

        /** Пересохраняем идентификатор сообщения */
        $lastItem->set($message->message_id);
        $lastItem->expiresAfter(DateInterval::createFromDateString('1 day'));
        $this->cache->save($lastItem);

        return $TelegramRequestMessageDTO;
    }

    /*$data = [

    "update_id" => 123456789,

    "callback_query" => null,

    "message" => [
        "message_id" => 123,

        "from" => [
            "id" => 123456,
            "is_bot" => false,
            "first_name" => "John",
            "last_name" => "Doe",
            "username" => "johndoe"
        ],

        "chat" => [
            "id" => 123456,
            "type" => "private",
            "first_name" => "John",
            "last_name" => "Doe",
            "username" => "johndoe"
        ],
        "date" => 1634323289,
        "text" => "Hello, world!",
        "photo" => null
    ]*/

}