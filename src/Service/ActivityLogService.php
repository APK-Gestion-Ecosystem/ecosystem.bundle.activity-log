<?php

namespace Ecosystem\ActivityLogBundle\Service;

use Aws\Sqs\SnsClient;
use Aws\Exception\AwsException;

class ActivityLogService
{
    private SnsClient $client;

    public function __construct(private string $activityLog)
    {
        $this->client = new SnsClient([
            'region' => getenv('AWS_REGION'),
            'version' => '2012-11-05',
            'credentials' => false
        ]);
    }

    public function log(
        string $namespace,
        string $event,
        string $id,
        string $triggerType,
        string|int $triggerId,
        string $triggerScreen,
        mixed $new,
        mixed $old
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

        $this->client->publish([
            'Message' => json_encode($payload),
            'TopicArn' => $this->activityLog,
        ]);
    }
}
