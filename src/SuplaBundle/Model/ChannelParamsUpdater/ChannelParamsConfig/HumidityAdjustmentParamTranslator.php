<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;

class HumidityAdjustmentParamTranslator implements ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'humidityAdjustment' => NumberUtils::maximumDecimalPrecision($channel->getParam3() / 100, 2),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (isset($config['humidityAdjustment'])) {
            $channel->setParam3(intval($config['humidityAdjustment'] * 100));
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::HUMIDITY,
            ChannelFunction::HUMIDITYANDTEMPERATURE,
        ]);
    }
}
