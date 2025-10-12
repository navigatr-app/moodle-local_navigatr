# Navigatr Plugin Scripts

This directory contains automation scripts for the Navigatr Moodle plugin.

## Release Script

### `release.sh`

Automates the entire release process for the Navigatr plugin.

#### Usage

```bash
./scripts/release.sh <version> [release_type]
```

#### Examples

```bash
# Create a major release (1.0.0)
./scripts/release.sh 1.0.0 major

# Create a minor release (1.1.0)
./scripts/release.sh 1.1.0 minor

# Create a patch release (1.1.1)
./scripts/release.sh 1.1.1 patch
```

#### What the script does

1. **Validates inputs** - Checks version format and current branch
2. **Updates version.php** - Sets the new version number
3. **Updates CHANGES.md** - Adds new version entry with date
4. **Creates release branch** - `release/<version>`
5. **Commits changes** - With descriptive commit message
6. **Pushes to origin** - Makes the release branch available remotely
7. **Updates develop** - Bumps develop branch to next dev version

#### Prerequisites

- Must be on the `develop` branch
- Working directory must be clean (no uncommitted changes)
- Must have push access to the remote repository

#### Safety Features

- **Confirmation prompt** - Asks before proceeding
- **Branch validation** - Ensures you're on the correct branch
- **Clean working directory check** - Prevents accidental commits
- **Version format validation** - Ensures semantic versioning
- **Error handling** - Stops on any error with clear messages

#### After running the script

1. **Create GitHub release** - Go to GitHub and create a release from the new branch
2. **Test with clients** - Use the release branch for testing
3. **Merge to main** - When ready, merge the release branch to your main branch

#### Example workflow

```bash
# 1. Make sure you're on develop and everything is committed
git checkout develop
git status

# 2. Run the release script
./scripts/release.sh 1.0.0 major

# 3. Go to GitHub and create a release from release/1.0.0 branch
# 4. Test the release
# 5. When satisfied, merge release/1.0.0 to your main branch
```

#### Troubleshooting

- **"You must be on the 'develop' branch"** - Run `git checkout develop`
- **"Working directory is not clean"** - Commit or stash your changes first
- **"Invalid version format"** - Use semantic versioning (e.g., 1.0.0, 1.1.0)
- **Permission denied** - Make sure the script is executable: `chmod +x scripts/release.sh`
