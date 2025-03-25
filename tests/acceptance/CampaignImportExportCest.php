<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
use Helper\RemoteTestHelper;

class CampaignImportExportCest
{
    private $config = [];
    private $remoteTestHelper;

    public function _before(AcceptanceTester $I)
    {
        // Initialize configuration
        $this->config['download_path'] = getenv('DOWNLOAD_PATH') ?: 
            '/Users/dcjarvis/Documents/Mautic/testing/mautic-campaign-testing/tests/_output/downloads';
        
        // Initialize RemoteTestHelper
        $this->remoteTestHelper = new RemoteTestHelper($I);
    }

    public function testRemoteMauticConnectivity(AcceptanceTester $I)
    {
        // Validate remote connection
        try {
            $mauticUrl = getenv('MAUTIC_REMOTE_URL') ?: getenv('MAUTIC_URL');
            $this->remoteTestHelper->validateRemoteConnection($mauticUrl);
            
            // Check network latency
            $latency = $this->remoteTestHelper->checkNetworkLatency($mauticUrl);
            $I->comment("Network latency: " . round($latency, 2) . " seconds");
        } catch (\Exception $e) {
            $I->fail("Remote Mautic connectivity test failed: " . $e->getMessage());
        }
    }

    public function testCampaignImportExport(AcceptanceTester $I)
    {
        // Determine authentication method
        $authMethod = getenv('MAUTIC_AUTH_METHOD') ?: 'standard';
        
        // Login configuration
        $loginConfig = [
            'standard' => function() use ($I) {
                $I->amOnPage('/s/login');
                $username = getenv('MAUTIC_REMOTE_USERNAME') ?: getenv('MAUTIC_USERNAME');
                $password = getenv('MAUTIC_REMOTE_PASSWORD') ?: getenv('MAUTIC_PASSWORD');
                
                $I->fillField('#username', $username);
                $I->fillField('#password', $password);
                $I->click('Log In');
                
                // Wait for dashboard with error handling
                $I->waitForText('Dashboard', 30, function() use ($I) {
                    if ($I->seeElement('.alert-error')) {
                        $errorMessage = $I->grabTextFrom('.alert-error');
                        throw new \Exception("Login failed: $errorMessage");
                    }
                });
            },
            'sso' => function() use ($I) {
                // SSO login method placeholder
                $I->comment('SSO login method not implemented');
                $I->fail('SSO login not configured');
            }
        ];
        
        // Execute login
        try {
            $loginConfig[$authMethod]();
        } catch (\Exception $e) {
            $I->fail($e->getMessage());
        }
        
        // Campaign scenarios
        $campaignScenarios = [
            'Basic Campaign' => [
                'name' => 'Remote Basic Campaign',
                'events' => 1,
                'complexity' => 'low'
            ],
            'Advanced Campaign' => [
                'name' => 'Remote Enterprise Campaign',
                'events' => 5,
                'complexity' => 'high'
            ]
        ];
        
        foreach ($campaignScenarios as $scenarioName => $scenario) {
            $I->comment("Testing $scenarioName Scenario");
            
            // Create campaign
            $I->amOnPage('/s/campaigns');
            $I->click(['css' => 'a[href*="campaigns/new"]']);
            
            $campaignName = $scenario['name'] . ' ' . time();
            $I->fillField('campaign[name]', $campaignName);
            
            // Add campaign events based on complexity
            if ($scenario['events'] > 1) {
                $I->click('Launch Campaign Builder');
                $I->waitForElement('#CampaignEventPanel', 30);
            }
            
            $I->click('Save & Close');
            $I->waitForText('Campaign saved', 10);
            
            // Export campaign
            $I->click(['xpath' => "//a[contains(text(), '$campaignName')]"]);
            $I->waitForElement('.dropdown-toggle', 10);
            $I->click('.dropdown-toggle');
            $I->click('Export');
            
            // Verify export
            $exportFilePath = $this->config['download_path'] . "/remote_campaign_export_{$scenario['complexity']}_" . time() . '.json';
            $I->wait(5);
            $I->assertTrue(file_exists($exportFilePath), "Export file not generated for $scenarioName");
            
            // Validate export file
            $exportContent = json_decode(file_get_contents($exportFilePath), true);
            $I->assertNotEmpty($exportContent, "Export file is empty for $scenarioName");
            $I->assertEquals($campaignName, $exportContent['name'], "Exported campaign name mismatch for $scenarioName");
            
            // Clean up
            unlink($exportFilePath);
        }
    }
}