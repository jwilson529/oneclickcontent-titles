# Release Process

This repo ships WordPress.org releases through GitHub Actions. This is the release pattern to copy into other plugin repos that use the same packaging setup.

## Release Contract

- `npm run dist` must build a runtime-only plugin directory at `dist/oneclickcontent-titles/`.
- `npm run dist` must also produce an installable zip at `dist/oneclickcontent-titles.zip`.
- `.github/workflows/plugin-check.yaml` validates the plugin on `push`, `pull_request`, and manual runs.
- The same workflow deploys to WordPress.org only when a GitHub Release is published.
- WordPress.org deploy uses `10up/action-wordpress-plugin-deploy` with:
  - `SLUG=oneclickcontent-titles`
  - `BUILD_DIR=dist/oneclickcontent-titles`
  - `ASSETS_DIR=assets`
- `assets/` stays in the repository for wordpress.org directory artwork and is not bundled into the install zip.
- GitHub repo secrets required for deploy:
  - `WPORG_USERNAME`
  - `WPORG_PASSWORD`

## Standard Release Steps

1. Finalize the release commit on `main`.
   - Include version bumps, `readme.txt`, `README.md`, `MARKETING_PLAN.md`, asset changes, and any workflow fixes before tagging.
   - Confirm the WordPress.org screenshots match the `readme.txt` captions exactly.
   - Recommended screenshot sequence is editor results first, settings second, generation workflow third.
2. Run the local release gate on that exact commit.
   - `npm run check`
   - `npm test`
   - `npm run dist`
3. Verify the build output shape.
   - `dist/oneclickcontent-titles/` must be the plugin root.
   - `dist/oneclickcontent-titles.zip` must install with a top-level `oneclickcontent-titles/` folder.
   - The zip must contain `oneclickcontent-titles/oneclickcontent-titles.php` and `oneclickcontent-titles/readme.txt`.
4. Push `main`.
   - The tagged commit and default branch should match.
   - Check the `push` workflow on `main` before trusting the published release run. It usually fails faster and exposes the same validation problems.
5. Create and push an annotated version tag.
   - Example: `git tag -a v2.1.0 -m "Release v2.1.0"`
   - Example: `git push origin v2.1.0`
6. Publish the GitHub Release from that tag.
   - The release workflow will re-run validation, deploy to wp.org SVN, and attach the generated zip to the GitHub Release.
   - Before publishing, confirm the repo actually has `WPORG_USERNAME` and `WPORG_PASSWORD` configured in GitHub Actions secrets.
7. Verify the release outputs.
   - Confirm the GitHub Actions release run succeeded.
   - Confirm the GitHub Release includes the attached zip.
   - Confirm wp.org SVN deploy succeeded.
8. Check the public wp.org page last.
   - The directory page can lag behind a successful SVN deploy.

## WordPress.org Screenshot Release Gate

Before publishing the release, confirm:

- `assets/screenshot-1.png` shows the post editor title generation workflow.
- `assets/screenshot-2.png` shows `options-general.php?page=occ_titles-settings`.
- `assets/screenshot-3.png` shows the in-editor generation/loading workflow.
- Optional `assets/screenshot-4.png` and `assets/screenshot-5.png` are included only if they are real, verified plugin UI.
- No screenshot includes browser chrome, WordPress admin chrome, mockups, marketing overlays, or unverified features.
- No visible API key or private site data appears in a screenshot.
- The `== Screenshots ==` section in `readme.txt` matches the final asset order.

## Failure Recovery

- If the workflow is wrong, fix it on `main` first. Release workflows run from the tagged commit, not from the current default branch state.
- If the tag points at the wrong commit, move the tag to the corrected commit and recreate the GitHub Release so the correct workflow file runs.
- If GitHub Actions fails on Composer platform requirements, update the workflow PHP version to satisfy `composer.lock`, then retag.
- If GitHub Actions cannot find `phpcs`, `phpcbf`, or `phpmd`, update the npm scripts to use the repo-local Composer binaries in `vendor/bin/` instead of assuming global installs on the runner.
- If deploy fails because of build structure, fix the `npm run dist` contract or `BUILD_DIR`. Do not add one-off unzip steps to compensate.
- If deploy fails with empty `SVN_USERNAME` or `SVN_PASSWORD`, stop and add the missing `WPORG_USERNAME` / `WPORG_PASSWORD` GitHub secrets before retrying the release.
- If SVN deploy succeeds but wordpress.org still looks stale, treat that as propagation delay unless the workflow logs show an actual SVN failure.

## Release Lessons

- Keep release commits focused. Leave unrelated scratch notes or planning docs out of the tagged commit.
- Make the workflow runtime match the lockfile. If `composer.lock` needs PHP `8.4`, the GitHub Actions PHP step must also use `8.4`.
- Make QA scripts self-contained. `npm run check`, `npm run fix`, and `npm run phpmd` should call `vendor/bin/...` so CI behaves like the repo, not like the machine.
- Use the `push` run as the fast canary. After pushing `main`, check the newest `CI and Release` run for the `push` event before waiting on the `release` event.
- Confirm release secrets before publishing. The deploy job will not work unless `WPORG_USERNAME` and `WPORG_PASSWORD` exist in the repo secrets.
- GitHub repo secrets are write-only. You can confirm that another repo has `WPORG_USERNAME` / `WPORG_PASSWORD`, but you cannot read the stored values back out and copy them automatically.
- If a release is published from a bad tag, the clean recovery path is:
  1. Fix `main`.
  2. Push `main`.
  3. Delete the GitHub Release with tag cleanup.
  4. Recreate the annotated tag on the corrected commit.
  5. Re-publish the GitHub Release.
- Verify the workflow from the command line when moving quickly:
  - `gh run list --workflow "CI and Release" --limit 5`
  - `gh run view <run_id> --log-failed`
- The current workflow is still carrying GitHub Actions Node 20 deprecation warnings from `actions/checkout@v4`, `actions/setup-node@v4`, `actions/upload-artifact@v4`, and `softprops/action-gh-release@v2`. The release passed, but those actions should be reviewed before the Node 24 enforcement window.

## Notes

- The release workflow assumes `assets/` contains the WordPress.org asset set to publish alongside the plugin code.
- The GitHub Release is expected to be published from a tag that already contains the final workflow and build script state for that version.
