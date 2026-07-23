// @ts-check
const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
    testDir: 'wordpress-docker/password-app/tests/ui',
    use: {
        baseURL: process.env.BASE_URL || 'http://localhost:8081',
    },
});
