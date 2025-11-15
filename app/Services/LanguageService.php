<?php

namespace App\Services;

use InvalidArgumentException;
use Exception;

/**
 * LanguageService - Comprehensive multilingual support service
 * 
 * Handles language detection, switching, translation loading, and localization
 * for English and Afaan Oromoo with fallback mechanisms and caching.
 * 
 * @package App\Services
 * @version 1.0.0
 */
class LanguageService
{
    public const SUPPORTED_LANGUAGES = [
        'en' => [
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'rtl' => false,
            'locale' => 'en_US',
            'flag' => '🇺🇸',
            'charset' => 'UTF-8'
        ],
        'om' => [
            'code' => 'om',
            'name' => 'Oromo',
            'native_name' => 'Afaan Oromoo',
            'rtl' => false,
            'locale' => 'om_ET',
            'flag' => '🇪🇹',
            'charset' => 'UTF-8'
        ]
    ];

    public const DEFAULT_LANGUAGE = 'en';
    public const FALLBACK_LANGUAGE = 'en';

    private static $instance = null;
    private $currentLanguage;
    private $translations = [];
    private $loadedModules = [];
    private $basePath;
    private $cacheEnabled;
    private $cacheLifetime = 3600; // 1 hour

    /**
     * Get singleton instance
     * 
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->basePath = dirname(__DIR__, 2) . '/lang/';
        $this->cacheEnabled = !empty($_ENV['LANGUAGE_CACHE_ENABLED']);
        $this->initializeLanguage();
    }

    /**
     * Initialize language settings
     */
    private function initializeLanguage(): void
    {
        // Detect language from various sources
        $language = $this->detectLanguage();
        $this->setLanguage($language);
    }

    /**
     * Detect language from various sources
     * 
     * @return string Language code
     */
    private function detectLanguage(): string
    {
        // 1. Check URL parameter
        if (isset($_GET['lang']) && $this->isValidLanguage($_GET['lang'])) {
            $this->setUserLanguagePreference($_GET['lang']);
            return $_GET['lang'];
        }

        // 2. Check session
        if (isset($_SESSION['language']) && $this->isValidLanguage($_SESSION['language'])) {
            return $_SESSION['language'];
        }

        // 3. Check user preference from database
        if (isset($_SESSION['user_id'])) {
            $userLanguage = $this->getUserLanguagePreference($_SESSION['user_id']);
            if ($userLanguage && $this->isValidLanguage($userLanguage)) {
                return $userLanguage;
            }
        }

        // 4. Check cookie
        if (isset($_COOKIE['language']) && $this->isValidLanguage($_COOKIE['language'])) {
            return $_COOKIE['language'];
        }

        // 5. Check Accept-Language header
        $browserLanguage = $this->detectBrowserLanguage();
        if ($browserLanguage) {
            return $browserLanguage;
        }

        // 6. Return default language
        return self::DEFAULT_LANGUAGE;
    }

