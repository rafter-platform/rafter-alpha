<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DatabaseInstance extends Model
{
    const TYPES = [
        'mysql',
        // 'postgres',
    ];

    const VERSIONS = [
        'MYSQL_5_7' => 'MySQL 5.7',
        'MYSQL_5_6' => 'MySQL 5.7',
        // 'POSTGRES_9_6' => 'Postgres 9.6',
        // 'POSTGRES_11' => 'Postgres 11 (Beta)',
    ];

    const TIERS = [
        'mysql' => [
            'db-f1-micro' => 'db-f1-micro (1vCPU, 614.4 MB) ~$8/mo',
            'db-g1-small' => 'db-g1-small (1vCPU, 1.7 GB) ~$26/mo',
            'db-n1-standard-1' => 'db-n1-standard-1 (1vCPU, 3.75 GB) ~$50/mo',
            'db-n1-standard-2' => 'db-n1-standard-2 (2vCPU, 7.5 GB) ~$100/mo',
            'db-n1-standard-4' => 'db-n1-standard-4 (4vCPU, 15 GB) ~$200/mo',
            'db-n1-standard-8' => 'db-n1-standard-8 (8vCPU, 30 GB) ~$400/mo',
            'db-n1-standard-16' => 'db-n1-standard-16 (16vCPU, 60 GB) ~$800/mo',
            'db-n1-standard-32' => 'db-n1-standard-32 (32vCPU, 120 GB) ~$1,600/mo',
            'db-n1-standard-64' => 'db-n1-standard-64 (64vCPU, 240 GB) ~$3,200/mo',
            'db-n1-highmem-2' => 'db-n1-highmem-2 (2vCPU, 13 GB) ~$130/mo',
            'db-n1-highmem-4' => 'db-n1-highmem-4 (4vCPU, 26 GB) ~$258/mo',
            'db-n1-highmem-8' => 'db-n1-highmem-8 (8vCPU, 52 GB) ~$515/mo',
            'db-n1-highmem-16' => 'db-n1-highmem-16 (16vCPU, 104 GB) ~$1,030/mo',
            'db-n1-highmem-32' => 'db-n1-highmem-32 (32vCPU, 208 GB) ~$2,056/mo',
            'db-n1-highmem-64' => 'db-n1-highmem-64 (64vCPU, 416 GB) ~$4,112/mo',
        ],
        'postgres' => [
            // TKTK
        ]
    ];
}
