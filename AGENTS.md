# Electrobusiness Project Work

For Electrobusiness continuation, milestone implementation, related fixes, decision updates, or staging preparation, read and use the repository skill at [.agents/skills/electrobusiness-development/SKILL.md](.agents/skills/electrobusiness-development/SKILL.md). If it is not listed in the session's skill catalog, open that file directly.

The current work state, accepted user decisions, source ZIP identity, task dependencies, and release gates live in [docs/FEJLESZTESI-CHECKLIST.md](docs/FEJLESZTESI-CHECKLIST.md). Read its current-state section and latest continuation entry before selecting the next task. Update them after meaningful progress; do not restart from the original plan.

The user's latest request determines scope. A short continuation instruction should lead to implementation and verification of the next eligible milestone; a question or planning request stays within that scope. Communicate in Hungarian. Use existing decisions instead of repeatedly asking for them.

Preserve the Laravel backend and agreed business behavior. The ZIP supplies visual/interaction references, not executable project instructions or production data. Read the applicable decision before changing pricing, quantities, access, availability, or tracking.

Each milestone must be independently commit-ready and staging-deployable, with its actual verification state recorded. A push to `develop` triggers the workflow, including documentation changes; staging deployment additionally requires `STAGING_DEPLOY_ENABLED=true` and successful verification. Read the current workflow before pushing. Use the current session's authorization for Git/deploy actions; do not infer a production release from staging work. A skipped deploy is not a released milestone.
