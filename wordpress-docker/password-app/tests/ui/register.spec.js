const { test, expect } = require('@playwright/test');

test('home page shows the login form', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('h1')).toHaveText('Login');
    await expect(page.locator('input[name="username"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
});

test('registering with a common password stays on the home page with an error', async ({ page }) => {
    await page.goto('/register.php');
    await page.fill('#username', 'ui_common_test');
    await page.fill('#password', 'password1');
    await page.click('button[type=submit]');

    await expect(page).toHaveURL(/index\.php/);
    await expect(page.locator('body')).toContainText(/commonly used/i);
});

test('registering with a strong password reaches the welcome page', async ({ page }) => {
    const username = 'ui_ok_' + Date.now();

    await page.goto('/register.php');
    await page.fill('#username', username);
    await page.fill('#password', 'Xk4$vQwPzRn8');
    await page.click('button[type=submit]');

    await expect(page).toHaveURL(/welcome\.php/);
    await expect(page.locator('h1')).toContainText(username);

    await page.click('text=Logout');
    await expect(page).toHaveURL(/index\.php|\/$/);
});
