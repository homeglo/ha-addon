<?php

namespace app\rbac;

use yii\rbac\Item;
use yii\rbac\Rule;
use app\models\HgHome;

/**
 * Checks if authorID matches user passed via params
 */
class HgHomeRule extends Rule
{
    public $name = 'isHomeOwner';

    /**
     * @param string|int $user the user ID.
     * @param Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return bool a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        if (isset($params['hgHome'])) {
            foreach ($params['hgHome']->hgHomeUsers as $hgHomeUser) {
                if ($hgHomeUser->user_id == $user)
                    return true;
            }
        }

        return false;
    }
}

