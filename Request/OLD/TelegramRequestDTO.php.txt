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

final class TelegramRequestDTO
{
    public int $update_id;

    public ?Telegram\TelegramRequestMessageDTO $message = null;

    public ?Telegram\TelegramRequestCallbackDTO $callback_query = null;


    public function getUpdate(): int
    {
        return $this->update_id;
    }

    public function getChat(): ?int
    {
        return match (true) {
            $this->isMessages() => $this->message->chat->id,
            $this->isCallbacks() => $this->callback_query->message->chat->id,
            default => null,
        };
    }

    public function getMessageId(): ?int
    {
        return match (true) {
            $this->isMessages() => $this->message->message_id,
            $this->isCallbacks() => $this->callback_query->message->message_id,
            default => null,
        };
    }

    public function getData(): ?int
    {
        return match (true) {
            $this->isMessages() => $this->message->text,
            $this->isCallbacks() => $this->callback_query->data,
            default => null,
        };
    }


    public function isBot(): bool
    {
        return match (true) {
            $this->isMessages() => $this->message->from->is_bot,
            $this->isCallbacks() => $this->callback_query->message->from->is_bot,
            default => true,
        };
    }

    public function isPhoto(): bool
    {
        return $this->message->photo !== null;
    }

    /** **************** */

    private function isMessages(): bool
    {
        return $this->message !== null;
    }

    private function isCallbacks(): bool
    {
        return $this->callback_query !== null;
    }

}