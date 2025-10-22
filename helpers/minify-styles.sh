#!/bin/bash
set -e

# Define directories relative to this script's location
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SRC_DIR="$SCRIPT_DIR/../styles"
DEST_DIR="$SRC_DIR/min"

mkdir -p "$DEST_DIR"

echo "Minifying CSS files in: $SRC_DIR"
echo "Output directory: $DEST_DIR"
echo

# Loop through all CSS files in styles (but not min/)
for f in "$SRC_DIR"/*.css; do
    filename=$(basename "$f" .css)
    out_file="$DEST_DIR/$filename.min.css"
    echo "Minifying: $filename.css → min/$filename.min.css"
    minify "$f" -o "$out_file"
done

echo
echo "✅ Minification complete."
