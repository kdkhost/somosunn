#!/bin/bash
cd /home/somosunn/public_html/public/assets-dflip

echo "=== createCover function ==="
grep -oP 'createCover[^}]{0,500}' js/dflip.min.js | head -5

echo ""
echo "=== cover usage ==="
grep -oE 'cover[^a-zA-Z][^,}]{0,60}' js/dflip.min.js | sort -u | head -20

echo ""
echo "=== hardConfig ==="
grep -oE 'hardConfig[^,}]{0,100}' js/dflip.min.js | head -10

echo ""
echo "=== hard=== and hard== checks ==="
grep -oE 'hard=="[^"]+"|hard==="[^"]+"' js/dflip.min.js | sort -u
