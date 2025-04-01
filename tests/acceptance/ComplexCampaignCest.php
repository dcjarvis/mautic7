<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class ComplexCampaignCest
{
    private $config = [];

    public function _before(AcceptanceTester $I)
    {
        $I->checkEnvVariables();
        $this->config['download_path'] = getenv('DOWNLOAD_PATH');
    }

    public function testComplexCampaign(AcceptanceTester $I)
    {
        // Login first
        $I->amOnPage('/s/login');
        $I->fillField('#username', getenv('MAUTIC_USERNAME'));
        $I->fillField('#password', getenv('MAUTIC_PASSWORD'));
        $I->click('login');
        $I->waitForElement('#mautic_dashboard_index', 30);

        // First create a dynamic content item
        $I->amOnPage('/s/dwc');
        $I->waitForElement(['css' => 'a[href*="dwc/new"]'], 30);
        $I->click(['css' => 'a[href*="dwc/new"]']);
        $I->waitForElement('#dwc_name', 30);
        $I->fillField('#dwc_name', 'Test Dynamic Content '.time());

        // Add default content
        $I->waitForElement(['css' => '.editor'], 30);
        $I->executeJS("
            var editor = document.querySelector('.editor');
            if (editor) {
                editor.innerHTML = 'Default content for all visitors';
            }
        ");

        // Add a filter for points
        $I->waitForElement(['css' => '.add-filter'], 30);
        $I->click(['css' => '.add-filter']);
        $I->waitForElement(['css' => '.dwc-filter'], 30);
        $I->selectOption(['css' => '.dwc-filter select'], 'point');
        $I->fillField(['css' => '.dwc-filter input[type="number"]'], '100');

        // Add content for high-point contacts
        $I->waitForElement(['css' => '.variant-content .editor'], 30);
        $I->executeJS("
            var variantEditor = document.querySelector('.variant-content .editor');
            if (variantEditor) {
                variantEditor.innerHTML = 'Special content for high-value contacts';
            }
        ");

        // Save the dynamic content
        $I->click(['css' => '.btn-primary[type="submit"]']);
        $I->waitForText('Dynamic Web Content has been saved', 30);

        // Create a point action
        $I->amOnPage('/s/points');
        $I->waitForElement(['css' => 'a[href*="points/new"]'], 30);
        $I->click(['css' => 'a[href*="points/new"]']);
        $I->waitForElement('#point_name', 30);
        $I->fillField('#point_name', 'Visit High Value Page');
        $I->fillField('#point_delta', '50');
        $I->click('Save & Close');
        $I->waitForText('Point action has been saved', 30);

        // Now create the complex campaign
        $I->amOnPage('/s/campaigns');
        $I->waitForElement(['css' => 'a[href*="campaigns/new"]'], 30);
        $I->click(['css' => 'a[href*="campaigns/new"]']);
        $I->waitForElement('form[name=campaign]', 30);
        $I->fillField('campaign[name]', 'Complex Campaign '.time());

        // Launch campaign builder
        $I->click('Launch Campaign Builder');
        $I->waitForElement('#CampaignEventPanel', 30);

        // Add source event (Form submit)
        $I->click(['css' => '#SourceList_chosen']);
        $I->waitForElement(['css' => '#SourceList_chosen.chosen-container-active'], 30);
        $I->click(['css' => '#SourceList_chosen .chosen-results li:contains("Form submit")']);
        $I->waitForElement('#campaignevent_properties_form', 30);
        $I->click('Submit');

        // Add points decision
        $I->click(['css' => '#ConditionList_chosen']);
        $I->waitForElement(['css' => '#ConditionList_chosen.chosen-container-active'], 30);
        $I->click(['css' => '#ConditionList_chosen .chosen-results li:contains("Contact Points")']);
        $I->waitForElement('#campaignevent_properties_points', 30);
        $I->fillField('#campaignevent_properties_points', '100');
        $I->click('Submit');

        // Add email action for high points
        $I->click(['css' => '#ActionList_chosen']);
        $I->waitForElement(['css' => '#ActionList_chosen.chosen-container-active'], 30);
        $I->click(['css' => '#ActionList_chosen .chosen-results li:contains("Send Email")']);
        $I->waitForElement('#campaignevent_properties_email', 30);
        $I->click('Submit');

        // Add modify contact action for low points
        $I->click(['css' => '#ActionList_chosen']);
        $I->waitForElement(['css' => '#ActionList_chosen.chosen-container-active'], 30);
        $I->click(['css' => '#ActionList_chosen .chosen-results li:contains("Modify Contact")']);
        $I->waitForElement('#campaignevent_properties_properties', 30);
        $I->click('Submit');

        // Add dynamic content decision
        $I->click(['css' => '#ActionList_chosen']);
        $I->waitForElement(['css' => '#ActionList_chosen.chosen-container-active'], 30);
        $I->click(['css' => '#ActionList_chosen .chosen-results li:contains("Push Dynamic Content")']);
        $I->waitForElement('#campaignevent_properties_dynamicContent', 30);
        $I->click('Submit');

        // Save the campaign
        $I->waitForElement(['css' => 'button[name="campaign[buttons][save]"]'], 30);
        $I->click(['css' => 'button[name="campaign[buttons][save]"]']);
        $I->waitForText('Campaign has been saved', 30);
    }
}
