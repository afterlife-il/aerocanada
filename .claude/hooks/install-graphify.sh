#!/bin/bash
# Installe graphifyy dans les sessions Claude Code cloud (conteneurs éphémères).
# Sur un poste local, installer une fois : pip install graphifyy && graphify install
[ "$CLAUDE_CODE_REMOTE" = "true" ] || exit 0
command -v graphify >/dev/null 2>&1 && exit 0
pip install -q graphifyy >/dev/null 2>&1 && graphify install --platform claude >/dev/null 2>&1
exit 0
