<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

namespace davidxu\srbac;

use yii\base\Module;
use yii\i18n\PhpMessageSource;
use Yii;

/**
 * Class SrbacModule
 * @package davidxu\srbac
 */
class SrbacModule extends Module
{
	/**
     * @var string
     */
    public $controllerNamespace = 'davidxu\srbac\controllers';

    public function init(): void
    {
        parent::init();

        if (!isset(Yii::$app->i18n->translations['srbac'])) {
            Yii::$app->i18n->translations['srbac'] = [
                'class' => PhpMessageSource::class,
                'sourceLanguage' => 'en-US',
                'basePath' => '@davidxu/srbac/messages',
            ];
        }
    }
}
