"""Ouvre chaque page dans l'éditeur de blocs et signale les blocs invalides.

Usage : python3 tests/editor_blocks.py   (site local lancé avec bin/serve.sh)
Prérequis : pip install playwright && python3 -m playwright install chromium
"""
import asyncio
import pathlib
import subprocess
import sys

from playwright.async_api import async_playwright

ROOT = pathlib.Path(__file__).resolve().parent.parent
URL = "http://127.0.0.1:8080"
WP = ["php", str(ROOT / "tools/wp-cli.phar"), f"--path={ROOT / '.local/wordpress'}", "--allow-root"]
INVALID = """() => { const out = []; const walk = (bs) => bs.forEach(b => {
  if (b.isValid === false) out.push(b.name + ' ' + (b.attributes.className || ''));
  walk(b.innerBlocks); });
  walk(wp.data.select('core/block-editor').getBlocks());
  return [wp.data.select('core/block-editor').getGlobalBlockCount(), out]; }"""


def pages():
    out = subprocess.run(WP + ["post", "list", "--post_type=page", "--fields=ID,post_name", "--format=csv"],
                         capture_output=True, text=True, check=True).stdout.strip().splitlines()[1:]
    return [line.split(",") for line in out]


async def main() -> int:
    password = (ROOT / ".local/admin.txt").read_text().split("Mot de passe : ")[1].strip()
    failed = 0
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        page = await browser.new_page(viewport={"width": 1400, "height": 900})
        await page.goto(f"{URL}/wp-login.php")
        await page.fill("#user_login", "admin")
        await page.fill("#user_pass", password)
        await page.click("#wp-submit")
        await page.wait_for_load_state("networkidle")
        for post_id, slug in pages():
            await page.goto(f"{URL}/wp-admin/post.php?post={post_id}&action=edit")
            await page.wait_for_function(
                "window.wp && wp.data && wp.data.select('core/block-editor')"
                " && wp.data.select('core/block-editor').getBlockCount() > 0", timeout=60000)
            await page.wait_for_timeout(1000)
            count, invalid = await page.evaluate(INVALID)
            failed += len(invalid)
            print(("RÉUSSI " if not invalid else "ÉCHEC  ") + f"{slug} : {count} blocs, {len(invalid)} invalide(s) {invalid or ''}")
        await browser.close()
    return 1 if failed else 0


if __name__ == "__main__":
    sys.exit(asyncio.run(main()))
