<?php
namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class LargeCampaignImportCest
{
    private $campaignFilePath;
    private $config = [];

    public function _before(AcceptanceTester $I)
    {
        // Set campaign import file path
        $this->campaignFilePath = __DIR__ . '/data/large_complex_campaign.json';

        // Load configuration from .env or predefined settings
        $this->config = [
            'url' => getenv('MAUTIC_URL') ?: 'http://localhost:8080',
            'username' => getenv('MAUTIC_USERNAME') ?: 'admin',
            'password' => getenv('MAUTIC_PASSWORD') ?: 'mautic'
        ];
    }

    /**
     * Test importing a large, complex campaign
     * 
     * @group campaign
     * @group import
     * @group performance
     */
    public function testImportLargeCampaign(AcceptanceTester $I)
    {
        // Login to Mautic
        $I->loginToMautic($this->config['username'], $this->config['password']);

        // Navigate to Campaigns section
        $I->amOnPage('/s/campaigns');
        $I->waitForElementVisible('.campaigns-list', 10);

        // Click Import Campaign button
        $I->click('Import Campaign');
        $I->waitForElementVisible('#campaign_import_file', 10);

        // Upload campaign JSON file
        $I->attachFile('#campaign_import_file', $this->campaignFilePath);
        $I->click('Upload');

        // Wait for import processing
        $I->waitForElementVisible('.campaign-import-results', 30);

        // Verify import success
        $I->see('Campaign imported successfully');

        // Validate imported campaign details
        $this->validateImportedCampaignDetails($I);
    }

    /**
     * Validate details of the imported large campaign
     */
    private function validateImportedCampaignDetails(AcceptanceTester $I)
    {
        // Read the original campaign JSON for comparison
        $originalCampaign = json_decode(file_get_contents($this->campaignFilePath), true);

        // Check campaign name
        $I->see($originalCampaign['name']);

        // Verify number of events
        $I->click($originalCampaign['name']);
        $I->waitForElementVisible('.campaign-events', 10);
        
        // Count and validate events
        $eventCount = count($originalCampaign['events']);
        $displayedEvents = $I->grabMultiple('.campaign-event');
        $I->assertEquals($eventCount, count($displayedEvents), 
            "Expected {$eventCount} events, but found " . count($displayedEvents));

        // Validate emails
        $this->validateImportedEmails($I, $originalCampaign['emails']);

        // Validate external assets
        $this->validateImportedAssets($I, $originalCampaign['assets']);
    }

    /**
     * Validate imported email templates
     */
    private function validateImportedEmails(AcceptanceTester $I, array $emails)
    {
        // Navigate to Emails section
        $I->amOnPage('/s/emails');
        $I->waitForElementVisible('.emails-list', 10);

        foreach ($emails as $email) {
            // Search for email by name
            $I->fillField('search', $email['name']);
            $I->click('Search');

            // Verify email exists
            $I->see($email['name']);
            $I->see($email['subject']);
        }
    }

    /**
     * Validate imported external assets
     */
    private function validateImportedAssets(AcceptanceTester $I, array $assets)
    {
        // Navigate to Assets section
        $I->amOnPage('/s/assets');
        $I->waitForElementVisible('.assets-list', 10);

        foreach ($assets as $asset) {
            // Search for asset by title
            $I->fillField('search', $asset['title']);
            $I->click('Search');

            // Verify asset exists
            $I->see($asset['title']);
            $I->see($asset['type']);
        }
    }

    /**
     * Performance test for large campaign import
     * 
     * @group performance
     */
    public function testLargeCampaignImportPerformance(AcceptanceTester $I)
    {
        $startTime = microtime(true);

        // Perform campaign import
        $this->testImportLargeCampaign($I);

        $endTime = microtime(true);
        $importDuration = $endTime - $startTime;

        // Assert import takes less than 2 minutes
        $I->assertLessThan(120, $importDuration, 
            "Campaign import took longer than 2 minutes. Actual time: {$importDuration} seconds");
    }
}
