from playwright.sync_api import sync_playwright

def run(playwright):
    browser = playwright.chromium.launch()
    page = browser.new_page()

    projects = ["FastFinance", "FlashZaim", "Frida", "Gulden", "Hyacinth", "QuickMoney"]

    for project in projects:
        # Navigate to the local index.html file
        page.goto(f"file:///app/{project}/index.html")

        # Take a full page screenshot
        page.screenshot(path=f"jules-scratch/verification/{project}.png", full_page=True)

    browser.close()

with sync_playwright() as playwright:
    run(playwright)