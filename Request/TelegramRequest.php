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
use BaksDev\Telegram\Api\TelegramChatAction;
use BaksDev\Telegram\Api\TelegramGetFile;
use BaksDev\Telegram\Bot\Repository\UsersTableTelegramSettings\TelegramBotSettingsInterface;
use BaksDev\Telegram\Request\Type\Photo\TelegramRequestPhoto;
use BaksDev\Telegram\Request\Type\Photo\TelegramRequestPhotoFile;
use BaksDev\Telegram\Request\Type\TelegramRequestAudio;
use BaksDev\Telegram\Request\Type\TelegramRequestCallback;
use BaksDev\Telegram\Request\Type\TelegramRequestDocument;
use BaksDev\Telegram\Request\Type\TelegramRequestIdentifier;
use BaksDev\Telegram\Request\Type\TelegramRequestLocation;
use BaksDev\Telegram\Request\Type\TelegramRequestMessage;
use BaksDev\Telegram\Request\Type\TelegramRequestQrcode;
use BaksDev\Telegram\Request\Type\TelegramRequestVideo;
use DateInterval;
use JsonException;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Zxing\QrReader;

final class TelegramRequest
{

    private ?TelegramRequestInterface $telegramRequest = null;

    private object $request;

    private RequestStack $requestStack;

    private CacheInterface $cache;

    private LoggerInterface $logger;

    private TelegramBotSettingsInterface $telegramBotSettings;

    private TelegramGetFile $telegramGetFile;

    private TelegramChatAction $telegramChatAction;

    public function __construct(
        RequestStack $requestStack,
        LoggerInterface $telegramLogger,
        AppCacheInterface $appCache,
        TelegramBotSettingsInterface $telegramBotSettings,
        TelegramGetFile $telegramGetFile,
        TelegramChatAction $telegramChatAction
    )
    {
        $this->requestStack = $requestStack;
        $this->cache = $appCache->init('telegram'); // new ApcuAdapter('telegram');
        $this->logger = $telegramLogger;
        $this->telegramBotSettings = $telegramBotSettings;
        $this->telegramGetFile = $telegramGetFile;
        $this->telegramChatAction = $telegramChatAction;
    }

