<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\blitz\helpers;

use Craft;
use craft\base\SavableComponent;
use craft\helpers\Component;
use putyourlightson\blitz\Blitz;
use putyourlightson\blitz\jobs\DriverJob;
use putyourlightson\blitz\models\SiteUriModel;
use putyourlightson\blitz\services\RefreshCacheService;
use yii\base\NotSupportedException;

class BaseDriverHelper
{
    /**
     * Creates drivers of the provided types.
     *
     * @return SavableComponent[]
     */
    public static function createDrivers(array $types): array
    {
        $drivers = [];

        foreach ($types as $type) {
            if ($type::isSelectable()) {
                $drivers[] = self::createDriver($type);
            }
        }

        return $drivers;
    }

    /**
     * Creates a driver of the provided type with the optional settings.
     */
    public static function createDriver(string $type, array $settings = []): SavableComponent
    {
        /** @var SavableComponent $driver */
        $driver = Component::createComponent([
            'type' => $type,
            'settings' => $settings,
        ], SavableComponent::class);

        return $driver;
    }

    /**
     * Adds a driver job to the queue.
     *
     * @param SiteUriModel[] $siteUris
     */
    public static function addDriverJob(array $siteUris, string $driverId, string $driverMethod, string $description = null, int $priority = null): void
    {
        $priority = $priority ?? Blitz::$plugin->settings->driverJobPriority;

        // Convert SiteUriModels to arrays to keep the job data minimal
        foreach ($siteUris as &$siteUri) {
            if ($siteUri instanceof SiteUriModel) {
                $siteUri = $siteUri->toArray();
            }
        }

        $job = new DriverJob([
            'siteUris' => $siteUris,
            'driverId' => $driverId,
            'driverMethod' => $driverMethod,
            'description' => $description,
        ]);

        $queue = Craft::$app->getQueue();

        /**
         * @see RefreshCacheService::refresh()
         */
        try {
            $queue->priority($priority)->push($job);
        }
        /** @noinspection PhpRedundantCatchClauseInspection */
        catch (NotSupportedException) {
            $queue->push($job);
        }
    }
}
