const { test, expect } = require('@playwright/test');

test('Login and export a campaign', async ({ page }) => {
    // Navigate to the login page
    await page.goto('https://8080-levente999-mautic-jzzhvw5dhnc.ws-eu118.gitpod.io/s/login');

    // Fill in the login form
    await page.fill('input[name="email"]', 'admin'); // Replace with the actual selector for the email input
    await page.fill('input[name="password"]', 'Maut1cR0cks!'); // Replace with the actual selector for the password input

    // Click the login button
    await page.click('button[type="form-loginbtn"]'); // Replace with the actual selector for the login button

    // Wait for navigation after login
    await page.waitForNavigation();

    // Navigate to the campaigns page
    await page.goto('https://8080-levente999-mautic-jzzhvw5dhnc.ws-eu118.gitpod.io/s/campaigns/1');

    // Wait for the campaigns list to load
    await page.waitForSelector('campaignTable'); // Replace with the actual selector for the campaign list

    // Select a campaign (replace '.campaign-item' with the actual selector for the campaign)
    await page.click('core-options'); // Click on the first campaign item

    // Wait for the export button to be visible
    await page.waitForSelector('Export'); // Replace with the actual selector for the export button

    // Click the export button
    await page.click('/s/campaigns/export/1?tmpl=list');

    // Start the download by clicking the download link/button
    const [download] = await Promise.all([
        page.waitForEvent('download'), // Wait for the download event
    ]);

    // Get the path of the downloaded file
    const path = await download.path();

    // Optionally, you can add assertions here to verify the export action
    const successMessage =  console.log('That worked!');; // Replace with the actual text
    expect(await successMessage.isVisible()).toBe(true);
});
