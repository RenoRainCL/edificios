<?php

// 📁 utils/TextManager.php
class TextManager
{
    private static $texts;

    public static function loadTexts()
    {
        if (self::$texts === null) {
            $textsPath = __DIR__.'/../config/texts_secure.json';
            if (file_exists($textsPath)) {
                self::$texts = json_decode(file_get_contents($textsPath), true);
            } else {
                self::$texts = [];
            }
        }

        return self::$texts;
    }

    public static function getText($key, $default = null)
    {
        $texts = self::loadTexts();

        return isset($texts[$key]) ? $texts[$key] : ($default ?? $key);
    }

    public static function getValidationMessages()
    {
        return self::getText('validation_messages', []);
    }

    public static function getLegalTextsChile()
    {
        return self::getText('legal_texts_chile', []);
    }

    public static function getMenuText($menuKey)
    {
        return self::getText("menu_$menuKey", ucfirst(str_replace('_', ' ', $menuKey)));
    }
}
