# Initializing a new package

Generate a repository from this template, start on `1.x`, and configure `1.x` as the default GitHub branch. Do not create a separate promotion branch.

Run `.template/initialize-package.php` with `--slug`, `--title`, `--namespace`, `--description` and `--workflow-ref`. The workflow reference is a reviewed full 40-character commit SHA from the canonical template. Use a commit containing all reusable workflows referenced by this template, including release documentation. Do not use the generated repository's unrelated initial commit as the template reference.

The initializer changes package identity, converts relative workflow calls into SHA-pinned calls, pins the checker/publication tools with `standard-ref`, and removes template-only tooling, including the template's maintainer-only Laravel Boost skill. A generated package can later add its own consumer-facing skill under `resources/boost/skills/<skill-name>/` when appropriate. It rejects invalid input and unsafe reinitialization, and can resume an interrupted initialization only when the original arguments match.

After initialization, run package CI and configure branch protection, release-tag/environment policies and deployment variables. Documentation remains unpublished until a stable release is explicitly published with deployment enabled. No secrets belong in this repository.
