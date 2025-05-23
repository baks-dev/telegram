<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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
 *
 */

declare(strict_types=1);

namespace BaksDev\Telegram\Builder\ReplyKeyboardMarkup;

/**
 * Класс строит клавиатуру
 * @see https://core.telegram.org/bots/api#inlinekeyboardbutton
 */
final class ReplyKeyboardMarkup
{

    private const string INLINE_KEYBOARD = 'inline_keyboard';

    /**
     * @example
     *
     * 'inline_keyboard' =>
     * [
     *  [
     *      ['text' => 'Button 1', 'callback_data' => 'action_1'],
     *      ['text' => 'Button 2', 'callback_data' => 'action_2'],
     *  ],
     *  [
     *      ['text' => 'Open website', 'url' => 'https://example.com']
     *  ]
     */
    private array $inlineKeyboard = [
        self::INLINE_KEYBOARD => []
    ];

    private int $maxRowButtons = 3;

    /** Текущий индекс для отслеживания строк */
    private int $currentIndex = 0;

    private array $rows;

    /** Добавить кнопку в текущую строку */
    public function addCurrentRow(ReplyKeyboardButton $button)
    {
        $this->inlineKeyboard[self::INLINE_KEYBOARD][$this->currentIndex][] = $button->build();

        /** При достижении лимита кнопок в строке - переходим на новую строку */
        if(count($this->inlineKeyboard[self::INLINE_KEYBOARD][$this->currentIndex]) === $this->maxRowButtons)
        {
            $this->currentIndex++;
        }
    }

    /** Добавить кнопку в новую строку */
    public function addNewRow(ReplyKeyboardButton $button)
    {
        /** Текущий индекс */
        $currentIndex = key($this->inlineKeyboard[self::INLINE_KEYBOARD]);

        if($currentIndex !== null)
        {
            $this->currentIndex++;
        }

        $this->inlineKeyboard[self::INLINE_KEYBOARD][] = [$button->build()];
        $this->currentIndex++;
    }

    /** Возвращает массив с построенной клавиатурой */
    public function build(): array|null
    {
        if(true === empty($this->inlineKeyboard[self::INLINE_KEYBOARD]))
        {
            return null;
        }

        return $this->inlineKeyboard;
    }

    /** Устанавливает максимальное количество кнопок в строке */
    public function setMaxRowButtons(int $max): self
    {
        $this->maxRowButtons = $max;
        return $this;
    }

    /** @deprecated */
    public function addRow(ReplyKeyboardRows $rows): void
    {
        $this->rows[] = $rows->getRows();
    }

    /** @deprecated */
    public function getMarkup(): string
    {
        return json_encode([$this->rows]);
    }
}
