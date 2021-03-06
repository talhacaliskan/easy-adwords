<?php

namespace EasyAdWords\Keywords;

use EasyAdWords\Config;
use EasyAdWords\Entity;
use Exception;
use Google\AdsApi\AdWords\v201802\cm\AdGroupCriterionOperation;
use Google\AdsApi\AdWords\v201802\cm\AdGroupCriterionService;
use Google\AdsApi\AdWords\v201802\cm\BiddableAdGroupCriterion;
use Google\AdsApi\AdWords\v201802\cm\BiddingStrategyConfiguration;
use Google\AdsApi\AdWords\v201802\cm\CpcBid;
use Google\AdsApi\AdWords\v201802\cm\Money;
use Google\AdsApi\AdWords\v201802\cm\Operator;

/**
 * Base class for Keyword and KeywordBatch classes.
 * Main objective is to merge repetitive parts of the both classes into a parent class.
 *
 * Class KeywordBase
 * @package EasyAdWords\Keywords
 */
class KeywordBase extends Entity {

    /**
     * @var \Google\AdsApi\Common\AdsSoapClient|\Google\AdsApi\Common\SoapClient    The Ad Group Criterion Service object.
     */
    protected $adGroupCriterionService;

    /**
     * KeywordBase constructor.
     * @param Config $config
     */
    public function __construct(Config $config) {

        // Construct the parent class.
        parent::__construct($config);

        // Set the service object.
        $this->adGroupCriterionService = $this->adWordsServices->get($this->authObject->getSession(), AdGroupCriterionService::class);
    }

    /**
     * Create a keyword operation based on the config.
     * @param KeywordConfig $config
     * @return AdGroupCriterionOperation
     * @throws Exception
     */
    public function createKeywordOperation(KeywordConfig $config) {

        // Create the criterion object.
        $adGroupCriterionObject = new \Google\AdsApi\AdWords\v201802\cm\Keyword();

        // Set the text and the match types of the criterion object.
        $adGroupCriterionObject->setText($config->getKeyword());
        $adGroupCriterionObject->setMatchType($config->getMatchType());

        // Create a new biddable ad group criterion.
        $adGroupCriterion = new BiddableAdGroupCriterion();
        if ($config->getAdGroupId()) {
            $adGroupCriterion->setAdGroupId($config->getAdGroupId());
        } else {
            throw new Exception("Ad group ID must be set in the config object in order to create a keyword.");
        }

        $adGroupCriterion->setCriterion($adGroupCriterionObject);

        // Set status if given in the config.
        if ($config->getStatus()) {
            $adGroupCriterion->setUserStatus($config->getStatus());
        }

        // Set final urls if given in the config.
        if ($config->getFinalUrls()) {
            $adGroupCriterion->setFinalUrls($config->getFinalUrls());
        }

        // Set bids if given in the config.
        if ($config->getBid()) {
            $adGroupCriterion->setBiddingStrategyConfiguration($this->setBiddingConfiguration());
        }

        // Create the operation object.
        $operation = new AdGroupCriterionOperation();
        $operation->setOperand($adGroupCriterion);
        $operation->setOperator(Operator::ADD);
        return $operation;
    }

    /**
     * Create a bidding strategy configuration object based on the config.
     * @return BiddingStrategyConfiguration
     */
    private function setBiddingConfiguration() {
        $bid = new CpcBid();
        $money = new Money();
        $money->setMicroAmount($this->config->getBid() * 1000000);
        $bid->setBid($money);
        $biddingStrategyConfiguration = new BiddingStrategyConfiguration();
        $biddingStrategyConfiguration->setBids([$bid]);
        return $biddingStrategyConfiguration;
    }


    /**
     * Get Ad Group Criterion Service object.
     * @return \Google\AdsApi\Common\AdsSoapClient|\Google\AdsApi\Common\SoapClient
     */
    public function getAdGroupCriterionService() {
        return $this->adGroupCriterionService;
    }

    /**
     * Set Ad Group Criterion Service object.
     * @param \Google\AdsApi\Common\AdsSoapClient|\Google\AdsApi\Common\SoapClient $adGroupCriterionService
     * @return KeywordBase
     */
    public function setAdGroupCriterionService($adGroupCriterionService) {
        $this->adGroupCriterionService = $adGroupCriterionService;
        return $this;
    }
}