    public function request(?Request $req = null): ?TelegramRequestInterface
    {

        /** @var Request $Request */
        $Request = $req ?: $this->requestStack->getCurrentRequest();

        $secretToken = $Request->headers->get('x-telegram-bot-api-secret-token');

        if(!$secretToken)
        {
            $this->logger->critical('Отсутствует заголовок X-Telegram-Bot-Api-Secret-Token', [self::class.':'.__LINE__]);

            return $this->telegramRequest = null;
        }

        $settings = $this->telegramBotSettings->settings();

        if(!$settings->equalsSecret($secretToken))
        {
            $this->logger->critical('Не соответствует заголовок X-Telegram-Bot-Api-Secret-Token', [self::class.':'.__LINE__]);

            return $this->telegramRequest = null;
        }

        if($this->telegramRequest)
        {
            return $this->telegramRequest;
        }

        $data = $Request->getContent();

        try
        {
            $this->request = json_decode($data, false, 512, JSON_THROW_ON_ERROR);
        }
        catch(JsonException)
        {
            return $this->telegramRequest = null;
        }

        $this->logger->debug($data, [self::class.':'.__LINE__]);

        if(property_exists($this->request, 'callback_query') && !empty($this->request->callback_query))
        {
            if(is_null($this->telegramRequest))
            {
                return $this->telegramRequest = null;
            }

            $this->responseCallback();
            $this->telegramChatAction->chanel($this->telegramRequest->getChatId())->send();

            return $this->telegramRequest;
        }

        if(!property_exists($this->request, 'message'))
        {
            $this->logger->critical(sprintf('Запрос невозможно распознать: %s', $data), [self::class.':'.__LINE__]);
            return $this->telegramRequest = null;
        }

        $TelegramRequest = match (true)
        {
            property_exists($this->request->message, 'photo') && !empty($this->request->message->photo) => $this->responsePhoto(),
            property_exists($this->request->message, 'audio') && !empty($this->request->message->audio) => $this->responseAudio(),
            property_exists($this->request->message, 'video') && !empty($this->request->message->video) => $this->responseVideo(),
            property_exists($this->request->message, 'document') && !empty($this->request->message->document) => $this->responseDocument(),
            property_exists($this->request->message, 'location') && !empty($this->request->message->location) => $this->responseLocation(),
            default => $this->responseMessage()
        };

        if(!$TelegramRequest)
        {
            $this->telegramRequest = null;
        }

        $this->telegramChatAction->chanel($this->telegramRequest->getChatId())->send();

        $message = $this->request->message;
        $TelegramRequest->setUpdate($this->request->update_id);

        /** Присваиваем идентификатор предыдущего сообщения */
        $lastItem = $this->cache->getItem('last-'.$TelegramRequest->getChatId());
        $lastId = (int) $lastItem->get();
        $TelegramRequest->setLast($lastId);

        /** Присваиваем идентификатор системного сообщения */
        $systemItem = $this->cache->getItem('system-'.$TelegramRequest->getChatId());
        $TelegramRequest->setSystem((int) $systemItem->get());

        if(property_exists($message, 'message_id'))
        {
            if($message->message_id === $lastId)
            {
                $this->logger->warning(sprintf('Дубликат запроса: %s', $data), [self::class.':'.__LINE__]);

                return $this->telegramRequest = null;
            }

            $TelegramRequest->setId($message->message_id);
        }

        if(property_exists($message, 'date'))
        {
            $TelegramRequest->setDate($message->date);
        }

        if(property_exists($message, 'text'))
        {
            $TelegramRequest->setText($message->text);

            if(in_array($message->text, ['menu', '/menu', 'start', '/start']))
            {
                $index = 'action-'.$TelegramRequest->getChatId();
                $this->cache->deleteItem($index);
            }
        }

        if(property_exists($message, 'language_code'))
        {
            $TelegramRequest->setLocale($message->language_code);
        }

        /** Сохраняем в кеш идентификатор текущего сообщения для последующего присвоения */
        $lastItem->set($message->message_id);
        $lastItem->expiresAfter(DateInterval::createFromDateString('1 day'));
        $this->cache->save($lastItem);

        return $TelegramRequest;
    }

