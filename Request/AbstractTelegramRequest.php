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

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Core\Type\Locale\Locales\LocaleDisable;
use BaksDev\Core\Type\Locale\Locales\Ru;
use DateInterval;
use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

abstract class AbstractTelegramRequest implements TelegramRequestInterface
{
    /** Идентификатор сообщения */
    private int $id;

    /** Идентификатор предыдущего сообщения */
    private ?int $last = null;

    /** Идентификатор предыдущего системного сообщения */
    private ?int $system = null;

    /** Уникальный идентификатор обновления */
    private int $update;

    /** Дата */
    private int $date;

    /** Текст сообщения */
    private ?string $text = null;

    /** Локаль */
    private Locale $locale;

//    /** Действие, которое выполняется пользователем */
//    private ?string $action = null;
//

    private TelegramUserDTO $user;

    private TelegramChatDTO $chat;

    protected CacheInterface $cache;


    public function __construct(TelegramUserDTO $user, TelegramChatDTO $chat, CacheInterface $cache)
    {
        /** По умолчанию Locale Ru */
        $this->locale = new Locale(Ru::class);

        $this->user = $user;
        $this->chat = $chat;
        $this->cache = $cache;
    }

    /**
     * Дата
     */
    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): self
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Идентификатор предыдущего сообщения
     */
    public function getLast(): ?int
    {
        return $this->last;
    }

    public function setLast(?int $last): self
    {
        $this->last = $last;
        return $this;
    }


    /**
     * Текст сообщения
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        if($text === '/start')
        {
            $index = 'action-'.$this->getUserId();
            $this->cache->deleteItem($index);
        }

        $this->text = $text;
        return $this;
    }

    /**
     * Id
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Update
     */
    public function getUpdate(): int
    {
        return $this->update;
    }

    public function setUpdate(int $update): self
    {
        $this->update = $update;
        return $this;
    }

    /**
     * Locale
     */
    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function setLocale(mixed $locale): self
    {
        $locale = new Locale($locale);

        if(!$locale->getLocal() instanceof LocaleDisable)
        {
            $this->locale = $locale;
        }

        return $this;
    }


    /**
     * User
     */
    public function getUser(): TelegramUserDTO
    {
        return $this->user;
    }

    public function getUserId(): int
    {
        return $this->user->getId();
    }

    /**
     * Chat
     */
    public function getChat(): TelegramChatDTO
    {
        return $this->chat;
    }

    public function getChatId(): int
    {
        return $this->chat->getId();
    }

    /**
     * System
     */
    public function getSystem(): ?int
    {
        return $this->system;
    }

    public function setSystem(?int $system): self
    {
        $this->system = $system;
        return $this;
    }

    /**
     * Действие, которое выполняется пользователем
     */
    public function getAction(): ?string
    {
        $index = 'action-'.$this->getUserId();

        $actionItem = $this->cache->getItem($index);

        if (!$actionItem->isHit()) {
            return null;
        }

        return $actionItem->get();
    }

    public function setAction(?string $action): self
    {
        $index = 'action-'.$this->getUserId();

        if(!$action)
        {
            $this->cache->deleteItem($index);
        }

        /** @var CacheItemInterface $actionItem */
        $actionItem = $this->cache->getItem($index);
        $actionItem->set($action);
        $actionItem->expiresAfter(DateInterval::createFromDateString('1 day'));
        $this->cache->save($actionItem);

        return $this;
    }

}