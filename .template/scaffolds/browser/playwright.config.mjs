import { defineConfig } from '@playwright/test'

export default defineConfig({
    testDir: './tests/Browser',
    use: {
        baseURL: 'http://127.0.0.1:8000',
    },
    webServer: {
        command: 'php vendor/bin/testbench serve --host=127.0.0.1 --port=8000',
        url: 'http://127.0.0.1:8000/admin',
        reuseExistingServer: !process.env.CI,
        timeout: 120000,
    },
})
