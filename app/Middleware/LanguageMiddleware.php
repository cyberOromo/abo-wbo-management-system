<?php

namespace App\Middleware;

use Exception;

class LanguageMiddleware
{
    const DEFAULT_LANGUAGE = 'en';
    const SUPPORTED_LANGUAGES = ['en', 'om']; // English, Afaan Oromoo
    const COOKIE_NAME = 'app_language';
    const COOKIE_LIFETIME = 2592000; // 30 days
    const SESSION_KEY = 'language';

    private $languageService;
    private $translations = [];
    private $currentLanguage;

    public function __construct()
    {
        $this->languageService = new \App\Services\LanguageService();
        $this->currentLanguage = self::DEFAULT_LANGUAGE;
    }

    /**
     * Handle language detection and switching
     */
    public function handle($request, $next)
    {
        try {
            // Detect and set current language
            $this->detectLanguage($request);
            
            // Load language translations
            $this->loadTranslations();
            
            // Set language context for the application
            $this->setLanguageContext($request);
            
            // Add language helpers to request
            $request->setLanguage($this->currentLanguage);
            $request->setTranslations($this->translations);
            
            return $next($request);
            
        } catch (Exception $e) {
            error_log("Language Middleware error: " . $e->getMessage());
            // Continue with default language on error
            $this->currentLanguage = self::DEFAULT_LANGUAGE;
            return $next($request);
        }
    }

