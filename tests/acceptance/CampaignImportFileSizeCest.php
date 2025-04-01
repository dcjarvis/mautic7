<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class CampaignImportFileSizeCest
{
    private $config = [];

    public function _before(AcceptanceTester $I)
    {
        $I->checkEnvVariables();
        $this->config['download_path'] = getenv('DOWNLOAD_PATH');
    }

    public function testCampaignImportFileSizeLimit(AcceptanceTester $I)
    {
        // Login first
        $I->amOnPage('/s/login');
        $I->fillField('#username', getenv('MAUTIC_USERNAME'));
        $I->fillField('#password', getenv('MAUTIC_PASSWORD'));
        $I->click('login');
        $I->waitForElement('#mautic_dashboard_index', 30);

        // Go to Campaigns section
        $I->amOnPage('/s/campaigns');
        $I->waitForElement('.page-header h3', 30);
        $I->see('Campaigns');

        // Go to import page
        $I->click(['css' => 'a[href*="campaigns/import"]']);

        // Check file size
        $downloadPath = $this->config['download_path'].'/campaign.json';

        // Verify file exists
        $I->assertTrue(file_exists($downloadPath), 'Campaign JSON file does not exist');

        // Get file size in kilobytes
        $fileSizeKB = filesize($downloadPath) / 1024;

        // Assert file size is not greater than 1024 KB
        $I->assertLessThanOrEqual(1024, $fileSizeKB,
            "Campaign JSON file size ({$fileSizeKB} KB) exceeds the 1024 KB limit");

        // Attempt to import the file
        $I->attachFile('input[type="file"]', $downloadPath);
        $I->click('Import');
        $I->see('Campaign has been imported');

        // Prepare for complex campaign test
        $I->amOnPage('/s/campaigns');
        $I->waitForElement('.page-header h3', 30);

        // Create a new campaign with multiple events
        $I->click(['css' => 'a[href*="campaigns/new"]']);
        $I->waitForElement('form[name=campaign]', 30);
        $I->fillField('campaign[name]', 'Complex Test Campaign '.time());

        // Launch campaign builder
        $I->click('Launch Campaign Builder');
        $I->waitForElement('#CampaignEventPanel', 30);

        // Add multiple events (similar to the complex test in CampaignImportExportCest)
        // Source event
        $I->executeJS("
            var sourceSelect = document.querySelector('#SourceList');
            var firstOptionValue = sourceSelect.options[1].value;
            var event = new CustomEvent('campaign-builder-source-add', {
                detail: {
                    type: firstOptionValue,
                    eventType: 'source'
                }
            });
            document.dispatchEvent(event);
        ");
        $I->waitForElementVisible('#campaignevent_properties_type', 30);
        $I->click('Submit');

        // Action event (Send Email)
        $I->executeJS("
            var actionSelect = document.querySelector('#ActionList');
            var emailOption = Array.from(actionSelect.options).find(opt => opt.text.includes('Send Email'));
            if (emailOption) {
                var event = new CustomEvent('campaign-builder-event-add', {
                    detail: {
                        type: emailOption.value,
                        eventType: 'action'
                    }
                });
                document.dispatchEvent(event);
            }
        ");
        $I->waitForElementVisible('#campaignevent_properties_email', 30);
        $I->click('Submit');

        // Condition event
        $I->executeJS("
            var conditionSelect = document.querySelector('#ConditionList');
            var fieldOption = Array.from(conditionSelect.options).find(opt => opt.text.includes('Contact Field Value'));
            if (fieldOption) {
                var event = new CustomEvent('campaign-builder-event-add', {
                    detail: {
                        type: fieldOption.value,
                        eventType: 'condition'
                    }
                });
                document.dispatchEvent(event);
            }
        ");
        $I->waitForElementVisible('#campaignevent_properties_field', 30);
        $I->click('Submit');

        // Save the complex campaign
        $I->waitForElement(['css' => 'button[name="campaign[buttons][save]"]'], 30);
        $I->executeJS('document.querySelector(\'button[name="campaign[buttons][save]"]\').click()');
        $I->waitForText('Campaign has been saved', 30);
    }
}
