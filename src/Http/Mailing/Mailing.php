<?php

namespace App\Http\Mailing;

use App\Http\Models\BaseModel;

class Mailing extends BaseModel
{
    const TABLE_NAME = 'mailing_user_list';

    public function setUserMailingList(int $user_id): void
    {
        $this->database->insert(self::TABLE_NAME, ['user_id' => $user_id]);
    }
}
