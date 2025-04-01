<?php

namespace Tests\Acceptance;

class CampaignImportUpdateCest
{
    public function testCampaignImportWithUniqueIdOverwrite(\AcceptanceTester $I)
    {
        // Login to Mautic
        $I->login();

        // Navigate to Campaign Import page
        $I->amOnPage('/campaigns/import');

        // Submit import
        $I->click('Import Campaign');

        // Grab the campaign UUID from the database
        $campaignUuid = $I->grabFromDatabase('campaigns', 'uuid', [
            'name' => 'Test Campaign',
            'uuid' => 'uuid',
        ]);

        // Store the campaign UUID for further verification
        $I->comment("Imported Campaign UUID: $campaignUuid");

        // Select option to update existing entities for all rows
        $I->executeJS("
    document.querySelectorAll('input[name=\"update_entity\"]').forEach(function(radio) {
        radio.checked = true;
    });
        ");

        // Alternative approach if the above doesn't work
        $I->click("//input[@name='update_entity']");
        $I->wait(1); // Add a small wait to ensure radio buttons are processed

        // Submit import
        $I->click('Import Campaign');

        // Assert campaign was imported or updated
        $I->see('Campaign imported successfully');
        $I->seeInDatabase('campaigns', [
            'name' => 'Test Campaign',
            'uuid' => 'uuid',
        ]);

        // Optional: Check that no duplicate campaigns were created
        $I->dontSeeInDatabase('campaigns', [
            'name' => 'Test Campaign',
            'uuid' => 'test_campaign_unique_id',
            'id'   => '!=', // Exclude the original campaign
        ]);
    }
}
