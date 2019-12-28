<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class ControllingSecondaryParamTranslator implements ChannelParamTranslator {
    /** @var ControllingAnyLockRelatedSensorUpdater */
    private $updater;

    public function __construct(ControllingAnyLockRelatedSensorUpdater $updater) {
        $this->updater = $updater;
    }

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return ['controllingSecondaryChannelId' => $channel->getParam2() ?: null];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (array_key_exists('controllingSecondaryChannelId', $config)) {
            if ($channel->getParam1() == $config['controllingSecondaryChannelId']) {
                // primary and secondary sensors the same, clear secondary
                $config['controllingSecondaryChannelId'] = 0;
            }
            $this->updater->pairControllingAndSensorChannels(
                new ChannelFunction(ControllingChannelParamTranslator::SENSOR_CONTROLLING_PAIRS[$channel->getFunction()->getId()]),
                $channel->getFunction(),
                3,
                2,
                intval($config['controllingSecondaryChannelId']),
                $channel->getId()
            );
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::OPENINGSENSOR_GATE,
        ]);
    }
}
