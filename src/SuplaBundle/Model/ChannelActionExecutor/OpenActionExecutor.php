<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Auth\Token\WebappToken;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelStateGetter\ValveChannelStateGetter;
use SuplaBundle\Model\CurrentUserAware;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class OpenActionExecutor extends SetCharValueActionExecutor {
    use CurrentUserAware;

    /** @var ValveChannelStateGetter */
    private $valveManuallyShutChannelStateGetter;

    public function __construct(ValveChannelStateGetter $valveManuallyShutChannelStateGetter) {
        $this->valveManuallyShutChannelStateGetter = $valveManuallyShutChannelStateGetter;
    }

    protected function getCharValue(HasFunction $subject, array $actionParams = []): int {
        if ($this->isGateSubject($subject)) {
            return 2;
        } else {
            return 1;
        }
    }

    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(),
            ChannelFunction::CONTROLLINGTHEDOORLOCK(),
//            ChannelFunction::CONTROLLINGTHEGARAGEDOOR(),
//            ChannelFunction::CONTROLLINGTHEGATE(),
            ChannelFunction::VALVEOPENCLOSE(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::OPEN();
    }

    private function isGateSubject(HasFunction $subject): bool {
        return in_array(
            $subject->getFunction()->getId(),
            [ChannelFunction::CONTROLLINGTHEGATE, ChannelFunction::CONTROLLINGTHEGARAGEDOOR]
        );
    }

    public function validateActionParams(HasFunction $subject, array $actionParams): array {
        Assertion::true(
            !$this->isGateSubject($subject) || $subject instanceof IODeviceChannel,
            "Cannot execute the requested action OPEN on channel group."
        );
        if ($subject->getFunction()->getId() == ChannelFunction::VALVEOPENCLOSE) {
            $state = $this->valveManuallyShutChannelStateGetter->getState($subject);
            $manuallyClosed = $state['manuallyClosed'] ?? true;
            $flooding = $state['flooding'] ?? true;
            if ($manuallyClosed || $flooding) {
                $userToken = $this->getCurrentUserToken();
                if (!$userToken instanceof WebappToken) {
                    throw new ConflictHttpException(
                        'The valve cannot be opened via a direct link or via API once it has been closed manually. ' .
                        'To resume control, open the valve manually.'
                    );
                }
            }
        }
        return parent::validateActionParams($subject, $actionParams);
    }
}
