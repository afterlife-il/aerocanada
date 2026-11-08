# Claude Code tooling

Last updated: 2026-11-08

Repository-level Claude Code configuration lives in `.claude/settings.json` and
applies to everyone who opens this repository in Claude Code (local or cloud).

## Installed tools

| Tool | Source | How it is wired |
| --- | --- | --- |
| Ponytail 5.3.3 | `DietrichGebert/ponytail` (original repository, not a fork) | `extraKnownMarketplaces.ponytail` + `enabledPlugins["ponytail@ponytail"]` |
| Anthropic agent skills | `anthropics/skills` | marketplace `anthropic-agent-skills`; plugins `document-skills` and `example-skills` enabled |
| Graphify 0.9.75 | PyPI `graphifyy` (two "y") | `graphify claude install` output: graphify section in `CLAUDE.md`, PreToolUse hooks on `Bash|Grep` and `Read|Glob`, SessionStart `compact` hook |

Claude Code prompts each user once to trust the repository marketplaces before
installing the plugins.

### Graphify specifics

- Hook commands are guarded with `command -v graphify`, so machines without
  graphify are not broken; the guard was added on top of the generated config,
  which used the absolute path `/usr/local/bin/graphify`.
- `.claude/hooks/install-graphify.sh` runs on SessionStart and installs
  `graphifyy` only in cloud sessions (`CLAUDE_CODE_REMOTE=true`) when it is
  missing. On a workstation, install once: `pip install graphifyy && graphify install`.
- The `CLAUDE.md` rules only apply once `graphify-out/graph.json` exists. No
  graph has been built or committed yet; build it with `graphify update .`
  (AST only, no LLM cost) when wanted.

## Deliberately not installed

- **OmniRoute** (`npm install -g omniroute`): it points Claude Code at a local
  proxy through `ANTHROPIC_BASE_URL`, which can route code to non-Anthropic
  models and breaks tool calls with some models. Declined on 2026-11-08.

## Ponytail data handling

Ponytail's hooks write a per-task ledger to `~/.config/ponytail/band/` on the
user's machine. Its optional webhook is empty by default; leave it empty unless
a team board is approved, since it would send task rows off the machine.

## Scope

Only this repository (`afterlife-il/aerocanada`) was configured. To apply the
same setup to another repository, copy `.claude/settings.json`,
`.claude/hooks/install-graphify.sh` and the graphify section of `CLAUDE.md`.
