<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

return array(
    'di' => array(
        'instance' => array(
            'cron' => array(
                'parameters' => array(
                    /**
                     * how long ahead to schedule cron jobs
                     *
                     * @var int (minute)
                     */
                    'scheduleAhead'         => 60,
                    /**
                     * how long before a scheduled job is considered missed
                     *
                     * @var int (minute)
                     */
                    'scheduleLifetime'      => 60,
                    /**
                     * maximum running time of each cron job
                     *
                     * @var int (minute)
                     */
                    'maxRunningTime'        => 60,
                    /**
                     * how long to keep successfully completed cron job logs
                     *
                     * @var int (minute)
                     */
                    'successLogLifetime'    => 10080,
                    /**
                     * how long to keep failed (missed / error) cron job logs
                     *
                     * @var int (minute)
                     */
                    'failureLogLifetime'    => 10080,
                ),
            ),
        ),
    ),
);
