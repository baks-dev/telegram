<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

final class TelegramUserDTO
{
    private int $id;

    /**
     * Верно, если этот пользователь — бот
     */
    private bool $is_bot = false;

    /**
     * Имя пользователя или бота
     */
    private string $first_name;

    /**
     * @note Optional.
     * Фамилия пользователя или бота
     */
    private ?string $last_name = null;

    /**
     * @note Optional.
     * Имя пользователя или бота
     */
    private ?string $username = null;

    /**
     * @note Optional.
     * Верно, если этот пользователь является пользователем Telegram Premium.
     */
    private bool $premium = false;

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
     * IsBot
     */
    public function isIsBot(): bool
    {
        return $this->is_bot;
    }

    public function setIsBot(bool $is_bot): self
    {
        $this->is_bot = $is_bot;
        return $this;
    }

    /**
     * FirstName
     */
    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;
        return $this;
    }

    /**
     * LastName
     */
    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string|bool $last_name): self
    {
        if(is_string($last_name))
        {
            $this->last_name = $last_name;
        }

        return $this;
    }

    /**
     * Username
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Premium
     */
    public function isPremium(): bool
    {
        return $this->premium;
    }

    public function setPremium(bool $premium): self
    {
        $this->premium = $premium;
        return $this;
    }
}