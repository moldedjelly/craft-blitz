<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\blitztests\fixtures;

use Craft;
use craft\helpers\FileHelper;
use craft\helpers\Json;
use craft\services\Fs;
use craft\services\ProjectConfig;
use yii\base\ErrorException;
use yii\test\ArrayFixture;

class FsFixture extends ArrayFixture
{
    public const BASE_URL = 'https://test.craftcms.test/';

    /**
     * @inheritdoc
     */
    public $dataFile = __DIR__ . '/data/fs.php';

    private ?array $_originalConfig = null;
    private Fs $_originalService;

    /**
     * @inheritdoc
     */
    public function load(): void
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $this->_originalConfig = $projectConfig->get(ProjectConfig::PATH_FS);
        $this->_originalService = Craft::$app->getFs();

        $projectConfig->set(ProjectConfig::PATH_FS, $this->getData());
        Craft::$app->set('fs', new Fs());
    }

    /**
     * @inheritdoc
     * @throws ErrorException
     */
    public function unload(): void
    {
        // Remove the dirs
        foreach ($this->getData() as $data) {
            $settings = Json::decodeIfJson($data['settings']);
            FileHelper::removeDirectory($settings['path']);
        }

        Craft::$app->getProjectConfig()->set(ProjectConfig::PATH_FS, $this->_originalConfig);
        if (isset($this->_originalService)) {
            Craft::$app->set('fs', $this->_originalService);
        }

        parent::unload();
    }
}
