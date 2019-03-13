<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\blitz\services;

use Craft;
use craft\base\Component;
use putyourlightson\blitz\Blitz;
use putyourlightson\blitz\models\SiteUriModel;
use putyourlightson\blitz\records\CacheTagRecord;

/**
 * @property bool $isCacheableRequest
 * @property SiteUriModel $requestedSiteUri
 */
class OutputCacheService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Outputs a given site URI if cached.
     *
     * @param SiteUriModel $siteUri
     */
    public function output(SiteUriModel $siteUri)
    {
        // Update cache control header
        header('Cache-Control: '.Blitz::$plugin->settings->cacheControlHeader);

        // Add cache tag header if set
        $tags = Blitz::$plugin->cacheTags->getSiteUriTags($siteUri);

        if (!empty($tags)) {
            header('Cache-Tag: '.Blitz::$plugin->settings->cacheControlHeader);
        }

        $value = Blitz::$plugin->cacheStorage->get($siteUri);

        if ($value) {
            $this->outputValue($value);
        }
    }

    /**
     * Outputs a given value and exits.
     *
     * @param string $value
     */
    public function outputValue(string $value)
    {
        // Update powered by header
        header_remove('X-Powered-By');

        if (Blitz::$plugin->settings->sendPoweredByHeader) {
            $header = Craft::$app->getConfig()->getGeneral()->sendPoweredByHeader ? Craft::$app->name.', ' : '';
            header('X-Powered-By: '.$header.'Blitz');
        }

        // Append served by comment
        if (Blitz::$plugin->settings->outputComments) {
            $value .= '<!-- Served by Blitz -->';
        }

        exit($value);
    }
}