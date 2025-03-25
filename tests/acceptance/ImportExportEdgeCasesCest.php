<?php

namespace Tests\Support\Acceptance;

use Dotenv\Dotenv;
use Tests\Support\AcceptanceTester;

class ImportExportEdgeCasesCest
{
    private $config;

    public function _before(AcceptanceTester $I)
    {
        // Load environment variables from .env file
        $dotenv = Dotenv::createImmutable('/Users/dcjarvis/Documents/Mautic/testing/mautic-campaign-testing/tests');
        $dotenv->load();

        $this->config = [
            'username' => $_ENV['MAUTIC_USERNAME'] ?? 'admin',
            'password' => $_ENV['MAUTIC_PASSWORD'] ?? 'mautic',
            'url' => $_ENV['MAUTIC_URL'] ?? 'http://localhost:8080',
            'emptyJsonPath' => __DIR__ . '/data/empty_campaign.json',
            'specialCharCampaignPath' => __DIR__ . '/data/special_chars_campaign.json',
            'zeroEventsCampaignPath' => __DIR__ . '/data/zero_events_campaign.json'
        ];

        $I->loginToMautic($this->config['username'], $this->config['password']);
        $I->amOnPage('/s/campaigns');
        $I->waitForElementVisible('.campaigns-list', 10);
    }

    /**
     * Test importing an empty campaign JSON
     * 
     * @group campaign
     * @group import
     * @group edge-cases
     */
    public function testImportEmptyCampaign(AcceptanceTester $I)
    {
        $I->amOnPage('/s/campaigns/import');
        $I->attachFile('#campaign_import_file', $this->config['emptyJsonPath']);
        $I->click('Import');
        $I->waitForElementVisible('.alert-success', 10);
        $I->see('Campaign imported successfully');
    }

    /**
     * Test importing a campaign with special characters in name
     * 
     * @group campaign
     * @group import
     * @group edge-cases
     */
    public function testImportCampaignWithSpecialCharacters(AcceptanceTester $I)
    {
        $I->amOnPage('/s/campaigns/import');
        $I->attachFile('#campaign_import_file', $this->config['specialCharCampaignPath']);
        $I->click('Import');
        $I->waitForElementVisible('.alert-success', 10);
        $I->see('Campaign imported successfully');
    }

    /**
     * Test importing a campaign with zero events
     * 
     * @group campaign
     * @group import
     * @group edge-cases
     */
    public function testImportCampaignWithZeroEvents(AcceptanceTester $I)
    {
        $I->amOnPage('/s/campaigns/import');
        $I->attachFile('#campaign_import_file', $this->config['zeroEventsCampaignPath']);
        $I->click('Import');
        $I->waitForElementVisible('.alert-success', 10);
        $I->see('Campaign imported successfully');
    }

    /**
     * Test exporting a campaign with zero events
     * 
     * @group campaign
     * @group export
     * @group edge-cases
     */
    public function testExportCampaignWithZeroEvents(AcceptanceTester $I)
    {
        // Implement export test logic
        $I->amOnPage('/s/campaigns');
        $I->click('.campaign-export-button');
        $I->waitForElementVisible('.export-modal', 10);
        $I->click('Export');
        $I->waitForElementVisible('.alert-success', 10);
        $I->see('Campaign exported successfully');
    }
}
