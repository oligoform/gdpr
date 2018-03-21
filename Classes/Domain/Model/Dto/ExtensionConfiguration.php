<?php
declare(strict_types=1);

namespace GeorgRinger\Gdpr\Domain\Model\Dto;

use TYPO3\CMS\Core\SingletonInterface;

class ExtensionConfiguration implements SingletonInterface
{

    /** @var string */
    protected $randomizerLocale = 'en_US';

    public function __construct()
    {
        $settings = (array)unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['gdpr'], ['allowed_classes' => false]);
        if (!empty($settings)) {
            $this->randomizerLocale = $settings['randomizerLocale'];
        }
    }

    /**
     * @return string
     */
    public function getRandomizerLocale(): string
    {
        return $this->randomizerLocale;
    }

}
