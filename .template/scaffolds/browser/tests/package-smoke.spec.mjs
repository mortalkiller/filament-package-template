import { expect, test } from '@playwright/test'

test('the workbench panel is reachable', async ({ page }) => {
    const response = await page.goto('/admin')

    expect(response?.ok()).toBeTruthy()
    await expect(page.locator('body')).toBeVisible()
})