    private function responseCallback(): ?TelegramRequestCallback
    {
        $TelegramRequestCallback = new TelegramRequestCallback(
            $this->getUser(),
            $this->getChat()
        );

        $query = $this->request->callback_query;

        if($query->data)
        {
            $calls = explode('|', $query->data, 2);
            $currentCall = current($calls);

            $TelegramRequestCallback->setCall($currentCall);

            if(isset($calls[1]))
            {
                $TelegramRequestCallback->setIdentifier(end($calls));

                $indexAction = 'action-'.$TelegramRequestCallback->getChatId();

                if($currentCall === 'action')
                {
                    /** @var CacheItemInterface $actionItem */
                    $actionItem = $this->cache->getItem($indexAction);
                    $actionItem->set(end($calls));
                    $actionItem->expiresAfter(DateInterval::createFromDateString('1 day'));
                    $this->cache->save($actionItem);
                }
                else
                {
                    $this->cache->deleteItem($indexAction);
                }
            }
        }

        if(property_exists($query, 'id'))
        {
            $TelegramRequestCallback->setId((int) $query->message->message_id);
        }

        if(property_exists($query, 'date'))
        {
            $TelegramRequestCallback->setDate($query->date);
        }

        if(property_exists($query, 'text'))
        {
            $TelegramRequestCallback->setText($query->text);

            $indexAction = 'action-'.$TelegramRequestCallback->getChatId();

            if(in_array($query->text, ['menu', '/menu', 'start', '/start']))
            {
                $this->cache->deleteItem($indexAction);
            }

            /** @var CacheItemInterface $actionItem */
            $actionItem = $this->cache->getItem($indexAction);
            $TelegramRequestCallback->setAction($actionItem->get());

        }


        $TelegramRequestCallback->setUpdate($this->request->update_id);

        /** Присваиваем идентификатор предыдущего сообщения */
        $lastItem = $this->cache->getItem('last-'.$TelegramRequestCallback->getChatId());
        $lastId = (int) $lastItem->get();

        if($query->message->message_id === $lastId)
        {
            $this->logger->warning(sprintf('Дубликат запроса клика кнопки: %s', $query->data), [self::class.':'.__LINE__]);

            return $this->telegramRequest = null;
        }

        $TelegramRequestCallback->setLast($lastId);

        /** Присваиваем идентификатор системного сообщения */
        $systemItem = $this->cache->getItem('system-'.$TelegramRequestCallback->getChatId());
        $TelegramRequestCallback->setSystem((int) $systemItem->get());


        /** Сохраняем в кеш идентификатор текущего сообщения */
        $lastItem->set($query->message->message_id);
        $lastItem->expiresAfter(DateInterval::createFromDateString('1 day'));
        $this->cache->save($lastItem);

        return $this->telegramRequest = $TelegramRequestCallback;
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

    private function responsePhoto(): TelegramRequestIdentifier|TelegramRequestPhoto|TelegramRequestQrcode|null
    {
        $TelegramRequestPhoto = new TelegramRequestPhoto($this->getUser(), $this->getChat());

        /** Делаем пред загрузку фото */

        $photos = $this->request->message->photo;

        /** Скачиваем по порядку фото для анализа  */
        foreach($photos as $photo)
        {
            /* скачиваем во временный файл фото по идентификатору */
            $file = $this->telegramGetFile
                ->file($photo->file_id)
                ->send(false);

            /** Проверяем, является ли фото QR-кодом с идентификатором */

            $qrcode = new QrReader($file['tmp_file']);
            $QRdata = (string) $qrcode->text(); // декодированный текст из QR-кода

            if($QRdata)
            {
                /** Удаляем временный файл после анализа */
                unlink($file['tmp_file']);

                /** Если QR является идентификатором - присваиваем TelegramRequestIdentifier */
                if($QRdata && preg_match('{^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$}Di', $QRdata))
                {
                    $TelegramRequestIdentifier = new TelegramRequestIdentifier($this->getUser(), $this->getChat());
                    $TelegramRequestIdentifier->setIdentifier($QRdata);

                    return $this->telegramRequest = $TelegramRequestIdentifier;
                }

                $TelegramRequestQrcode = new TelegramRequestQrcode($this->getUser(), $this->getChat());

                return $this->telegramRequest = $TelegramRequestQrcode->setText($QRdata);

            }

            /**
             * Создаем TelegramRequestPhotoFile
             */
            $TelegramRequestPhotoFile = new TelegramRequestPhotoFile();
            $TelegramRequestPhotoFile
                ->setId($photo->file_id)
                ->setUnique($photo->file_unique_id)
                ->setWidth($photo->width)
                ->setHeight($photo->height)
                ->setPath($file['tmp_file']);

            if(property_exists($photo, 'file_size'))
            {
                $TelegramRequestPhotoFile->setSize($photo->file_size);
            }

            $TelegramRequestPhoto->addPhoto($TelegramRequestPhotoFile);
        }

        return $this->telegramRequest = $TelegramRequestPhoto;
    }


    private function responseMessage(): TelegramRequestMessage|TelegramRequestIdentifier|null
    {
        $message = $this->request->message;

        /** Если текст является идентификатором - присваиваем TelegramRequestIdentifier */
        if($message->text && preg_match('{^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$}Di', $message->text))
        {
            $TelegramRequestIdentifier = new TelegramRequestIdentifier($this->getUser(), $this->getChat());
            $TelegramRequestIdentifier->setIdentifier($message->text);

            return $this->telegramRequest = $TelegramRequestIdentifier;
        }

        $TelegramRequestMessage = new TelegramRequestMessage($this->getUser(), $this->getChat());

        return $this->telegramRequest = $TelegramRequestMessage;
    }


    public function getUser(): TelegramUserDTO
    {
        $data = null;

        if(property_exists($this->request, 'message'))
        {
            $data = $this->request->message->from;
        }

        if(property_exists($this->request, 'callback_query'))
        {
            $data = $this->request->callback_query->message->from;
        }

        $user = new TelegramUserDTO();

        if(!$data)
        {
            return $user;
        }

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

        $data = null;

        if(property_exists($this->request, 'message'))
        {
            $data = $this->request->message->chat;
        }

        if(property_exists($this->request, 'callback_query'))
        {
            $data = $this->request->callback_query->message->chat;
        }

        $chat = new TelegramChatDTO();

        if(!$data)
        {
            return $chat;
        }

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