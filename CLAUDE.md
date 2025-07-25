# Claude Code Instructions

## Version Bumping and Commit Process

**IMPORTANT**: Always bump the version in `config.yaml` AND create a commit when making any changes to the Home Assistant addon.

### When to bump version:
- Any code changes (PHP, JavaScript, CSS)
- Configuration file changes
- Dockerfile modifications
- Dependencies updates
- Bug fixes
- New features

### Version Bump and Commit Process:
1. Make your code changes
2. Open `/ha-addon/config.yaml`
3. Increment the version number following semantic versioning:
   - MAJOR.MINOR.PATCH (e.g., 1.0.2 → 1.0.3)
   - Patch: Bug fixes, minor changes
   - Minor: New features, backwards compatible
   - Major: Breaking changes
4. Create a git commit with all changes including the version bump
5. Use a descriptive commit message that includes the version number

### Example:
```yaml
version: "1.0.3"  # Increment this on every change
```

### Commit Message Format:
```
feat: Add sync button to navigation (v1.0.3)

- Added Home Assistant sync button to top nav
- Fixed PHP-FPM configuration
- Updated logging to output to stderr
```

This ensures Home Assistant properly updates the addon when changes are made and maintains a clear history of changes.