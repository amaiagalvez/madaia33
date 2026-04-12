#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
CHECK_ONLY=0

if [[ "${1:-}" == "--check" ]]; then
    CHECK_ONLY=1
fi

# Restrict processing to common project text files and avoid third-party/generated folders.
mapfile -d '' FILES < <(
    find "$ROOT_DIR" \
        -type f \
        \( \
            -name '*.php' -o -name '*.blade.php' -o -name '*.js' -o -name '*.ts' \
            -o -name '*.css' -o -name '*.scss' -o -name '*.html' -o -name '*.md' \
            -o -name '*.yml' -o -name '*.yaml' -o -name '*.json' -o -name '*.xml' \
            -o -name '*.sh' \
        \) \
        -not -path '*/vendor/*' \
        -not -path '*/node_modules/*' \
        -not -path '*/storage/*' \
        -not -path '*/bootstrap/cache/*' \
        -not -path '*/public/build/*' \
        -not -path '*/dc-data/*' \
        -not -path '*/.git/*' \
        -not -path '*/.github/workflows/*' \
        -print0
)

checked=0
changed=0

for file in "${FILES[@]}"; do
    checked=$((checked + 1))

    temp_file="$(mktemp)"

    perl -pe '
        # Normalize CRLF/CR to LF line endings.
        s/\r$//;

        # Keep PHPDoc/JSDoc block indentation untouched.
        our $in_doc_block;
        if (/^[ \t]*\/\*\*/) {
            $in_doc_block = 1;
        }

        # Force indentation widths to multiples of 4 spaces.
        if (!$in_doc_block && /^([ \t]+)/) {
            my $leading = $1;
            my $width = 0;

            foreach my $char (split //, $leading) {
                $width += ($char eq "\t") ? 4 : 1;
            }

            my $target = int(($width + 3) / 4) * 4;
            s/^[ \t]+/" " x $target/e;
        }

        if ($in_doc_block && /\*\//) {
            $in_doc_block = 0;
        }
    ' "$file" > "$temp_file"

    # Ensure non-empty files always end with LF.
    if [[ -s "$temp_file" ]] && [[ -n "$(tail -c1 "$temp_file")" ]]; then
        printf '\n' >> "$temp_file"
    fi

    if ! cmp -s "$file" "$temp_file"; then
        changed=$((changed + 1))
        echo "Adjusted: ${file#$ROOT_DIR/}"

        if [[ "$CHECK_ONLY" -eq 0 ]]; then
            cat "$temp_file" > "$file"
        fi
    fi

    rm -f "$temp_file"
done

if [[ "$CHECK_ONLY" -eq 1 ]]; then
    if [[ "$changed" -gt 0 ]]; then
        echo
        echo "Found $changed file(s) with non-4-space indentation out of $checked checked."
        exit 1
    fi

    echo "All good. $checked file(s) checked, all already aligned to 4-space indentation."
    exit 0
fi

echo
echo "Done. Updated $changed file(s) out of $checked checked."

echo
echo "Running Pint via Docker..."
docker compose run --rm --user "${DC_UID:-1000}:${DC_GID:-1000}" madaia33 vendor/bin/pint --dirty
