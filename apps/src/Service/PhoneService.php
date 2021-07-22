<?php

namespace Labstag\Service;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberToCarrierMapper;
use libphonenumber\PhoneNumberToTimeZonesMapper;
use libphonenumber\PhoneNumberUtil;
use Psr\Log\LoggerInterface;

class PhoneService
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var PhoneNumberUtil
     */
    protected $phoneUtil;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger    = $logger;
        $this->phoneUtil = PhoneNumberUtil::getInstance();
    }

    /**
     * Verifie le numéro de téléphone en fonction du pays.
     *
     * @param string $numero Numéro de téléphone
     * @param string $locale code du pays
     *
     * @throws NumberParseException
     */
    public function verif(?string $numero, ?string $locale): array
    {
        $numero         = str_replace([' ', '-', '.'], '', (string) $numero);
        $data           = [];
        $timeZoneMapper = PhoneNumberToTimeZonesMapper::getInstance();
        $carrier        = PhoneNumberToCarrierMapper::getInstance();

        try {
            $parse   = $this->phoneUtil->parse(
                $numero,
                strtoupper($locale)
            );
            $isvalid = $this->phoneUtil->isValidNumber($parse);

            $data['isvalid']   = $isvalid;
            $data['format']    = [
                'e164'          => $this->phoneUtil->format(
                    $parse,
                    PhoneNumberFormat::E164
                ),
                'national'      => $this->phoneUtil->format(
                    $parse,
                    PhoneNumberFormat::NATIONAL
                ),
                'international' => $this->phoneUtil->format(
                    $parse,
                    PhoneNumberFormat::INTERNATIONAL
                ),
            ];
            $data['timezones'] = $timeZoneMapper->getTimeZonesForNumber($parse);
            $data['carrier']   = $carrier->getNameForNumber(
                $parse,
                strtoupper($locale)
            );
            $data['parse']     = $parse;
        } catch (NumberParseException $exception) {
            $this->logger->error($exception->getMessage());
            $data['error'] = $exception->getMessage();
        }

        return $data;
    }
}
