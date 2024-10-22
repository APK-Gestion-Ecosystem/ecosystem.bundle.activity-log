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

    public function __construct(
        private string $activityLogArn,
        private readonly string $id = 'system',
        private readonly string $screenName = 'system',
    ) {
        $config = [
            'region' => getenv('AWS_REGION'),
            'version' => 'latest',
        ];

        if (getenv('LOCALSTACK')) {
            $config['endpoint'] = 'http://localstack:4566';
            $config['credentials'] = ['key' => 'key', 'secret' => 'secret'];
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
        mixed $old,
        ?string $message = null
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

        if ($message !== null) {
            $payload['message'] = $message;
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

    public function logMessage(
        string $namespace,
        string $event,
        string $id,
        string $message
    ): void {
        $this->log($namespace, $event, $id, 'system', $this->id, $this->screenName, null, null, $message);
    }
}
