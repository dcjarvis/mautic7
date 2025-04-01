<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class CampaignImportExportAdvancedCest
{
    private $config = [];
    private $campaignImportFile;
    private $campaignExportFile;

    /**
     * Get global test configuration.
     */
    public static function getTestConfig(): array
    {
        return [
            'url'           => getenv('MAUTIC_URL') ?: 'http://localhost:8080',
            'username'      => getenv('MAUTIC_USERNAME') ?: 'admin',
            'password'      => getenv('MAUTIC_PASSWORD') ?: 'mautic',
            'download_path' => getenv('DOWNLOAD_PATH') ?: '/downloads',
        ];
    }

    public function _before(AcceptanceTester $I)
    {
        // Load configuration
        $this->config = self::getTestConfig();

        // Set import and export file paths
        $this->campaignImportFile = __DIR__.'/data/large_complex_campaign.json';
        $this->campaignExportFile = $this->config['download_path'].'/exported_campaign.json';
    }

    /**
     * Test advanced campaign import functionality.
     *
     * @group campaign
     * @group import
     * @group advanced
     */
    public function testAdvancedCampaignImport(AcceptanceTester $I)
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
        $I->attachFile('#campaign_import_file', $this->campaignImportFile);
        $I->click('Upload');

        // Wait for import processing and verify success
        $I->waitForElementVisible('.campaign-import-results', 30);
        $I->see('Campaign imported successfully');

        // Verify imported campaign details
        $this->validateImportedCampaignDetails($I);
    }

    /**
     * Validate details of the imported campaign.
     */
    private function validateImportedCampaignDetails(AcceptanceTester $I)
    {
        // Read the original campaign JSON for comparison
        $originalCampaign = json_decode(file_get_contents($this->campaignImportFile), true);

        // Verify campaign name exists in the list
        $I->see($originalCampaign['name']);

        // Open the imported campaign
        $I->click($originalCampaign['name']);
        $I->waitForElementVisible('.campaign-details', 10);

        // Validate campaign events
        $this->validateCampaignEvents($I, $originalCampaign);

        // Validate associated emails
        $this->validateCampaignEmails($I, $originalCampaign);

        // Validate associated assets
        $this->validateCampaignAssets($I, $originalCampaign);
    }

    /**
     * Validate campaign events.
     */
    private function validateCampaignEvents(AcceptanceTester $I, array $campaignData)
    {
        // Count and validate events
        $eventCount      = count($campaignData['events']);
        $displayedEvents = $I->grabMultiple('.campaign-event');

        $I->assertEquals($eventCount, count($displayedEvents),
            "Expected {$eventCount} events, but found ".count($displayedEvents));

        // Validate event properties
        foreach ($campaignData['events'] as $event) {
            $I->see($event['name']);
            $I->see($event['type']);
        }
    }

    /**
     * Validate campaign emails.
     */
    private function validateCampaignEmails(AcceptanceTester $I, array $campaignData)
    {
        // Navigate to Emails section
        $I->amOnPage('/s/emails');
        $I->waitForElementVisible('.emails-list', 10);

        foreach ($campaignData['emails'] as $email) {
            // Search for email by name
            $I->fillField('search', $email['name']);
            $I->click('Search');

            // Verify email details
            $I->see($email['name']);
            $I->see($email['subject']);
        }
    }

    /**
     * Validate campaign assets.
     */
    private function validateCampaignAssets(AcceptanceTester $I, array $campaignData)
    {
        // Navigate to Assets section
        $I->amOnPage('/s/assets');
        $I->waitForElementVisible('.assets-list', 10);

        foreach ($campaignData['assets'] as $asset) {
            // Search for asset by title
            $I->fillField('search', $asset['title']);
            $I->click('Search');

            // Verify asset details
            $I->see($asset['title']);
            $I->see($asset['type']);
        }
    }

    /**
     * Test advanced campaign export functionality.
     *
     * @group campaign
     * @group export
     * @group advanced
     *
     * @depends testAdvancedCampaignImport
     */
    public function testAdvancedCampaignExport(AcceptanceTester $I)
    {
        // Login to Mautic
        $I->loginToMautic($this->config['username'], $this->config['password']);

        // Navigate to Campaigns section
        $I->amOnPage('/s/campaigns');
        $I->waitForElementVisible('.campaigns-list', 10);

        // Find the recently imported campaign
        $originalCampaign = json_decode(file_get_contents($this->campaignImportFile), true);
        $I->fillField('search', $originalCampaign['name']);
        $I->click('Search');

        // Open campaign export dropdown
        $I->click('.campaign-actions-dropdown');
        $I->click('Export Campaign');

        // Wait for export and download
        $I->waitForElementVisible('.export-progress', 30);
        $I->see('Campaign exported successfully');

        // Verify exported file exists and is valid
        $this->validateExportedCampaignFile($I);
    }

    /**
     * Validate exported campaign file.
     */
    private function validateExportedCampaignFile(AcceptanceTester $I)
    {
        // Check if export file exists
        $I->assertTrue(file_exists($this->campaignExportFile),
            "Campaign export file not found at {$this->campaignExportFile}");

        // Validate JSON structure
        $exportedData = json_decode(file_get_contents($this->campaignExportFile), true);

        $I->assertNotNull($exportedData, 'Exported campaign file is not a valid JSON');

        // Check key campaign attributes
        $I->assertArrayHasKey('name', $exportedData);
        $I->assertArrayHasKey('description', $exportedData);
        $I->assertArrayHasKey('events', $exportedData);
        $I->assertArrayHasKey('emails', $exportedData);
        $I->assertArrayHasKey('assets', $exportedData);
    }

    /**
     * Performance test for campaign import and export.
     *
     * @group performance
     * @group campaign
     */
    public function testCampaignImportExportPerformance(AcceptanceTester $I)
    {
        // Measure import performance
        $importStartTime = microtime(true);
        $this->testAdvancedCampaignImport($I);
        $importEndTime  = microtime(true);
        $importDuration = $importEndTime - $importStartTime;

        // Measure export performance
        $exportStartTime = microtime(true);
        $this->testAdvancedCampaignExport($I);
        $exportEndTime  = microtime(true);
        $exportDuration = $exportEndTime - $exportStartTime;

        // Assert performance thresholds
        $I->assertLessThan(120, $importDuration,
            "Campaign import took longer than 2 minutes. Actual time: {$importDuration} seconds");

        $I->assertLessThan(60, $exportDuration,
            "Campaign export took longer than 1 minute. Actual time: {$exportDuration} seconds");
    }
}
