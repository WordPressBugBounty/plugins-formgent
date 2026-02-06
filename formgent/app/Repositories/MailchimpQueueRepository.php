<?php

namespace FormGent\App\Repositories;


defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\Repositories\Repository;
use FormGent\WpMVC\Database\Query\Builder;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\App\Models\MailchimpQueue;

class MailchimpQueueRepository extends Repository {
    public function get_query_builder(): Builder {
        return MailchimpQueue::query();
    }
}