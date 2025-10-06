from playwright.sync_api import sync_playwright
import os

def run(playwright):
    browser = playwright.chromium.launch()
    page = browser.new_page()

    projects = ["FastFinance", "FlashZaim", "Frida", "Gulden", "Hyacinth", "QuickMoney"]
    pages_to_check = ["index.html", "shareholders.html", "executive.html"]

    for project in projects:
        for page_name in pages_to_check:
            file_path = f"/app/{project}/{page_name}"
            if os.path.exists(file_path):
                page.goto(f"file://{file_path}")
                screenshot_path = f"jules-scratch/verification/{project}_{page_name.split('.')[0]}.png"
                page.screenshot(path=screenshot_path, full_page=True)
                print(f"Screenshot taken for {project}/{page_name}")
            else:
                print(f"Skipping {project}/{page_name} as it does not exist.")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)