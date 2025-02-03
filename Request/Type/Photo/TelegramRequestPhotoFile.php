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
 */

declare(strict_types=1);

namespace BaksDev\Telegram\Request\Type\Photo;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Этот объект представляет собой фотографию или миниатюру файла/стикера одного размера.
 *
 * @see https://core.telegram.org/bots/api#photosize
 */
final class TelegramRequestPhotoFile
{
    /**
     * Идентификатор этого файла, который можно использовать для загрузки или повторного использования файла.
     */
    private string $id;

    /**
     * Уникальный идентификатор этого файла, который должен быть одинаковым во времени и для разных ботов.
     * Невозможно использовать для загрузки или повторного использования файла.
     */
    private string $unique;

    /**
     * @note Optional.
     * Размер файла в байтах
     */
    private ?int $size = null;

    /** Ширина фотографии */
    private int $width;

    /** Высота фото */
    private int $height;

    /** Путь к локальному файлу */
    private string|false $path = false;

    /**
     * Id
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Unique
     */
    public function getUnique(): string
    {
        return $this->unique;
    }

    public function setUnique(string $unique): self
    {
        $this->unique = $unique;
        return $this;
    }

    /**
     * Size
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Width
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Height
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;
        return $this;
    }

    /**
     * Path
     */
    public function getPath(): string|false
    {
        return $this->path;
    }

    public function setPath(string|null|false $path): self
    {
        $this->path = empty($path) ? false : $path;

        return $this;
    }

}