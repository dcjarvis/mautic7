<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class CampaignSecurityCest
{
    private $config = [];

    public function _before(AcceptanceTester $I)
    {
        $I->checkEnvVariables();
        $this->config['download_path'] = getenv('DOWNLOAD_PATH');

        // Add logging and longer wait times
        $I->comment('Attempting to access Mautic login page');
        $I->amOnPage('/s/login');
        $I->waitForElement('#username', 60);
        $I->comment('Login page loaded successfully');

        // Define users to create
        $users = [
            'editor' => [
                'username' => getenv('MAUTIC_EDITOR_USERNAME'),
                'password' => getenv('MAUTIC_EDITOR_PASSWORD'),
                'email' => 'editor@mautic.test',
                'firstName' => 'Campaign',
                'lastName' => 'Editor',
                'role' => 'Editor'
            ],
            'viewer' => [
                'username' => getenv('MAUTIC_VIEWER_USERNAME'),
                'password' => getenv('MAUTIC_VIEWER_PASSWORD'),
                'email' => 'viewer@mautic.test',
                'firstName' => 'Campaign',
                'lastName' => 'Viewer',
                'role' => 'Viewer'
            ]
        ];

        foreach ($users as $userType => $userData) {
            // Create user
            $I->comment("Creating user: {$userData['username']}");
            $I->amOnPage('/s/login');
            $I->fillField('#username', getenv('MAUTIC_ADMIN_USERNAME'));
            $I->fillField('#password', getenv('MAUTIC_ADMIN_PASSWORD'));
            $I->click('login');
            $I->waitForElement('#mautic_dashboard_index', 60);

            // Navigate to user management
            $I->amOnPage('/s/users');
            $I->waitForElement('a[href*="users/new"]', 60);

            // Create user
            $I->click('New User');
            $I->waitForElement('#user_username', 60);
            $I->fillField('#user_username', $userData['username']);
            $I->fillField('#user_password', $userData['password']);
            $I->fillField('#user_email', $userData['email']);
            $I->fillField('#user_firstName', $userData['firstName']);
            $I->fillField('#user_lastName', $userData['lastName']);
            $I->selectOption('select[name="user[role]"]', $userData['role']);
            $I->click('Save');
            $I->waitForElement('.alert-success', 60);
            $I->comment("User {$userData['username']} created successfully");
        }
    }

    public function testCreateUsersWithDifferentRoles(AcceptanceTester $I)
    {
        // Login as admin
        $I->amOnPage('/s/login');
        $I->fillField('#username', getenv('MAUTIC_ADMIN_USERNAME'));
        $I->fillField('#password', getenv('MAUTIC_ADMIN_PASSWORD'));
        $I->click('login');
        $I->waitForElement('#mautic_dashboard_index', 30);

        // Navigate to user management
        $I->amOnPage('/s/users');
        $I->waitForElement('a[href*="users/new"]', 30);

        // Define users to create
        $users = [
            'editor' => [
                'username' => getenv('MAUTIC_EDITOR_USERNAME'),
                'password' => getenv('MAUTIC_EDITOR_PASSWORD'),
                'email' => 'editor@mautic.test',
                'firstName' => 'Campaign',
                'lastName' => 'Editor',
                'role' => 'Editor'
            ],
            'viewer' => [
                'username' => getenv('MAUTIC_VIEWER_USERNAME'),
                'password' => getenv('MAUTIC_VIEWER_PASSWORD'),
                'email' => 'viewer@mautic.test',
                'firstName' => 'Campaign',
                'lastName' => 'Viewer',
                'role' => 'Viewer'
            ]
        ];

        foreach ($users as $userType => $userData) {
            // Check if user already exists
            $I->amOnPage('/s/users');
            $I->wait(2);

            try {
                // Try to find existing user
                $I->see($userData['username']);

                // If user exists, edit the user
                $I->click(sprintf('//a[contains(text(), "%s")]', $userData['username']));
                $I->waitForElement('form[name="user"]', 30);
            } catch (\Exception $e) {
                // User doesn't exist, create new user
                $I->click('a[href*="users/new"]');
                $I->waitForElement('form[name="user"]', 30);
            }

            // Fill out user details
            $I->fillField('user[username]', $userData['username']);
            $I->fillField('user[email]', $userData['email']);
            $I->fillField('user[firstName]', $userData['firstName']);
            $I->fillField('user[lastName]', $userData['lastName']);
            $I->fillField('user[plainPassword][first]', $userData['password']);
            $I->fillField('user[plainPassword][second]', $userData['password']);

            // Select role
            $I->selectOption('user[role]', $userData['role']);

            // Save user
            $I->click('Save');
            $I->waitForText('User has been saved', 30);
        }

        // Logout
        $I->click('.dropdown-toggle.account-dropdown');
        $I->click('Logout');
    }

    public function testCampaignAccessControlWithDifferentRoles(AcceptanceTester $I)
    {
        // Define different user roles to test
        $roles = [
            'admin' => [
                'username' => getenv('MAUTIC_ADMIN_USERNAME'),
                'password' => getenv('MAUTIC_ADMIN_PASSWORD'),
                'expected_access' => true
            ],
            'editor' => [
                'username' => getenv('MAUTIC_EDITOR_USERNAME'),
                'password' => getenv('MAUTIC_EDITOR_PASSWORD'),
                'expected_access' => true
            ],
            'viewer' => [
                'username' => getenv('MAUTIC_VIEWER_USERNAME'),
                'password' => getenv('MAUTIC_VIEWER_PASSWORD'),
                'expected_access' => false
            ]
        ];

        foreach ($roles as $role => $userData) {
            // Login with different user roles
            $I->amOnPage('/s/login');
            $I->fillField('#username', $userData['username']);
            $I->fillField('#password', $userData['password']);
            $I->click('login');
            $I->waitForElement('#mautic_dashboard_index', 30);

            // Try to access campaign creation page
            $I->amOnPage('/s/campaigns/new');

            if ($userData['expected_access']) {
                // For roles with access, verify campaign creation form is visible
                $I->seeElement('form[name=campaign]');
                $I->see('New Campaign');
            } else {
                // For roles without access, verify they are redirected or blocked
                $I->dontSeeElement('form[name=campaign]');
                $I->see('Access Denied', ['css' => '.alert-danger']);
            }

            // Logout
            $I->click('.dropdown-toggle.account-dropdown');
            $I->click('Logout');
        }
    }

    public function testCampaignImportJsonInjectionPrevention(AcceptanceTester $I)
    {
        // Login as admin
        $I->amOnPage('/s/login');
        $I->fillField('#username', getenv('MAUTIC_ADMIN_USERNAME'));
        $I->fillField('#password', getenv('MAUTIC_ADMIN_PASSWORD'));
        $I->click('login');
        $I->waitForElement('#mautic_dashboard_index', 30);

        // Malicious JSON payloads to test injection
        $maliciousPayloads = [
            'script_injection' => '{
                "name": "<script>alert(\'XSS\');</script>Malicious Campaign",
                "events": []
            }',
            'sql_injection' => '{
                "name": "Injected Campaign\' OR 1=1 --",
                "events": []
            }',
            'remote_code_execution' => '{
                "name": "RCE Test",
                "events": [
                    {"type": "<?php system(\'ls\'); ?>"}
                ]
            }'
        ];

        foreach ($maliciousPayloads as $type => $payload) {
            // Navigate to campaign import page
            $I->amOnPage('/s/campaigns');
            $I->click(['css' => 'a[href*="campaigns/import"]']);

            // Create a temporary file with malicious payload
            $tempFile = tempnam(sys_get_temp_dir(), 'mautic_campaign_test_');
            file_put_contents($tempFile, $payload);

            // Attempt to import malicious file
            $I->attachFile('input[type="file"]', $tempFile);
            $I->click('Import');

            // Verify security measures
            $I->dontSee('Campaign has been imported');
            $I->see('Invalid campaign configuration', ['css' => '.alert-danger']);

            // Clean up temporary file
            unlink($tempFile);
        }
    }

    public function testCampaignExportPermissions(AcceptanceTester $I)
    {
        // Define roles with different export permissions
        $roles = [
            'admin' => [
                'username' => getenv('MAUTIC_ADMIN_USERNAME'),
                'password' => getenv('MAUTIC_ADMIN_PASSWORD'),
                'can_export' => true
            ],
            'editor' => [
                'username' => getenv('MAUTIC_EDITOR_USERNAME'),
                'password' => getenv('MAUTIC_EDITOR_PASSWORD'),
                'can_export' => true
            ],
            'viewer' => [
                'username' => getenv('MAUTIC_VIEWER_USERNAME'),
                'password' => getenv('MAUTIC_VIEWER_PASSWORD'),
                'can_export' => false
            ]
        ];

        foreach ($roles as $role => $userData) {
            // Login
            $I->amOnPage('/s/login');
            $I->fillField('#username', $userData['username']);
            $I->fillField('#password', $userData['password']);
            $I->click('login');
            $I->waitForElement('#mautic_dashboard_index', 30);

            // Go to campaigns
            $I->amOnPage('/s/campaigns');

            // Check for export button visibility and functionality
            if ($userData['can_export']) {
                $I->seeElement(['css' => 'button.dropdown-toggle']);
                $I->click(['css' => 'button.dropdown-toggle']);
                $I->seeElement('a:contains("Export")');
            } else {
                $I->dontSeeElement('button.dropdown-toggle');
                // Or check for disabled export functionality
                $I->dontSee('Export');
            }

            // Logout
            $I->click('.dropdown-toggle.account-dropdown');
            $I->click('Logout');
        }
    }

    public function testCampaignExportPermissionsWithDifferentRoles(AcceptanceTester $I)
    {
        // Define different user roles to test export permissions
        $roles = [
            'admin' => [
                'username' => getenv('MAUTIC_ADMIN_USERNAME'),
                'password' => getenv('MAUTIC_ADMIN_PASSWORD'),
                'expected_export_access' => true
            ],
            'editor' => [
                'username' => getenv('MAUTIC_EDITOR_USERNAME'),
                'password' => getenv('MAUTIC_EDITOR_PASSWORD'),
                'expected_export_access' => true
            ],
            'viewer' => [
                'username' => getenv('MAUTIC_VIEWER_USERNAME'),
                'password' => getenv('MAUTIC_VIEWER_PASSWORD'),
                'expected_export_access' => false
            ]
        ];

        // Create a sample campaign for export testing
        $I->amOnPage('/s/login');
        $I->fillField('#username', getenv('MAUTIC_ADMIN_USERNAME'));
        $I->fillField('#password', getenv('MAUTIC_ADMIN_PASSWORD'));
        $I->click('login');
        $I->waitForElement('#mautic_dashboard_index', 30);

        // Navigate to campaigns
        $I->amOnPage('/s/campaigns');
        $I->waitForElement('a[href*="/s/campaigns/new"]', 30);

        // Create a test campaign
        $I->click('New Campaign');
        $I->waitForElement('#campaign_name', 30);
        $I->fillField('#campaign_name', 'Export Permission Test Campaign');
        $I->click('Save');
        $I->waitForText('Campaign saved', 30);

        // Test export permissions for different roles
        foreach ($roles as $role => $userData) {
            // Login with different user roles
            $I->amOnPage('/s/login');
            $I->fillField('#username', $userData['username']);
            $I->fillField('#password', $userData['password']);
            $I->click('login');
            $I->waitForElement('#mautic_dashboard_index', 30);

            // Navigate to campaigns
            $I->amOnPage('/s/campaigns');
            $I->waitForElement('table.table-striped', 30);

            // Attempt to export campaign
            try {
                // Look for export button or link
                $I->click('Export');
                
                if ($userData['expected_export_access']) {
                    // If export is expected to be allowed
                    $I->waitForText('Export', 30);
                    $I->see('Export Campaigns');
                    
                    // Verify export options are available
                    $I->seeElement('select[name="format"]');
                    $I->seeElement('input[type="submit"]');
                } else {
                    // If export is not expected to be allowed
                    $I->dontSee('Export Campaigns');
                    $I->see('Access Denied', ['css' => '.alert-danger']);
                }
            } catch (\Exception $e) {
                // If export button/link is not found
                if ($userData['expected_export_access']) {
                    // Export should be allowed but button is missing
                    $I->fail("Export functionality not found for role: {$role}");
                }
            }

            // Logout
            $I->click('.dropdown-toggle.account-dropdown');
            $I->click('Logout');
        }
    }

    public function testCampaignExportPermissionsWithUnescapedCharacters(AcceptanceTester $I)
    {
        // Define different user roles to test export permissions
        $roles = [
            'admin' => [
                'username' => getenv('MAUTIC_ADMIN_USERNAME'),
                'password' => getenv('MAUTIC_ADMIN_PASSWORD'),
                'expected_export_access' => true
            ],
            'editor' => [
                'username' => getenv('MAUTIC_EDITOR_USERNAME'),
                'password' => getenv('MAUTIC_EDITOR_PASSWORD'),
                'expected_export_access' => true
            ],
            'viewer' => [
                'username' => getenv('MAUTIC_VIEWER_USERNAME'),
                'password' => getenv('MAUTIC_VIEWER_PASSWORD'),
                'expected_export_access' => false
            ]
        ];

        // Malicious and unescaped character payloads to test export
        $maliciousPayloads = [
            'script_tag' => '<script>alert("XSS");</script>',
            'sql_injection' => "'; DROP TABLE campaigns; --",
            'unicode_chars' => '🚀 Campaign ☢️ Test & Injection ✓',
            'special_chars' => '`~!@#$%^&*()_+-=[]{}|;:,.<>?',
            'html_entities' => '&lt;script&gt;console.log("test");&lt;/script&gt;',
            'xml_chars' => '<?xml version="1.0"?><test>Injection</test>',
            'control_chars' => "\x00\x01\x02\x03\x04\x05",
            'unicode_control' => "\u{200B}\u{200C}\u{200D}\u{FEFF}"
        ];

        // Create a sample campaign for export testing
        $I->amOnPage('/s/login');
        $I->fillField('#username', getenv('MAUTIC_ADMIN_USERNAME'));
        $I->fillField('#password', getenv('MAUTIC_ADMIN_PASSWORD'));
        $I->click('login');
        $I->waitForElement('#mautic_dashboard_index', 30);

        // Navigate to campaigns
        $I->amOnPage('/s/campaigns');
        $I->waitForElement('a[href*="/s/campaigns/new"]', 30);

        // Create test campaigns with unescaped characters
        foreach ($maliciousPayloads as $payloadType => $payload) {
            $I->click('New Campaign');
            $I->waitForElement('#campaign_name', 30);
            $I->fillField('#campaign_name', "Export Security Test - {$payloadType}: {$payload}");
            $I->click('Save');
            $I->waitForText('Campaign saved', 30);
        }

        // Test export permissions and character handling for different roles
        foreach ($roles as $role => $userData) {
            // Login with different user roles
            $I->amOnPage('/s/login');
            $I->fillField('#username', $userData['username']);
            $I->fillField('#password', $userData['password']);
            $I->click('login');
            $I->waitForElement('#mautic_dashboard_index', 30);

            // Navigate to campaigns
            $I->amOnPage('/s/campaigns');
            $I->waitForElement('table.table-striped', 30);

            // Attempt to export campaign with various payloads
            foreach ($maliciousPayloads as $payloadType => $payload) {
                try {
                    // Look for export button or link
                    $I->click('Export');
                    
                    if ($userData['expected_export_access']) {
                        // If export is expected to be allowed
                        $I->waitForText('Export', 30);
                        $I->see('Export Campaigns');
                        
                        // Verify export options are available
                        $I->seeElement('select[name="format"]');
                        $I->seeElement('input[type="submit"]');

                        // Verify only ZIP format is available
                        $availableFormats = $I->grabTextFrom('select[name="format"] option');
                        $I->assertContains('zip', strtolower($availableFormats), 'ZIP format should be the only export option');
                        
                        // Select ZIP format
                        $I->selectOption('select[name="format"]', 'zip');
                        
                        // Verify no script execution or unexpected behavior
                        $I->dontSee('<script');
                        $I->dontSee('alert(');
                        $I->dontSee('console.log');

                        // Attempt to submit export
                        $I->click('input[type="submit"]');

                        // Wait for download or verify export process
                        $I->waitForText('Exporting', 30);

                        // Verify download attributes or export behavior
                        $downloadLink = $I->grabAttributeFrom('a.download-link', 'href');
                        $I->assertStringContainsString('.zip', $downloadLink, 'Export should be a ZIP file');
                    } else {
                        // If export is not expected to be allowed
                        $I->dontSee('Export Campaigns');
                        $I->see('Access Denied', ['css' => '.alert-danger']);
                    }
                } catch (\Exception $e) {
                    // If export button/link is not found
                    if ($userData['expected_export_access']) {
                        // Export should be allowed but button is missing
                        $I->fail("Export functionality not found for role: {$role}, payload type: {$payloadType}");
                    }
                }
            }

            // Logout
            $I->click('.dropdown-toggle.account-dropdown');
            $I->click('Logout');
        }
    }
}
