from playwright.sync_api import sync_playwright
import os

PAGES = [
    ("index", "http://127.0.0.1:8000/index.html"),
    ("portal", "http://127.0.0.1:8000/portal.html"),
]
VIEWPORTS = [
    (375, 812, "mobile"),
    (768, 1024, "tablet"),
    (1280, 800, "desktop"),
]
OUTPUT_DIR = os.path.join(os.path.dirname(__file__), "..", "screenshots")
os.makedirs(OUTPUT_DIR, exist_ok=True)

import subprocess
import time
import socket

# Start a temporary HTTP server to serve the site for screenshots
print("Starting temporary HTTP server on port 8000...")
server_proc = subprocess.Popen(["python", "-m", "http.server", "8000"], cwd=os.path.join(os.path.dirname(__file__), ".."), stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)

# wait until the server is up (simple TCP check)
for i in range(20):
    s = socket.socket()
    try:
        s.connect(("127.0.0.1", 8000))
        s.close()
        break
    except Exception:
        time.sleep(0.5)
else:
    server_proc.terminate()
    raise SystemExit("Failed to start local server on port 8000")

with sync_playwright() as p:
    browser = p.chromium.launch()
    for name, url in PAGES:
        for w, h, label in VIEWPORTS:
            context = browser.new_context(viewport={"width": w, "height": h})
            page = context.new_page()
            print(f"Opening {url} @ {w}x{h}")
            # retry loop to handle transient server start-up
            success = False
            attempts = 0
            while not success and attempts < 6:
                try:
                    page.goto(url, wait_until="networkidle", timeout=10000)
                    success = True
                except Exception as e:
                    attempts += 1
                    print(f"Connection failed (attempt {attempts}/6): {e}. Retrying in 1s...")
                    page.wait_for_timeout(1000)
            if not success:
                print(f"Failed to open {url} after retries, skipping.")
                context.close()
                continue
            # wait briefly for animations
            page.wait_for_timeout(800)
            filename = f"{name}-{label}-{w}x{h}.png"
            out_path = os.path.join(OUTPUT_DIR, filename)
            page.screenshot(path=out_path, full_page=True)
            print(f"Saved {out_path}")
            context.close()
    browser.close()

# Stop the temporary server
server_proc.terminate()
print("Screenshots complete.")