<?php

namespace Ecosystem\ActivityLog\Service;

class ActivityLogService
{
    public function __construct(private string $activityLog)
    {
    }

    public function log(
        string $namespace,
        string $event,
        string $id,
        string $triggerType,
        string|int $triggerId,
        string $triggerScreen,
        ?mixed $new,
        ?mixed $old
    ): void {
        $payload = [
            'timestamp' => intval(microtime(true) * 1000),
            'namespace' => $namespace,
            'event' => $event,
            'id' => $id,
            'trigger' => [
                'type' => $triggerType,
                'id' => $triggerId,
                'screen' => $triggerScreen
            ]
        ];

        if ($new !== null) {
            $payload['record'] = ['new' => $new, 'old' => $old];
        }
    }
}
