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
use BaksDev\Telegram\Request\Type\TelegramRequestAudio;
use BaksDev\Telegram\Request\Type\TelegramRequestCallback;
use BaksDev\Telegram\Request\Type\TelegramRequestDocument;
use BaksDev\Telegram\Request\Type\TelegramRequestLocation;
use BaksDev\Telegram\Request\Type\TelegramRequestMessage;
use BaksDev\Telegram\Request\Type\TelegramRequestPhoto;
use BaksDev\Telegram\Request\Type\TelegramRequestVideo;
use DateInterval;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;

final class TelegramRequest
{

    private ?TelegramRequestInterface $telegramRequest = null;

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

    public function request(): ?TelegramRequestInterface
    {
        $secretToken = $this->requestStack->getCurrentRequest()
            ->headers->get('x-telegram-bot-api-secret-token');

        if(!$secretToken)
        {
            $this->telegramRequest = null;
            $this->logger->critical('Отсутствует заголовок X-Telegram-Bot-Api-Secret-Token');
            return null;
        }

        $settings = $this->telegramBotSettings->settings();

        if(!$settings->equalsSecret($secretToken))
        {
            $this->telegramRequest = null;
            $this->logger->critical('Не соответствует заголовок X-Telegram-Bot-Api-Secret-Token');
            return null;
        }

        if($this->telegramRequest)
        {
            return $this->telegramRequest;
        }

        $data = $this->requestStack->getCurrentRequest()?->getContent();
        $this->logger->debug($data);

        $this->request = json_decode($data, false, 512, JSON_THROW_ON_ERROR);

        if(!property_exists($this->request, 'message'))
        {
            $this->telegramRequest = null;
            return null;
        }

        if(property_exists($this->request, 'callback_query') && !empty($this->request->callback_query))
        {
            return $this->responseCallback();
        }

        return match (true)
        {
            property_exists($this->request->message, 'photo') && !empty($this->request->message->photo) => $this->responsePhoto(),
            property_exists($this->request->message, 'audio') && !empty($this->request->message->audio) => $this->responseAudio(),
            property_exists($this->request->message, 'video') && !empty($this->request->message->video) => $this->responseVideo(),
            property_exists($this->request->message, 'document') && !empty($this->request->message->document) => $this->responseDocument(),
            property_exists($this->request->message, 'location') && !empty($this->request->message->location) => $this->responseLocation(),
            default => $this->responseMessage()
        };
    }


    private function responseCallback(): ?TelegramRequestCallback
    {
        $TelegramRequestCallback = new TelegramRequestCallback($this->getUser(), $this->getChat());

        return $TelegramRequestCallback;
    }

    private function responseVideo(): ?TelegramRequestVideo
    {
        $TelegramRequestVideo = new TelegramRequestVideo($this->getUser(), $this->getChat());

        return $TelegramRequestVideo;
    }

    private function responseAudio(): ?TelegramRequestAudio
    {
        $TelegramRequestAudio = new TelegramRequestAudio($this->getUser(), $this->getChat());

        return $this->telegramRequest = $TelegramRequestAudio;
    }

    private function responseDocument(): ?TelegramRequestDocument
    {
        $TelegramRequestDocument = new TelegramRequestDocument($this->getUser(), $this->getChat());

        return $this->telegramRequest = $TelegramRequestDocument;
    }

    private function responseLocation(): ?TelegramRequestLocation
    {
        $TelegramRequestLocation = new TelegramRequestLocation($this->getUser(), $this->getChat());

        return $this->telegramRequest = $TelegramRequestLocation;
    }

    private function responsePhoto(): ?TelegramRequestPhoto
    {
        $TelegramRequestPhoto = new TelegramRequestPhoto($this->getUser(), $this->getChat());

        return $this->telegramRequest = $TelegramRequestPhoto;
    }


    private function responseMessage(): ?TelegramRequestMessage
    {
        $TelegramRequestMessage = new TelegramRequestMessage($this->getUser(), $this->getChat());

        $message = $this->request->message;

        $TelegramRequestMessage->setUpdate($this->request->update_id);

        /** Присваиваем идентификатор предыдущего сообщения */
        $lastItem = $this->cache->getItem('last-'.$TelegramRequestMessage->getUserId());
        $TelegramRequestMessage->setLast((int) $lastItem->get());

        $systemItem = $this->cache->getItem('system-'.$TelegramRequestMessage->getChatId());
        $TelegramRequestMessage->setSystem((int) $systemItem->get());

        if(property_exists($message, 'message_id'))
        {
            $TelegramRequestMessage->setId($message->message_id);
        }

        if(property_exists($message, 'date'))
        {
            $TelegramRequestMessage->setDate($message->date);
        }

        if(property_exists($message, 'text'))
        {
            $TelegramRequestMessage->setText($message->text);
        }

        if(property_exists($message, 'language_code'))
        {
            $TelegramRequestMessage->setLocale($message->language_code);
        }

        /** Сохраняем идентификатор текущего сообщения */
        $lastItem->set($message->message_id);
        $lastItem->expiresAfter(DateInterval::createFromDateString('1 day'));
        $this->cache->save($lastItem);

        return $this->telegramRequest = $TelegramRequestMessage;
    }


    public function getUser(): TelegramUserDTO
    {
        $user = new TelegramUserDTO();

        $data = $this->request->message->from;

        $user
            ->setId($data->id)
            ->setIsBot((bool) $data->is_bot)
            ->setFirstName($data->first_name);

        /**
         * @note Optional.
         * Имя пользователя или бота
         */
        if(property_exists($data, 'username'))
        {
            $user->setUsername($data->username);
        }

        /**
         * @note Optional.
         * Фамилия пользователя или бота
         */
        if(property_exists($data, 'last_name'))
        {
            $user->setLastName($data->last_name);
        }

        /**
         * @note Optional.
         * Фамилия пользователя или бота
         */
        if(property_exists($data, 'is_premium'))
        {
            $user->setLastName($data->is_premium);
        }

        return $user;
    }

    public function getChat(): TelegramChatDTO
    {
        $chat = new TelegramChatDTO();

        $data = $this->request->message->chat;

        $chat
            ->setId($data->id)
            ->setType($data->type);


        /**
         * @note Optional.
         * Имя пользователя для частных чатов, супергрупп и каналов, если они доступны.
         */
        if(property_exists($data, 'username'))
        {
            $chat->setUsername($data->username);
        }

        /**
         * @note Optional.
         * Имя собеседника в приватном чате
         */
        if(property_exists($data, 'first_name'))
        {
            $chat->setFirstName($data->first_name);
        }


        /**
         * @note  Optional.
         * Фамилия собеседника в приватном чате
         */
        if(property_exists($data, 'last_name'))
        {
            $chat->setLastName($data->last_name);
        }

        return $chat;
    }


}