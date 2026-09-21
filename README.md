# Docket Assistant

WordPress plugin that embeds a **Docket** website chat bubble on every public page.

Connects to a Docket desk host (local FastAPI desk or deployed URL). Paste your **desk URL** and **bot ID** under **Settings → Docket Assistant**.

Part of the [DocketAI](https://github.com/annamw3333-creator/DocketAI) stack (mystery-shop QA desk + mobile). Standalone plugin repo for easy WP upload.

## Install

1. Download the zip: [main.zip](https://github.com/annamw3333-creator/docket-assistant/archive/refs/heads/main.zip)  
   or use the copy under `wordpress-plugin/` in the DocketAI repo.
2. WordPress → **Plugins → Add Plugin → Upload Plugin** → activate.
3. **Settings → Docket Assistant** → set Docket URL + Bot ID → Save.

The chat bubble appears on public pages. Host allowlisting and security notes: see `SECURITY.md` / `readme.txt` in this repo.

## Requirements

- WordPress 6.0+
- PHP 7.4+
- A reachable Docket desk (`/widget/...` + bot id)

## License

GPLv2 or later (WordPress plugin norm) — see `license.txt`.
