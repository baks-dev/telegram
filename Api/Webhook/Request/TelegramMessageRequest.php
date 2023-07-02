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

namespace BaksDev\Telegram\Api\Webhook\Request;

final class TelegramMessageRequest
{
    private int $message_id; // ": 11

    private string $text; // "привет"

    private int $date; //  +"date": 1688060632

    private ?From $from = null;

    private ?Chat $chat = null;

    public function __construct(string $json)
    {


    }

    /**
     * MessageId.
     */
    public function getId(): int
    {
        return $this->message_id;
    }

    /**
     * Text.
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * From.
     */
    public function getFrom(): From
    {
        return $this->from;
    }

    /**
     * Chat.
     */
    public function getChat(): Chat
    {
        return $this->chat;
    }
}

//
//+"from": {#2164 ▶
//    +"id": 661608960
//    + "is_bot": false
//    + "first_name": "𝔏𝔦𝔩𝔦𝔱𝔥"
//    + "username": "ivoryfIower"
//    + "language_code": "ru"
//}

//
//+"chat": {#2165 ▶
//    +"id": 661608960
//    + "first_name": "𝔏𝔦𝔩𝔦𝔱𝔥"
//    + "username": "ivoryfIower"
//    + "type": "private"
//    }
//    +"date": 1688060632
//
//
//+ "text": "привет"
//}
