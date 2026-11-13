<?php

use Illuminate\Support\Facades\Cache;
use Stichoza\GoogleTranslate\Exceptions\LargeTextException;
use Stichoza\GoogleTranslate\Exceptions\RateLimitException;
use Stichoza\GoogleTranslate\Exceptions\TranslationRequestException;
use Stichoza\GoogleTranslate\GoogleTranslate;

if (! function_exists('autoGoogleTranslator')) {
    /**
     * Translate content using Google Translate with caching and retry logic.
     *
     * @param string $targetLang Target language code (e.g., 'ar', 'en', 'fr')
     * @param string $content The text to translate
     * @param int $maxRetries Maximum number of retry attempts on rate limit errors
     * @param int $cacheTtl Cache time-to-live in minutes
     * @return string|null The translated text, or original content if translation fails
     */
    function autoGoogleTranslator(string $targetLang, string $content, int $maxRetries = 3, int $cacheTtl = 60)
    {
        // Skip translation if content is empty or already in target language
        if (trim($content) === '') {
            return $content;
        }

        // Check cache first to avoid unnecessary API calls
        $cacheKey = 'translate:'.$targetLang.':'.md5($content);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $translator = new GoogleTranslate;
        $translator->setTarget($targetLang);

        $attempt = 0;
        $delay = 1; // Start with 1 second delay

        while ($attempt <= $maxRetries) {
            try {
                $result = $translator->translate($content);

                if ($result !== null) {
                    // Cache the successful result
                    Cache::put($cacheKey, $result, now()->addMinutes($cacheTtl));
                    return $result;
                }

                // Translation returned null — return original content
                return $content;

            } catch (RateLimitException $e) {
                $attempt++;

                if ($attempt > $maxRetries) {
                    // Exhausted all retries — return original content as fallback
                    return $content;
                }

                // Exponential backoff with jitter
                $jitter = random_int(0, (int) ($delay * 1000)) / 1000.0;
                usleep((int) (($delay + $jitter) * 1000000));
                $delay *= 2; // Double the delay for next attempt

            } catch (LargeTextException $e) {
                // Text is too large — return original content
                return $content;

            } catch (TranslationRequestException $e) {
                // Other HTTP errors — return original content
                return $content;
            }
        }

        // Fallback: return original content if all attempts failed
        return $content;
    }
}

if (! function_exists('otherLangs')) {
    function otherLangs()
    {
        $locale = app()->getLocale();

        return array_keys(array_filter(
            config('laravellocalization.supportedLocales'),
            function ($value, $key) use ($locale) {
                return $key != $locale;
            },
            ARRAY_FILTER_USE_BOTH
        ));
    }

}
