#!/bin/bash

echo "=== GitHub Analyzer - Demo Test ==="
echo ""

echo "1. Testing help command:"
./gh-analyzer --help
echo ""

echo "2. Testing version command:"
./gh-analyzer --version
echo ""

echo "3. Testing invalid command (should show error and help):"
./gh-analyzer invalid-command
echo ""

echo "4. Testing analyze command without arguments (should show error):"
./gh-analyzer analyze
echo ""

echo "5. Testing repo command without arguments (should show error):"
./gh-analyzer repo
echo ""

echo "6. Testing user command without arguments (should show error):"
./gh-analyzer user
echo ""

echo "=== Demo Complete ==="
echo ""
echo "Note: API calls to GitHub will fail without authentication token."
echo "To test with real data, set GITHUB_TOKEN in .env file."
echo ""
echo "Example usage with token:"
echo "  cp .env.example .env"
echo "  # Edit .env to add your GitHub token"
echo "  ./gh-analyzer repo octocat/Hello-World"