    /**
     * Detect language from various sources
     */
    private function detectLanguage($request): void
    {
        $detectedLanguage = null;
        
        // 1. Check URL parameter (highest priority)
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['lang']) && $this->isValidLanguage($queryParams['lang'])) {
            $detectedLanguage = $queryParams['lang'];
            $this->setLanguagePreference($detectedLanguage);
        }
        
        // 2. Check route parameter
        if (!$detectedLanguage) {
            $routeParams = $request->getRouteParams();
            if (isset($routeParams['lang']) && $this->isValidLanguage($routeParams['lang'])) {
                $detectedLanguage = $routeParams['lang'];
                $this->setLanguagePreference($detectedLanguage);
            }
        }
        
        // 3. Check user's saved preference (database)
        if (!$detectedLanguage) {
            $detectedLanguage = $this->getUserLanguagePreference($request);
        }
        
        // 4. Check session
        if (!$detectedLanguage) {
            session_start();
            if (isset($_SESSION[self::SESSION_KEY]) && $this->isValidLanguage($_SESSION[self::SESSION_KEY])) {
                $detectedLanguage = $_SESSION[self::SESSION_KEY];
            }
        }
        
        // 5. Check cookie
        if (!$detectedLanguage) {
            if (isset($_COOKIE[self::COOKIE_NAME]) && $this->isValidLanguage($_COOKIE[self::COOKIE_NAME])) {
                $detectedLanguage = $_COOKIE[self::COOKIE_NAME];
            }
        }
        
        // 6. Check Accept-Language header
        if (!$detectedLanguage) {
            $detectedLanguage = $this->detectFromAcceptLanguage($request);
        }
        
        // 7. Use default language
        $this->currentLanguage = $detectedLanguage ?: self::DEFAULT_LANGUAGE;
    }

    /**
     * Check if language code is valid
     */
    private function isValidLanguage(string $language): bool
    {
        return in_array($language, self::SUPPORTED_LANGUAGES);
    }

    /**
     * Get user's saved language preference
     */
    private function getUserLanguagePreference($request): ?string
    {
        try {
            $user = $this->getCurrentUser($request);
            
            if ($user && isset($user['language_preference'])) {
                return $this->isValidLanguage($user['language_preference']) 
                    ? $user['language_preference'] 
                    : null;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error getting user language preference: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Detect language from Accept-Language header
     */
    private function detectFromAcceptLanguage($request): ?string
    {
        $acceptLanguage = $request->getHeader('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }
        
        // Parse Accept-Language header
        $languages = $this->parseAcceptLanguage($acceptLanguage);
        
        // Find first supported language
        foreach ($languages as $language) {
            $langCode = substr($language, 0, 2); // Get primary language code
            if ($this->isValidLanguage($langCode)) {
                return $langCode;
            }
        }
        
        return null;
    }

    /**
     * Parse Accept-Language header
     */
    private function parseAcceptLanguage(string $acceptLanguage): array
    {
        $languages = [];
        $items = explode(',', $acceptLanguage);
        
        foreach ($items as $item) {
            $parts = explode(';', trim($item));
            $language = trim($parts[0]);
            $quality = 1.0;
            
            // Parse quality value if present
            if (isset($parts[1]) && strpos($parts[1], 'q=') === 0) {
                $quality = floatval(substr($parts[1], 2));
            }
            
            $languages[] = [
                'language' => $language,
                'quality' => $quality
            ];
        }
        
        // Sort by quality (highest first)
        usort($languages, function($a, $b) {
            return $b['quality'] <=> $a['quality'];
        });
        
        return array_column($languages, 'language');
    }

    /**
     * Load translations for current language
     */
    private function loadTranslations(): void
    {
        try {
            $this->translations = $this->languageService->getTranslations($this->currentLanguage);
            
            // Load additional translation files
            $this->loadTranslationFiles();
            
        } catch (Exception $e) {
            error_log("Error loading translations: " . $e->getMessage());
            $this->translations = [];
        }
    }

    /**
     * Load translation files from disk
     */
    private function loadTranslationFiles(): void
    {
        $langPath = __DIR__ . "/../../lang/{$this->currentLanguage}";
        
        if (!is_dir($langPath)) {
            return;
        }
        
        $translationFiles = [
            'common.php',
            'auth.php',
            'dashboard.php',
            'tasks.php',
            'meetings.php',
            'donations.php',
            'events.php',
            'courses.php',
            'reports.php',
            'users.php',
            'validation.php',
            'errors.php'
        ];
        
        foreach ($translationFiles as $file) {
            $filePath = $langPath . '/' . $file;
            
            if (file_exists($filePath)) {
                $translations = include $filePath;
                if (is_array($translations)) {
                    $category = pathinfo($file, PATHINFO_FILENAME);
                    $this->translations[$category] = array_merge(
                        $this->translations[$category] ?? [],
                        $translations
                    );
                }
            }
        }
    }

    /**
     * Set language context for the application
     */
    private function setLanguageContext($request): void
    {
        // Set global language constants
        if (!defined('APP_LANGUAGE')) {
            define('APP_LANGUAGE', $this->currentLanguage);
        }
        
        // Set locale for date/time formatting
        $this->setLocale();
        
        // Store in session
        session_start();
        $_SESSION[self::SESSION_KEY] = $this->currentLanguage;
        
        // Set response headers
        header('Content-Language: ' . $this->currentLanguage);
        
        // Add to global variables for templates
        $GLOBALS['current_language'] = $this->currentLanguage;
        $GLOBALS['translations'] = $this->translations;
    }

    /**
     * Set system locale
     */
    private function setLocale(): void
    {
        $locales = [
            'en' => ['en_US.UTF-8', 'en_US', 'English_United States'],
            'om' => ['om_ET.UTF-8', 'om_ET', 'Oromo_Ethiopia']
        ];
        
        $languageLocales = $locales[$this->currentLanguage] ?? $locales['en'];
        
        foreach ($languageLocales as $locale) {
            if (setlocale(LC_ALL, $locale)) {
                break;
            }
        }
        
        // Set timezone (can be user-specific)
        date_default_timezone_set('UTC');
    }

    /**
     * Set language preference (save to cookie and user profile)
     */
    private function setLanguagePreference(string $language): void
    {
        if (!$this->isValidLanguage($language)) {
            return;
        }
        
        // Set cookie
        setcookie(
            self::COOKIE_NAME,
            $language,
            time() + self::COOKIE_LIFETIME,
            '/',
            '',
            isset($_SERVER['HTTPS']),
            true // HttpOnly
        );
        
        // Update user preference in database
        $this->updateUserLanguagePreference($language);
    }

    /**
     * Update user's language preference in database
     */
    private function updateUserLanguagePreference(string $language): void
    {
        try {
            session_start();
            
            if (!isset($_SESSION['user_id'])) {
                return;
            }
            
            $userId = $_SESSION['user_id'];
            $userService = new \App\Services\UserService();
            $userService->updateUserLanguage($userId, $language);
            
            // Update session cache
            if (isset($_SESSION['user_data'])) {
                $_SESSION['user_data']['language_preference'] = $language;
            }
            
        } catch (Exception $e) {
            error_log("Error updating user language preference: " . $e->getMessage());
        }
    }

    /**
     * Get current user from request context
     */
    private function getCurrentUser($request): ?array
    {
        // Try to get user from request (set by auth middleware)
        $user = $request->getUser();
        if ($user) {
            return $user;
        }
        
        // Fallback to session
        session_start();
        if (isset($_SESSION['user_id'])) {
            try {
                $userService = new \App\Services\UserService();
                return $userService->getUserProfile($_SESSION['user_id']);
            } catch (Exception $e) {
                error_log("Error getting current user: " . $e->getMessage());
            }
        }
        
        return null;
    }

    /**
     * Get current language
     */
    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    /**
     * Get translations
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    /**
     * Get translation for key
     */
    public function translate(string $key, array $parameters = []): string
    {
        $translation = $this->getTranslationValue($key);
        
        if ($translation === null) {
            return $key; // Return key if translation not found
        }
        
        // Replace parameters
        foreach ($parameters as $param => $value) {
            $translation = str_replace(':' . $param, $value, $translation);
        }
        
        return $translation;
    }

    /**
     * Get translation value by key (supports dot notation)
     */
    private function getTranslationValue(string $key): ?string
    {
        $keys = explode('.', $key);
        $value = $this->translations;
        
        foreach ($keys as $k) {
            if (!is_array($value) || !isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }
        
        return is_string($value) ? $value : null;
    }

    /**
     * Switch language
     */
    public function switchLanguage(string $language): bool
    {
        if (!$this->isValidLanguage($language)) {
            return false;
        }
        
        $this->currentLanguage = $language;
        $this->setLanguagePreference($language);
        $this->loadTranslations();
        
        return true;
    }

    /**
     * Get supported languages
     */
    public function getSupportedLanguages(): array
    {
        return [
            'en' => [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'flag' => '🇺🇸',
                'rtl' => false
            ],
            'om' => [
                'code' => 'om',
                'name' => 'Oromo',
                'native_name' => 'Afaan Oromoo',
                'flag' => '🇪🇹',
                'rtl' => false
            ]
        ];
    }

    /**
     * Get language info
     */
    public function getLanguageInfo(string $language = null): ?array
    {
        $language = $language ?: $this->currentLanguage;
        $languages = $this->getSupportedLanguages();
        
        return $languages[$language] ?? null;
    }

    /**
     * Check if language is RTL
     */
    public function isRtl(string $language = null): bool
    {
        $language = $language ?: $this->currentLanguage;
        $info = $this->getLanguageInfo($language);
        
        return $info['rtl'] ?? false;
    }

    /**
     * Generate language switcher URLs
     */
    public function getLanguageSwitcherUrls($request): array
    {
        $currentUrl = $request->getUri();
        $urls = [];
        
        foreach (self::SUPPORTED_LANGUAGES as $lang) {
            $url = $this->addLanguageToUrl($currentUrl, $lang);
            $urls[$lang] = $url;
        }
        
        return $urls;
    }

    /**
     * Add language parameter to URL
     */
    private function addLanguageToUrl(string $url, string $language): string
    {
        $parsed = parse_url($url);
        $query = [];
        
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query);
        }
        
        $query['lang'] = $language;
        
        $newUrl = $parsed['scheme'] . '://' . $parsed['host'];
        
        if (isset($parsed['port'])) {
            $newUrl .= ':' . $parsed['port'];
        }
        
        if (isset($parsed['path'])) {
            $newUrl .= $parsed['path'];
        }
        
        $newUrl .= '?' . http_build_query($query);
        
        if (isset($parsed['fragment'])) {
            $newUrl .= '#' . $parsed['fragment'];
        }
        
        return $newUrl;
    }

    /**
     * Format date according to current language
     */
    public function formatDate($date, string $format = null): string
    {
        if (!$date) {
            return '';
        }
        
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        
        if (!$timestamp) {
            return '';
        }
        
        // Use language-specific date formats
        $formats = [
            'en' => $format ?: 'F j, Y',
            'om' => $format ?: 'j F Y' // Adjust for Oromo preferences
        ];
        
        $dateFormat = $formats[$this->currentLanguage] ?? $formats['en'];
        
        return date($dateFormat, $timestamp);
    }

    /**
     * Format number according to current language
     */
    public function formatNumber($number, int $decimals = 0): string
    {
        $formats = [
            'en' => ['decimal_point' => '.', 'thousands_sep' => ','],
            'om' => ['decimal_point' => '.', 'thousands_sep' => ','] // Adjust if needed
        ];
        
        $format = $formats[$this->currentLanguage] ?? $formats['en'];
        
        return number_format(
            $number,
            $decimals,
            $format['decimal_point'],
            $format['thousands_sep']
        );
    }

    /**
     * Get direction for CSS (ltr/rtl)
     */
    public function getDirection(): string
    {
        return $this->isRtl() ? 'rtl' : 'ltr';
    }

    /**
     * Get language-specific font stack
     */
    public function getFontStack(): string
    {
        $fonts = [
            'en' => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            'om' => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
        ];
        
        return $fonts[$this->currentLanguage] ?? $fonts['en'];
    }

    /**
     * Create language middleware instance
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Middleware factory with custom supported languages
     */
    public static function withLanguages(array $supportedLanguages)
    {
        return function ($request, $next) use ($supportedLanguages) {
            $middleware = new self();
            // Custom implementation would override supported languages
            return $middleware->handle($request, $next);
        };
    }
}