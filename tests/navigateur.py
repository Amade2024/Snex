"""Vérifie dans un vrai navigateur la politique de sécurité du contenu (CSP) et la protection contre la copie.

- aucune ressource légitime bloquée par la CSP, en visiteur comme en administrateur connecté ;
- un script injecté dans la page (attaque XSS) n'est pas exécuté ;
- menu mobile et formulaire de devis fonctionnent toujours ;
- clic droit, glisser-déposer, sélection et copie désactivés (sauf dans les champs de formulaire).

Usage : python3 tests/navigateur.py   (site local lancé avec bin/serve.sh)
"""
import asyncio
import pathlib
import sys

from playwright.async_api import async_playwright

ROOT = pathlib.Path(__file__).resolve().parent.parent
URL = "http://127.0.0.1:8080"
PAGES = ["/", "/telecommunications/", "/energie/", "/photovoltaique/", "/a-propos/", "/contact/",
         "/demande-de-devis/", "/mentions-legales/", "/page-inexistante/"]
INJECT = """() => { const s = document.createElement('script'); s.textContent = 'window.__xss = 1';
  document.body.append(s); return window.__xss === 1; }"""
EVENTS = """(selector) => { const el = document.querySelector(selector); if (!el) return null;
  return ['contextmenu', 'dragstart', 'selectstart', 'copy', 'cut'].map(t => {
  const e = new Event(t, {bubbles: true, cancelable: true}); el.dispatchEvent(e); return e.defaultPrevented; }); }"""

results = {}


def check(label, ok):
    results[label] = bool(ok)


def watch(page, violations):
    page.on("console", lambda m: "Content Security Policy" in m.text and violations.append(m.text))
    page.on("pageerror", lambda e: violations.append(str(e)))


async def main() -> int:
    async with async_playwright() as p:
        browser = await p.chromium.launch()

        # Visiteur sur mobile.
        page = await browser.new_page(viewport={"width": 390, "height": 844})
        for path in PAGES:
            violations = []
            watch(page, violations)
            await page.goto(URL + path, wait_until="networkidle")
            check(f"{path} : aucune ressource bloquée par la CSP", not violations)
            if path == "/":
                check("Script injecté bloqué par la CSP", not await page.evaluate(INJECT))
                check("Clic droit, glisser-déposer et copie désactivés sur les images",
                      await page.evaluate(EVENTS, "main img") == [True] * 5)
                check("Sélection et copie du texte désactivées", await page.evaluate(EVENTS, "main p") == [True] * 5)
                check("Texte non sélectionnable (CSS)",
                      await page.evaluate("getComputedStyle(document.querySelector('main p')).userSelect") == "none")
                await page.get_by_role("button", name="Menu").first.click()
                await page.wait_for_timeout(400)
                check("Menu mobile ouvert", await page.locator(".wp-block-navigation__responsive-container.is-menu-open").count() == 1)
            if path == "/demande-de-devis/":
                check("Champs du devis : sélection et copier-coller possibles",
                      await page.evaluate(EVENTS, "main input:not([type=hidden])") == [False] * 5)
                await page.locator(".devis__next").first.click()
                check("Formulaire de devis : vérification JavaScript active",
                      await page.locator("#devis-err-nom").inner_text() != "")
            await page.close()
            page = await browser.new_page(viewport={"width": 390, "height": 844})

        # Administrateur connecté (barre d'administration).
        password = (ROOT / ".local/admin.txt").read_text().split("Mot de passe : ")[1].strip()
        admin = await browser.new_page(viewport={"width": 1280, "height": 900})
        await admin.goto(f"{URL}/wp-login.php")
        await admin.fill("#user_login", "admin")
        await admin.fill("#user_pass", password)
        await admin.click("#wp-submit")
        await admin.wait_for_load_state("networkidle")
        violations = []
        watch(admin, violations)
        await admin.goto(URL + "/energie/", wait_until="networkidle")
        check("Administrateur connecté : aucune ressource bloquée par la CSP", not violations)
        check("Administrateur connecté : copie possible", await admin.evaluate(EVENTS, "main p") == [False] * 5)
        await browser.close()

    for label, ok in results.items():
        print(("RÉUSSI  " if ok else "ÉCHEC   ") + label)
    failed = sum(not ok for ok in results.values())
    print(f"{failed} test(s) en échec." if failed else f"{len(results)} tests réussis.")
    return 1 if failed else 0


if __name__ == "__main__":
    sys.exit(asyncio.run(main()))
