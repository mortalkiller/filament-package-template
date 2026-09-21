#!/usr/bin/env bash

set -euo pipefail

usage() {
    cat <<'EOF'
Usage:
  bash install.sh <skills-directory> [--force]

Examples:
  bash install.sh ~/.agents/skills
  bash install.sh ~/.claude/skills
  bash install.sh ~/.agents/skills --force
EOF
}

if [[ $# -lt 1 ]]; then
    usage >&2
    exit 2
fi

target_root="$1"
shift

force=false

while [[ $# -gt 0 ]]; do
    case "$1" in
        --force)
            force=true
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            echo "Unknown option: $1" >&2
            usage >&2
            exit 2
            ;;
    esac

    shift
done

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
destination="${target_root%/}/developing-filament-packages"
temporary="${target_root%/}/.developing-filament-packages.tmp.$$"

mkdir -p "$target_root"

if [[ ! -d "$target_root" || ! -w "$target_root" ]]; then
    echo "Skills directory is not writable: $target_root" >&2
    exit 1
fi

if [[ -e "$destination" && "$force" != true ]]; then
    echo "Skill already exists at $destination. Re-run with --force to replace it." >&2
    exit 1
fi

cleanup() {
    rm -rf "$temporary"
}

trap cleanup EXIT

rm -rf "$temporary"
mkdir -p "$temporary/references"

cp "$script_dir/SKILL.md" "$temporary/SKILL.md"
cp "$script_dir"/references/*.md "$temporary/references/"

if [[ "$force" == true ]]; then
    rm -rf "$destination"
fi

mv "$temporary" "$destination"
trap - EXIT

echo "Installed developing-filament-packages to $destination"