    /**
     * Detect language from browser Accept-Language header
     * 
     * @return string|null Language code or null if not found
     */
    private function detectBrowserLanguage(): ?string
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return null;
        }

        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $languages = [];

        // Parse Accept-Language header
        if (preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)\s*(?:;\s*q\s*=\s*(1(?:\.0{1,3})?|0(?:\.[0-9]{1,3})?)?)?/i', $acceptLanguage, $matches)) {
            $priorities = $matches[2];
            $languageCodes = $matches[1];

            foreach ($languageCodes as $index => $code) {
                $priority = isset($priorities[$index]) && $priorities[$index] !== '' ? (float) $priorities[$index] : 1.0;
                $languages[$code] = $priority;
            }

            // Sort by priority
            arsort($languages);

            // Find first supported language
            foreach (array_keys($languages) as $code) {
                $shortCode = substr($code, 0, 2);
                if ($this->isValidLanguage($shortCode)) {
                    return $shortCode;
                }
            }
        }

        return null;
    }

    /**
     * Set current language
     * 
     * @param string $language Language code
     * @throws InvalidArgumentException
     */
    public function setLanguage(string $language): void
    {
        if (!$this->isValidLanguage($language)) {
            throw new InvalidArgumentException("Unsupported language: {$language}");
        }

        $this->currentLanguage = $language;
        $_SESSION['language'] = $language;

        // Set cookie for 30 days
        setcookie('language', $language, time() + (30 * 24 * 60 * 60), '/', '', false, true);

        // Set locale
        $this->setLocale($language);

        // Clear loaded translations to force reload
        $this->translations = [];
        $this->loadedModules = [];
    }

    /**
     * Get current language
     * 
     * @return string Current language code
     */
    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage ?: self::DEFAULT_LANGUAGE;
    }

    /**
     * Get current language information
     * 
     * @return array Language information
     */
    public function getCurrentLanguageInfo(): array
    {
        return self::SUPPORTED_LANGUAGES[$this->getCurrentLanguage()];
    }

    /**
     * Check if language is valid
     * 
     * @param string $language Language code
     * @return bool
     */
    public function isValidLanguage(string $language): bool
    {
        return isset(self::SUPPORTED_LANGUAGES[$language]);
    }

    /**
     * Get all supported languages
     * 
     * @return array Supported languages
     */
    public function getSupportedLanguages(): array
    {
        return self::SUPPORTED_LANGUAGES;
    }

    /**
     * Set system locale
     * 
     * @param string $language Language code
     */
    private function setLocale(string $language): void
    {
        $languageInfo = self::SUPPORTED_LANGUAGES[$language];
        $locale = $languageInfo['locale'];

        // Try different locale formats
        $locales = [
            $locale . '.UTF-8',
            $locale . '.utf8',
            $locale,
            $language . '_' . strtoupper($language) . '.UTF-8',
            $language . '_' . strtoupper($language),
            $language
        ];

        foreach ($locales as $loc) {
            if (setlocale(LC_ALL, $loc)) {
                break;
            }
        }

        // Set text domain for gettext (if using gettext)
        bindtextdomain('messages', $this->basePath);
        textdomain('messages');
    }

    /**
     * Load translation module
     * 
     * @param string $module Module name (e.g., 'common', 'auth', 'dashboard')
     * @param string $language Language code (optional, uses current if not specified)
     * @return array Loaded translations
     */
    public function loadModule(string $module, ?string $language = null): array
    {
        $language = $language ?: $this->getCurrentLanguage();
        $cacheKey = "{$language}.{$module}";

        // Check if already loaded
        if (isset($this->loadedModules[$cacheKey])) {
            return $this->translations[$cacheKey] ?? [];
        }

        // Check cache
        if ($this->cacheEnabled) {
            $cached = $this->getCachedTranslations($cacheKey);
            if ($cached !== null) {
                $this->translations[$cacheKey] = $cached;
                $this->loadedModules[$cacheKey] = true;
                return $cached;
            }
        }

        // Load from file
        $translations = $this->loadTranslationsFromFile($module, $language);
        
        // Load fallback if primary language fails or is incomplete
        if (empty($translations) && $language !== self::FALLBACK_LANGUAGE) {
            $fallbackTranslations = $this->loadTranslationsFromFile($module, self::FALLBACK_LANGUAGE);
            $translations = array_merge($fallbackTranslations, $translations);
        }

        $this->translations[$cacheKey] = $translations;
        $this->loadedModules[$cacheKey] = true;

        // Cache translations
        if ($this->cacheEnabled && !empty($translations)) {
            $this->cacheTranslations($cacheKey, $translations);
        }

        return $translations;
    }

    /**
     * Load translations from file
     * 
     * @param string $module Module name
     * @param string $language Language code
     * @return array Translations
     */
    private function loadTranslationsFromFile(string $module, string $language): array
    {
        $filePath = $this->basePath . $language . '/' . $module . '.php';
        
        if (!file_exists($filePath)) {
            return [];
        }

        try {
            $translations = include $filePath;
            return is_array($translations) ? $translations : [];
        } catch (Exception $e) {
            error_log("Error loading translations from {$filePath}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Translate text
     * 
     * @param string $key Translation key
     * @param array $replacements Variable replacements
     * @param string|null $module Module name (auto-detected if null)
     * @param string|null $language Language code (current if null)
     * @return string Translated text
     */
    public function translate(string $key, array $replacements = [], ?string $module = null, ?string $language = null): string
    {
        $language = $language ?: $this->getCurrentLanguage();
        
        // Auto-detect module from key if not provided
        if ($module === null) {
            $keyParts = explode('.', $key, 2);
            if (count($keyParts) === 2) {
                $module = $keyParts[0];
                $key = $keyParts[1];
            } else {
                $module = 'common';
            }
        }

        // Load module if not loaded
        $this->loadModule($module, $language);
        
        $cacheKey = "{$language}.{$module}";
        $translations = $this->translations[$cacheKey] ?? [];

        // Get translation
        $translation = $this->getNestedTranslation($translations, $key);

        // Fallback to key if translation not found
        if ($translation === null) {
            $translation = $key;
            
            // Log missing translation
            $this->logMissingTranslation($key, $module, $language);
        }

        // Apply replacements
        return $this->applyReplacements($translation, $replacements);
    }

    /**
     * Get nested translation value
     * 
     * @param array $translations Translations array
     * @param string $key Dot-notation key
     * @return string|null Translation or null if not found
     */
    private function getNestedTranslation(array $translations, string $key): ?string
    {
        $keys = explode('.', $key);
        $value = $translations;

        foreach ($keys as $k) {
            if (!is_array($value) || !isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }

        return is_string($value) ? $value : null;
    }

    /**
     * Apply variable replacements to translation
     * 
     * @param string $translation Translation text
     * @param array $replacements Replacement variables
     * @return string Processed translation
     */
    private function applyReplacements(string $translation, array $replacements): string
    {
        if (empty($replacements)) {
            return $translation;
        }

        // Replace :variable format
        foreach ($replacements as $key => $value) {
            $translation = str_replace(":{$key}", $value, $translation);
        }

        // Replace {variable} format
        foreach ($replacements as $key => $value) {
            $translation = str_replace("{{$key}}", $value, $translation);
        }

        return $translation;
    }

    /**
     * Pluralize translation
     * 
     * @param string $key Translation key
     * @param int $count Count for pluralization
     * @param array $replacements Variable replacements
     * @param string|null $module Module name
     * @param string|null $language Language code
     * @return string Pluralized translation
     */
    public function pluralize(string $key, int $count, array $replacements = [], ?string $module = null, ?string $language = null): string
    {
        $language = $language ?: $this->getCurrentLanguage();
        
        // Add count to replacements
        $replacements['count'] = $count;

        // Determine plural form based on language rules
        $pluralForm = $this->getPluralForm($count, $language);
        
        // Try to get specific plural translation
        $pluralKey = $key . '.' . $pluralForm;
        $translation = $this->translate($pluralKey, $replacements, $module, $language);
        
        // If specific plural not found, try base key
        if ($translation === $pluralKey) {
            $translation = $this->translate($key, $replacements, $module, $language);
        }

        return $translation;
    }

    /**
     * Get plural form for count and language
     * 
     * @param int $count Count
     * @param string $language Language code
     * @return string Plural form ('zero', 'one', 'few', 'many', 'other')
     */
    private function getPluralForm(int $count, string $language): string
    {
        // English pluralization rules
        if ($language === 'en') {
            if ($count === 0) return 'zero';
            if ($count === 1) return 'one';
            return 'other';
        }

        // Oromo pluralization rules (simplified)
        if ($language === 'om') {
            if ($count === 0) return 'zero';
            if ($count === 1) return 'one';
            return 'other';
        }

        // Default
        return $count === 1 ? 'one' : 'other';
    }

    /**
     * Format date according to current locale
     * 
     * @param string|\DateTime $date Date to format
     * @param string $format Format string
     * @return string Formatted date
     */
    public function formatDate($date, string $format = 'medium'): string
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        }

        $language = $this->getCurrentLanguage();
        $languageInfo = self::SUPPORTED_LANGUAGES[$language];

        // Format based on language and locale
        $formats = [
            'en' => [
                'short' => 'M j, Y',
                'medium' => 'M j, Y g:i A',
                'long' => 'F j, Y g:i:s A',
                'full' => 'l, F j, Y g:i:s A T'
            ],
            'om' => [
                'short' => 'j/n/Y',
                'medium' => 'j/n/Y H:i',
                'long' => 'j F Y H:i:s',
                'full' => 'l, j F Y H:i:s T'
            ]
        ];

        $formatString = $formats[$language][$format] ?? $formats['en'][$format] ?? $format;
        
        return $date->format($formatString);
    }

    /**
     * Format number according to current locale
     * 
     * @param float $number Number to format
     * @param int $decimals Number of decimal places
     * @return string Formatted number
     */
    public function formatNumber(float $number, int $decimals = 2): string
    {
        $language = $this->getCurrentLanguage();
        
        // Number formatting by language
        $separators = [
            'en' => ['decimal' => '.', 'thousands' => ','],
            'om' => ['decimal' => '.', 'thousands' => ',']
        ];

        $sep = $separators[$language] ?? $separators['en'];
        
        return number_format($number, $decimals, $sep['decimal'], $sep['thousands']);
    }

    /**
     * Format currency according to current locale
     * 
     * @param float $amount Amount to format
     * @param string $currency Currency code
     * @return string Formatted currency
     */
    public function formatCurrency(float $amount, string $currency = 'USD'): string
    {
        $language = $this->getCurrentLanguage();
        $formattedNumber = $this->formatNumber($amount, 2);
        
        // Currency formatting by language
        $currencyFormats = [
            'en' => [
                'USD' => '$:amount',
                'EUR' => '€:amount',
                'ETB' => ':amount Birr'
            ],
            'om' => [
                'USD' => '$:amount',
                'EUR' => '€:amount',
                'ETB' => ':amount Birr'
            ]
        ];

        $format = $currencyFormats[$language][$currency] ?? $currencyFormats['en'][$currency] ?? ':amount ' . $currency;
        
        return str_replace(':amount', $formattedNumber, $format);
    }

    /**
     * Get user language preference from database
     * 
     * @param int $userId User ID
     * @return string|null Language code or null
     */
    private function getUserLanguagePreference(int $userId): ?string
    {
        try {
            $database = \App\Utils\Database::getInstance();
            $sql = "SELECT language_preference FROM users WHERE id = ?";
            $stmt = $database->prepare($sql);
            $stmt->execute([$userId]);
            
            return $stmt->fetchColumn() ?: null;
        } catch (Exception $e) {
            error_log("Error getting user language preference: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Set user language preference in database
     * 
     * @param string $language Language code
     * @param int|null $userId User ID (current user if null)
     * @return bool Success status
     */
    public function setUserLanguagePreference(string $language, ?int $userId = null): bool
    {
        if (!$this->isValidLanguage($language)) {
            return false;
        }

        $userId = $userId ?: ($_SESSION['user_id'] ?? null);
        if (!$userId) {
            return false;
        }

        try {
            $database = \App\Utils\Database::getInstance();
            $sql = "UPDATE users SET language_preference = ? WHERE id = ?";
            $stmt = $database->prepare($sql);
            return $stmt->execute([$language, $userId]);
        } catch (Exception $e) {
            error_log("Error setting user language preference: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cached translations
     * 
     * @param string $cacheKey Cache key
     * @return array|null Cached translations or null
     */
    private function getCachedTranslations(string $cacheKey): ?array
    {
        $cacheFile = $this->getCacheFilePath($cacheKey);
        
        if (!file_exists($cacheFile)) {
            return null;
        }

        if (time() - filemtime($cacheFile) > $this->cacheLifetime) {
            unlink($cacheFile);
            return null;
        }

        $cached = file_get_contents($cacheFile);
        return $cached ? json_decode($cached, true) : null;
    }

    /**
     * Cache translations
     * 
     * @param string $cacheKey Cache key
     * @param array $translations Translations to cache
     * @return bool Success status
     */
    private function cacheTranslations(string $cacheKey, array $translations): bool
    {
        $cacheFile = $this->getCacheFilePath($cacheKey);
        $cacheDir = dirname($cacheFile);
        
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        return file_put_contents($cacheFile, json_encode($translations)) !== false;
    }

    /**
     * Get cache file path
     * 
     * @param string $cacheKey Cache key
     * @return string Cache file path
     */
    private function getCacheFilePath(string $cacheKey): string
    {
        $cacheDir = dirname(__DIR__, 2) . '/storage/cache/translations/';
        return $cacheDir . $cacheKey . '.json';
    }

    /**
     * Log missing translation
     * 
     * @param string $key Translation key
     * @param string $module Module name
     * @param string $language Language code
     * @return void
     */
    private function logMissingTranslation(string $key, string $module, string $language): void
    {
        $logEntry = [
            'key' => $key,
            'module' => $module,
            'language' => $language,
            'timestamp' => date('Y-m-d H:i:s'),
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'user_id' => $_SESSION['user_id'] ?? null
        ];

        error_log("Missing translation: " . json_encode($logEntry));
    }

    /**
     * Clear translation cache
     * 
     * @param string|null $language Specific language or all if null
     * @return bool Success status
     */
    public function clearCache(?string $language = null): bool
    {
        $cacheDir = dirname(__DIR__, 2) . '/storage/cache/translations/';
        
        if (!is_dir($cacheDir)) {
            return true;
        }

        $pattern = $language ? "{$language}.*.json" : "*.json";
        $files = glob($cacheDir . $pattern);
        
        foreach ($files as $file) {
            unlink($file);
        }

        return true;
    }

    /**
     * Get language statistics
     * 
     * @return array Language usage statistics
     */
    public function getStatistics(): array
    {
        try {
            $database = \App\Utils\Database::getInstance();
            
            // User language preferences
            $sql = "SELECT language_preference, COUNT(*) as count FROM users WHERE language_preference IS NOT NULL GROUP BY language_preference";
            $stmt = $database->prepare($sql);
            $stmt->execute();
            $userPreferences = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
            
            // Current session language
            $currentLanguage = $this->getCurrentLanguage();
            
            return [
                'supported_languages' => self::SUPPORTED_LANGUAGES,
                'default_language' => self::DEFAULT_LANGUAGE,
                'current_language' => $currentLanguage,
                'user_preferences' => $userPreferences,
                'loaded_modules' => array_keys($this->loadedModules),
                'cache_enabled' => $this->cacheEnabled
            ];
        } catch (Exception $e) {
            error_log("Error getting language statistics: " . $e->getMessage());
            return [
                'supported_languages' => self::SUPPORTED_LANGUAGES,
                'current_language' => $this->getCurrentLanguage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Magic method for short translation syntax
     * 
     * @param string $key Translation key
     * @param array $replacements Variable replacements
     * @return string Translated text
     */
    public function __invoke(string $key, array $replacements = []): string
    {
        return $this->translate($key, $replacements);
    }
}

// Global helper functions for easy access
if (!function_exists('__')) {
    /**
     * Translate text (shorthand function)
     * 
     * @param string $key Translation key
     * @param array $replacements Variable replacements
     * @param string|null $module Module name
     * @return string Translated text
     */
    function __(string $key, array $replacements = [], ?string $module = null): string
    {
        return \App\Services\LanguageService::getInstance()->translate($key, $replacements, $module);
    }
}

if (!function_exists('__n')) {
    /**
     * Plural translation (shorthand function)
     * 
     * @param string $key Translation key
     * @param int $count Count for pluralization
     * @param array $replacements Variable replacements
     * @param string|null $module Module name
     * @return string Pluralized translation
     */
    function __n(string $key, int $count, array $replacements = [], ?string $module = null): string
    {
        return \App\Services\LanguageService::getInstance()->pluralize($key, $count, $replacements, $module);
    }
}

if (!function_exists('lang')) {
    /**
     * Get LanguageService instance
     * 
     * @return \App\Services\LanguageService
     */
    function lang(): \App\Services\LanguageService
    {
        return \App\Services\LanguageService::getInstance();
    }
}