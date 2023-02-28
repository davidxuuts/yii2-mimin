<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

namespace davidxu\srbac\rules;

use yii\rbac\Rule;
use Yii;

class EditOwnRule extends Rule
{
    public $name = 'editOwn';

    /**
     * @inheritDoc
     */
    public function execute($user, $item, $params): bool
    {
        // TODO: Implement execute() method.
        return true;
    }
}
