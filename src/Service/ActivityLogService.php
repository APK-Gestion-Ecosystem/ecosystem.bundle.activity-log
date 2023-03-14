<?php

namespace Ecosystem\ActivityLogBundle\Service;

use Aws\Sns\SnsClient;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Service\Attribute\Required;

class ActivityLogService
{
    #[Required]
    public LoggerInterface $logger;

    private SnsClient $client;

    public function __construct(private string $activityLogArn)
    {
        $config = [
            'region' => getenv('AWS_REGION'),
            'version' => '2010-03-31',
        ];

        if (getenv('LOCALSTACK')) {
            $config['credentials'] = false;
            $config['endpoint'] = 'http://localstack:4566';
        }

        $this->client = new SnsClient($config);
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

        try {
            $this->client->publish([
                'Message' => json_encode($payload),
                'TopicArn' => $this->activityLogArn,
            ]);
            $this->logger->debug(sprintf(
                'Logged activity for "%s:%s:%s".',
                $namespace,
                $event,
                $id
            ));
        } catch (\Exception $exception) {
            $this->logger->critical(sprintf(
                'Unable to send activity log. Exception: "%s".',
                $exception->getMessage()
            ));
        }
    }
}
