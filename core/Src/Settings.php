<?php

namespace Src;

use Error;
use function Collect\collection;

class Settings
{
    private array $_settings;

    public function __construct(array $settings = [])
    {
        $this->_settings = $settings;
    }

    public function __get($key)
    {
        if (collection($this->_settings)->has($key)) {
            return $this->_settings[$key];
        }
        throw new Error('Accessing a non-existent property');
    }

    public function getDbSetting(): array
    {
        return $this->db ?? [];
    }

    public function getRootPath(): string
    {
        return $this->path['root'] ? '/' . $this->path['root'] : '';
    }

    public function getViewsPath(): string
    {
        return '/' . $this->path['views'] ?? '';
    }
}