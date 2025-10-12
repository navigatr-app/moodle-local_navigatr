#!/bin/bash

# Navigatr Plugin Release Script
# Usage: ./scripts/release.sh <version> [release_type]
# Example: ./scripts/release.sh 1.0.0 major
# Example: ./scripts/release.sh 1.1.0 minor
# Example: ./scripts/release.sh 1.1.1 patch

set -e  # Exit on any error

# Colours for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Colour

# Function to print coloured output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if version is provided
if [ $# -eq 0 ]; then
    print_error "Version number is required!"
    echo "Usage: $0 <version> [release_type]"
    echo "Example: $0 1.0.0 major"
    echo "Example: $0 1.1.0 minor"
    echo "Example: $0 1.1.1 patch"
    exit 1
fi

NEW_VERSION=$1
RELEASE_TYPE=${2:-"release"}

# Validate version format (basic semantic versioning check)
if ! [[ $NEW_VERSION =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    print_error "Invalid version format. Use semantic versioning (e.g., 1.0.0, 1.1.0, 1.1.1)"
    exit 1
fi

# Check for GitHub CLI
if ! command -v gh &> /dev/null; then
    print_error "GitHub CLI (gh) is not installed. Please install it to create GitHub releases."
    print_error "See: https://cli.github.com/"
    exit 1
fi

# Check if authenticated with GitHub
if ! gh auth status &> /dev/null; then
    print_error "Not authenticated with GitHub CLI. Please run: gh auth login"
    exit 1
fi

# Get current version from version.php
CURRENT_VERSION=$(grep "plugin->release" version.php | sed "s/.*'\(.*\)';/\1/")
print_status "Current version: $CURRENT_VERSION"
print_status "New version: $NEW_VERSION"

# Check if we're on develop branch
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "develop" ]; then
    print_error "You must be on the 'develop' branch to create a release!"
    print_status "Current branch: $CURRENT_BRANCH"
    print_status "Please run: git checkout develop"
    exit 1
fi

# Check if working directory is clean
if ! git diff-index --quiet HEAD --; then
    print_error "Working directory is not clean! Please commit or stash your changes."
    git status --short
    exit 1
fi

# Generate changes summary from commit history
print_status "Generating changes summary from commit history..."
LAST_TAG=$(git describe --tags --abbrev=0 2>/dev/null || echo "")
CHANGES_SUMMARY=""

if [ -z "$LAST_TAG" ]; then
    # First release - get all commits
    COMMIT_COUNT=$(git rev-list --count HEAD)
    COMMIT_RANGE="HEAD"
    CHANGES_SUMMARY=$(git log --pretty=format:"- %s (%h)" --no-merges)
    if [ -z "$CHANGES_SUMMARY" ]; then
        CHANGES_SUMMARY="Initial release of Navigatr plugin"
    fi
else
    # Get commits since last tag
    COMMIT_COUNT=$(git rev-list --count ${LAST_TAG}..HEAD)
    COMMIT_RANGE="${LAST_TAG}..HEAD"
    CHANGES_SUMMARY=$(git log --pretty=format:"- %s (%h)" --no-merges ${LAST_TAG}..HEAD)
    if [ -z "$CHANGES_SUMMARY" ]; then
        CHANGES_SUMMARY="Release ${NEW_VERSION} (no new commits since ${LAST_TAG})"
    fi
fi

# Create detailed release notes
RELEASE_NOTES="## Changes in this release

**Commit Count:** ${COMMIT_COUNT} commits
**Commit Range:** ${COMMIT_RANGE}

${CHANGES_SUMMARY}"

# Confirm release
echo
print_warning "About to create release $NEW_VERSION"
echo "This will:"
echo "  1. Update version.php to $NEW_VERSION"
echo "  2. Update CHANGES.md with new version entry"
echo "  3. Create release branch: release/$NEW_VERSION"
echo "  4. Create Git tag: $NEW_VERSION"
echo "  5. Create GitHub release with changes summary"
echo "  6. Update develop branch to next dev version"
echo
echo "Changes summary:"
echo "$CHANGES_SUMMARY"
echo
read -p "Continue? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_status "Release cancelled."
    exit 0
fi

print_status "Starting release process for version $NEW_VERSION..."

# Step 1: Update version.php
print_status "Updating version.php..."
sed -i.bak "s/\$plugin->release   = '.*';/\$plugin->release   = '$NEW_VERSION';/" version.php
rm version.php.bak
print_success "Updated version.php to $NEW_VERSION"

# Step 2: Update CHANGES.md
print_status "Updating CHANGES.md..."
TODAY=$(date +%Y-%m-%d)

# Create new changelog entry with changes summary
cat > changelog_entry.tmp << EOF
## [$NEW_VERSION] - $TODAY

### Changes in this release
**Commit Count:** ${COMMIT_COUNT} commits
**Commit Range:** ${COMMIT_RANGE}

${CHANGES_SUMMARY}

EOF

# Insert new entry after the header
if [ -f CHANGES.md ]; then
    # Find the line number after the header (after the second ##)
    INSERT_LINE=$(awk '/^## \[/ {if(++count==1) print NR; exit}' CHANGES.md)
    if [ -n "$INSERT_LINE" ]; then
        # Insert the new entry
        head -n $((INSERT_LINE-1)) CHANGES.md > CHANGES.md.tmp
        cat changelog_entry.tmp >> CHANGES.md.tmp
        tail -n +$INSERT_LINE CHANGES.md >> CHANGES.md.tmp
        mv CHANGES.md.tmp CHANGES.md
    else
        # If no existing entries, append to end
        cat changelog_entry.tmp >> CHANGES.md
    fi
    rm changelog_entry.tmp
    print_success "Updated CHANGES.md"
else
    print_warning "CHANGES.md not found, skipping changelog update"
fi

# Step 3: Create release branch
print_status "Creating release branch: release/$NEW_VERSION"
git checkout -b "release/$NEW_VERSION"
print_success "Created and switched to release/$NEW_VERSION branch"

# Step 4: Commit changes
print_status "Committing changes..."
git add version.php CHANGES.md
git commit -m "Release $NEW_VERSION

- Updated version to $NEW_VERSION
- Updated changes with release notes
- Created release branch for $NEW_VERSION"
print_success "Committed changes"

# Step 5: Push branch to origin
print_status "Pushing release branch to origin..."
git push -u origin "release/$NEW_VERSION"
print_success "Pushed release/$NEW_VERSION to origin"

# Step 6: Create Git tag
print_status "Creating Git tag: $NEW_VERSION"
git tag -a "$NEW_VERSION" -m "Release $NEW_VERSION"
print_success "Created Git tag: $NEW_VERSION"

# Step 7: Push tag to origin
print_status "Pushing Git tag to origin..."
git push origin "$NEW_VERSION"
print_success "Pushed Git tag to origin"

# Step 8: Create GitHub release
print_status "Creating GitHub release..."
# Determine if it's a pre-release (version starts with 0.)
PRERELEASE_FLAG=""
if [[ $NEW_VERSION =~ ^0\. ]]; then
    PRERELEASE_FLAG="--prerelease"
    print_status "Version $NEW_VERSION is a pre-release, marking GitHub release as pre-release"
fi

gh release create "$NEW_VERSION" \
    --title "Release $NEW_VERSION" \
    --notes "$RELEASE_NOTES" \
    --target "release/$NEW_VERSION" \
    $PRERELEASE_FLAG

print_success "Created GitHub release: $NEW_VERSION"

# Step 9: Switch back to develop
print_status "Switching back to develop branch..."
git checkout develop
print_success "Switched back to develop branch"

# Step 10: Update develop branch version for next development
print_status "Updating develop branch for next development..."
NEXT_VERSION=$(echo $NEW_VERSION | awk -F. '{print $1"."$2"."($3+1)}')
sed -i.bak "s/\$plugin->release   = '.*';/\$plugin->release   = '$NEXT_VERSION-dev';/" version.php
rm version.php.bak
git add version.php
git commit -m "Bump version to $NEXT_VERSION-dev for next development cycle"
git push origin develop
print_success "Updated develop branch to $NEXT_VERSION-dev"

# Final summary
echo
print_success "Release $NEW_VERSION created successfully!"
echo
echo "What was done:"
echo "  ✅ Updated version.php to $NEW_VERSION"
echo "  ✅ Updated CHANGES.md with release notes"
echo "  ✅ Created release branch: release/$NEW_VERSION"
echo "  ✅ Created Git tag: $NEW_VERSION"
echo "  ✅ Created GitHub release with changes summary"
echo "  ✅ Updated develop branch to $NEXT_VERSION-dev"
echo
echo "GitHub release includes:"
echo "  - Commit count: $COMMIT_COUNT"
echo "  - Commit range: $COMMIT_RANGE"
echo "  - Detailed changes summary"
echo
print_status "Release process completed!"